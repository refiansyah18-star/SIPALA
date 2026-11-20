<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Service;
use App\Models\Status;
use App\Models\Ticket;
use App\Models\TicketAttachment;
use App\Models\TicketFieldValue;
use App\Models\TicketMessage;
use App\Models\TicketStatusHistory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class TicketController extends Controller
{
    /**
     * Display user's own tickets.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Ticket::byUser($user->id)
            ->with(['service.category', 'status'])
            ->latest('created_at');

        if ($request->filled('status')) {
            $query->whereHas('status', fn($q) => $q->where('name', $request->status));
        }

        $tickets = $query->paginate(10);
        $statuses = Status::ordered()->get();

        return view('user.tickets.index', compact('tickets', 'statuses'));
    }

    /**
     * Show form for creating a new ticket.
     */
    public function create(Request $request)
    {
        $user = auth()->user();
        $userTypeName = $user->userType->name;

        // Get services available strictly for this user type
        $categories = Category::active()
            ->whereHas('services', function ($q) use ($userTypeName) {
                $q->active()->availableFor($userTypeName);
            })
            ->with(['services' => function ($q) use ($userTypeName) {
                $q->active()->availableFor($userTypeName)->with(['requirements', 'fields'])->orderBy('name');
            }])
            ->orderBy('sort_order')
            ->get();

        $selectedService = null;
        if ($request->filled('service_id')) {
            $selectedService = Service::active()
                ->availableFor($userTypeName)
                ->with(['requirements', 'fields'])
                ->find($request->service_id);
        }

        return view('user.tickets.create', compact('categories', 'selectedService'));
    }

    /**
     * Store newly created ticket.
     */
    public function store(Request $request)
    {
        $request->validate([
            'service_id'  => 'required|exists:services,id',
            'subject'     => 'required|string|max:255',
            'description' => 'required|string|min:10',
            'fields'      => 'nullable|array|max:30',
            'attachments' => 'nullable|array|max:15',
        ]);

        $user = auth()->user();
        $userTypeName = $user->userType->name;
        $service = Service::active()
            ->availableFor($userTypeName)
            ->with(['requirements', 'fields'])
            ->findOrFail($request->service_id);

        $validationErrors = [];

        // =============================================
        // 1. VALIDASI BACKEND: CUSTOM FIELDS
        // =============================================
        $submittedFields = $request->input('fields', []);

        foreach ($service->fields as $field) {
            $val = $submittedFields[$field->id] ?? null;
            $fieldKey = "fields.{$field->id}";

            // Pengecekan field wajib diisi
            if ($field->is_required) {
                $isEmpty = is_null($val) || (is_string($val) && trim($val) === '') || (is_array($val) && empty($val));
                if ($isEmpty) {
                    $validationErrors[$fieldKey] = ["Form '{$field->label}' wajib diisi."];
                    continue;
                }
            }

            // Validasi tipe data jika ada isinya
            if (!is_null($val) && $val !== '') {
                if ($field->field_type === 'number' && !is_numeric($val)) {
                    $validationErrors[$fieldKey] = ["Form '{$field->label}' harus berupa angka."];
                } elseif ($field->field_type === 'date' && !strtotime((string) $val)) {
                    $validationErrors[$fieldKey] = ["Form '{$field->label}' harus berupa format tanggal yang valid."];
                } elseif (in_array($field->field_type, ['select', 'radio']) && is_array($field->options) && !empty($field->options)) {
                    if (!in_array($val, $field->options, true)) {
                        $validationErrors[$fieldKey] = ["Pilihan untuk '{$field->label}' tidak valid."];
                    }
                }
            }
        }

        // =============================================
        // 2. VALIDASI BACKEND: PERSYARATAN DOKUMEN (REQUIREMENTS)
        // =============================================
        $rawAttachments = $request->file('attachments', []);

        foreach ($service->requirements as $req) {
            $reqKey = "attachments.{$req->id}";
            $reqFiles = [];

            if ($request->hasFile("attachments.{$req->id}")) {
                $f = $request->file("attachments.{$req->id}");
                $reqFiles = is_array($f) ? $f : [$f];
            } elseif (isset($rawAttachments[$req->id])) {
                $f = $rawAttachments[$req->id];
                $reqFiles = is_array($f) ? $f : [$f];
            }

            // Pengecekan berkas wajib diunggah
            if ($req->is_required) {
                if (empty($reqFiles)) {
                    // Cek jika pengguna mengunggah via input flat attachments tanpa requirement_id
                    $hasAnyGeneralFile = !empty($rawAttachments);
                    if (!$hasAnyGeneralFile) {
                        $validationErrors[$reqKey] = ["Dokumen persyaratan '{$req->name}' wajib diunggah."];
                    }
                }
            }

            // Validasi batasan spesifik berkas untuk persyaratan ini
            if (!empty($reqFiles)) {
                if ($req->max_file_count && count($reqFiles) > $req->max_file_count) {
                    $validationErrors[$reqKey] = ["Jumlah berkas untuk '{$req->name}' maksimal {$req->max_file_count} berkas."];
                }

                $allowedExtensions = array_map('trim', explode(',', strtolower($req->allowed_file_types ?: 'pdf,jpg,jpeg,png')));

                foreach ($reqFiles as $file) {
                    if (!$file || !$file->isValid()) {
                        $validationErrors[$reqKey] = ["Berkas untuk '{$req->name}' gagal diunggah atau rusak."];
                        continue;
                    }

                    // Validasi batas ukuran spesifik (max_file_size dalam KB)
                    $sizeKb = $file->getSize() / 1024;
                    if ($req->max_file_size && $sizeKb > $req->max_file_size) {
                        $limitText = $req->max_file_size >= 1024
                            ? round($req->max_file_size / 1024, 1) . ' MB'
                            : $req->max_file_size . ' KB';
                        $validationErrors[$reqKey] = ["Ukuran berkas '{$file->getClientOriginalName()}' melebihi batas maksimal ({$limitText})."];
                    }

                    // Validasi ekstensi yang diizinkan
                    $ext = strtolower($file->getClientOriginalExtension());
                    if (!in_array($ext, $allowedExtensions, true)) {
                        $validationErrors[$reqKey] = ["Format berkas '{$file->getClientOriginalName()}' tidak diizinkan. Ekstensi yang diizinkan: " . implode(', ', $allowedExtensions)];
                    }
                }
            }
        }

        // Validasi global untuk seluruh berkas lampiran yang diunggah
        if (!empty($rawAttachments)) {
            foreach ($rawAttachments as $idx => $attItem) {
                $filesToCheck = is_array($attItem) ? $attItem : [$attItem];
                foreach ($filesToCheck as $f) {
                    if ($f instanceof \Illuminate\Http\UploadedFile) {
                        if ($f->getSize() > 5 * 1024 * 1024) {
                            $validationErrors["attachments.{$idx}"] = ["Ukuran berkas '{$f->getClientOriginalName()}' melebihi batas maksimum 5 MB."];
                        }
                        $ext = strtolower($f->getClientOriginalExtension());
                        $globalAllowed = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];
                        if (!in_array($ext, $globalAllowed, true)) {
                            $validationErrors["attachments.{$idx}"] = ["Format berkas '{$f->getClientOriginalName()}' ({$ext}) tidak didukung."];
                        }
                    }
                }
            }
        }

        if (!empty($validationErrors)) {
            throw ValidationException::withMessages($validationErrors);
        }

        $diajukanStatus = Status::where('name', Status::DIAJUKAN)->firstOrFail();

        // Calculate deadline based on SLA
        $deadline = match ($service->sla_unit) {
            'jam' => Carbon::now()->addHours($service->sla_duration),
            'hari_kerja' => Carbon::now()->addWeekdays($service->sla_duration),
            default => Carbon::now()->addDays($service->sla_duration),
        };

        // =============================================
        // 3. TRANSAKSI DATABASE & STORAGE ROLLBACK SAFEGUARD
        // =============================================
        $uploadedFilePaths = [];

        try {
            DB::beginTransaction();

            $ticket = Ticket::create([
                'ticket_number'    => Ticket::generateTicketNumber(),
                'user_id'          => $user->id,
                'service_id'       => $service->id,
                'category_id'      => $service->category_id,
                'program_study_id' => $user->program_study_id,
                'unit_id'          => $user->unit_id ?? $service->unit_id,
                'status_id'        => $diajukanStatus->id,
                'subject'          => $request->subject,
                'description'      => $request->description,
                'sla_duration'     => $service->sla_duration,
                'sla_unit'         => $service->sla_unit,
                'deadline'         => $deadline,
            ]);

            // Simpan custom fields (hanya field_id yang valid milik layanan ini)
            if ($request->has('fields')) {
                $validFieldIds = $service->fields->pluck('id')->all();

                foreach ($request->fields as $fieldId => $val) {
                    $numericFieldId = is_numeric($fieldId) ? (int) $fieldId : 0;
                    if (!is_null($val) && $val !== '' && in_array($numericFieldId, $validFieldIds, true)) {
                        TicketFieldValue::create([
                            'ticket_id'        => $ticket->id,
                            'service_field_id' => $numericFieldId,
                            'value'            => is_array($val) ? json_encode($val) : (string) $val,
                        ]);
                    }
                }
            }

            // Simpan lampiran pada private storage disk 'local'
            if ($request->hasFile('attachments')) {
                $validReqIds = $service->requirements->pluck('id')->all();

                foreach ($request->file('attachments') as $reqKey => $fileOrList) {
                    $files = is_array($fileOrList) ? $fileOrList : [$fileOrList];
                    $requirementId = (is_numeric($reqKey) && (int) $reqKey > 0 && in_array((int) $reqKey, $validReqIds, true))
                        ? (int) $reqKey
                        : null;

                    foreach ($files as $file) {
                        if ($file && $file->isValid()) {
                            // Simpan ke disk lokal/privat (storage/app/private/attachments/YYYY/MM)
                            $storedPath = $file->store('attachments/' . date('Y/m'), 'local');
                            $uploadedFilePaths[] = $storedPath;

                            TicketAttachment::create([
                                'ticket_id'      => $ticket->id,
                                'requirement_id' => $requirementId,
                                'original_name'  => $file->getClientOriginalName(),
                                'stored_name'    => basename($storedPath),
                                'file_path'      => $storedPath,
                                'file_size'      => $file->getSize(),
                                'mime_type'      => $file->getMimeType(),
                            ]);
                        }
                    }
                }
            }

            // Initial status history
            TicketStatusHistory::create([
                'ticket_id'    => $ticket->id,
                'to_status_id' => $diajukanStatus->id,
                'changed_by'   => $user->id,
                'note'         => 'Pengaduan / layanan diajukan oleh pemohon.',
            ]);

            ActivityLog::log(
                $user->id,
                ActivityLog::ACTION_CREATE,
                ActivityLog::MODULE_TICKET,
                $ticket->id,
                "Membuat tiket baru: {$ticket->ticket_number} ({$ticket->subject})"
            );

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            // Rollback penghapusan berkas fisik jika database gagal
            foreach ($uploadedFilePaths as $path) {
                Storage::disk('local')->delete($path);
            }

            Log::error('Gagal menyimpan pembuatan tiket baru (Rollback)', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
            ]);

            throw $e;
        }

        // Kirim Notifikasi Email Otomatis setelah transaksi sukses di-commit
        try {
            app(\App\Services\NotificationService::class)->sendTicketSubmitted($ticket);
        } catch (\Throwable $e) {
            Log::warning('Gagal mengirim email notifikasi tiket baru', [
                'ticket_id' => $ticket->id,
                'error'     => $e->getMessage(),
            ]);
        }

        return redirect()->route('user.tickets.show', $ticket)
            ->with('success', "Tiket Anda berhasil diajukan dengan nomor: {$ticket->ticket_number} dan notifikasi email telah dikirimkan. Mohon tunggu verifikasi petugas.");
    }

    /**
     * Show user's ticket details and conversation.
     */
    public function show(Ticket $ticket)
    {
        if ($ticket->user_id !== auth()->id()) {
            abort(403, 'Anda tidak memiliki hak untuk melihat tiket ini.');
        }

        $ticket->load([
            'service.category',
            'status',
            'attachments.requirement',
            'fieldValues.serviceField',
            'statusHistories.fromStatus',
            'statusHistories.toStatus',
            'messages.user.userType',
            'emailNotifications',
            'rating',
        ]);

        return view('user.tickets.show', compact('ticket'));
    }

    /**
     * Store message from ticket owner.
     */
    public function storeMessage(Request $request, Ticket $ticket)
    {
        if ($ticket->user_id !== auth()->id()) {
            abort(403, 'Akses ditolak.');
        }

        if ($ticket->status->name !== Status::DIPROSES) {
            return back()->with('error', 'Fitur pesan / tanggapan hanya dapat digunakan saat pengaduan sedang berstatus Diproses.');
        }

        $request->validate([
            'message' => 'required|string|max:50',
        ], [
            'message.required' => 'Pesan tidak boleh kosong.',
            'message.max' => 'Pesan tidak boleh lebih dari 50 karakter (termasuk huruf dan spasi).',
        ]);

        TicketMessage::create([
            'ticket_id' => $ticket->id,
            'user_id' => auth()->id(),
            'message' => trim($request->message),
        ]);

        return redirect()->route('user.tickets.show', $ticket)
            ->with('success', 'Pesan Anda berhasil dikirim ke staf.');
    }

    /**
     * Tampilkan / unduh berkas lampiran tiket dengan proteksi otorisasi (Anti-IDOR).
     */
    public function showAttachment(Ticket $ticket, TicketAttachment $attachment)
    {
        if ($attachment->ticket_id !== $ticket->id) {
            abort(404);
        }

        $user = auth()->user();
        $isOwner = ($ticket->user_id === $user->id);
        $isAdmin = ($user->isAdmin() || $user->isSuperAdmin());
        $isAllowedStaff = $user->isStaff() && (
            $ticket->assignments()->where('staff_id', $user->id)->exists() ||
            $user->staffSpecialties->contains('id', $ticket->service_id)
        );

        if (!$isOwner && !$isAdmin && !$isAllowedStaff) {
            abort(403, 'Anda tidak memiliki hak akses untuk membuka berkas lampiran ini.');
        }

        $disk = 'local';
        if (!Storage::disk('local')->exists($attachment->file_path)) {
            if (Storage::disk('public')->exists($attachment->file_path)) {
                $disk = 'public';
            } else {
                abort(404, 'Berkas tidak ditemukan.');
            }
        }

        return Storage::disk($disk)->response($attachment->file_path, $attachment->original_name);
    }
}

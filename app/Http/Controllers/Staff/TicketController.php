<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Status;
use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\TicketStatusHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class TicketController extends Controller
{
    /**
     * Display a listing of tickets assigned to the staff,
     * plus tickets matching their service specialties.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $specialtyServiceIds = $user->staffSpecialties->pluck('id');

        // Tickets assigned to this staff
        $assignedQuery = Ticket::assignedTo($user->id)
            ->with(['service.category', 'status', 'user.userType'])
            ->latest('created_at');

        // Optional filter by status
        if ($request->filled('status')) {
            $assignedQuery->whereHas('status', fn($q) => $q->where('name', $request->status));
        }

        $assignedTickets = $assignedQuery->paginate(15, ['*'], 'assigned_page')->withQueryString();

        // Unassigned or pending tickets in this staff's specialties (for awareness / picking up)
        $specialtyTickets = Ticket::whereIn('service_id', $specialtyServiceIds)
            ->whereHas('status', fn($q) => $q->whereIn('name', [Status::DIAJUKAN, Status::DIVERIFIKASI]))
            ->with(['service.category', 'status', 'user.userType'])
            ->latest('created_at')
            ->take(10)
            ->get();

        $statuses = Status::ordered()->get();

        return view('staff.tickets.index', compact('assignedTickets', 'specialtyTickets', 'statuses'));
    }

    /**
     * Display ticket detail for staff.
     */
    public function show(Ticket $ticket)
    {
        // Authorize: staff must be assigned or super admin
        $user = auth()->user();
        $isAssigned = $ticket->assignments()->where('staff_id', $user->id)->exists();

        if (!$isAssigned && !$user->isAdmin() && !$user->isSuperAdmin()) {
            // Also allow if it's within their specialty
            $isSpecialist = $user->staffSpecialties->contains('id', $ticket->service_id);
            if (!$isSpecialist) {
                abort(403, 'Anda tidak ditugaskan pada tiket ini dan tiket ini bukan bidang spesialisasi Anda.');
            }
        }

        $ticket->load([
            'user.userType',
            'user.programStudy',
            'user.serviceUnit',
            'service.category',
            'status',
            'attachments.requirement',
            'fieldValues.serviceField',
            'assignments.staff',
            'assignments.assignedByUser',
            'statusHistories.changedByUser',
            'statusHistories.fromStatus',
            'statusHistories.toStatus',
            'messages.user.userType',
            'rating',
        ]);

        return view('staff.tickets.show', compact('ticket', 'isAssigned'));
    }

    /**
     * Pastikan staf benar-benar ditugaskan pada tiket sebelum mengubah status atau mengirim pesan.
     */
    protected function authorizeAssignedStaff(Ticket $ticket): void
    {
        $user = auth()->user();
        $isAssigned = $ticket->assignments()->where('staff_id', $user->id)->exists();

        if (!$isAssigned && !$user->isAdmin() && !$user->isSuperAdmin()) {
            abort(403, 'Anda tidak ditugaskan pada tiket ini sehingga tidak memiliki izin untuk mengubah status atau mengirim pesan.');
        }
    }

    /**
     * Update ticket status (Staff moves to DIPROSES or SELESAI).
     */
    public function updateStatus(Request $request, Ticket $ticket)
    {
        $this->authorizeAssignedStaff($ticket);

        $request->validate([
            'status' => 'required|in:' . Status::DIPROSES . ',' . Status::SELESAI,
            'note' => 'nullable|string|max:1000',
            'resolution_note' => 'required_if:status,' . Status::SELESAI . '|nullable|string|max:2000',
            'proofs' => 'nullable|array|max:5',
            'proofs.*' => 'file|max:5120|mimes:pdf,jpg,jpeg,png|mimetypes:application/pdf,image/jpeg,image/png',
        ]);

        // State Machine Validation
        if (!$ticket->canTransitionTo($request->status)) {
            $msg = match ($request->status) {
                Status::DIPROSES => "Tiket tidak dapat diubah ke status 'Diproses' karena status saat ini adalah {$ticket->status->label}. Tiket wajib diverifikasi terlebih dahulu oleh Administrator.",
                Status::SELESAI => "Tiket tidak dapat langsung diselesaikan karena status saat ini adalah {$ticket->status->label}. Tiket wajib berstatus 'Diproses' terlebih dahulu.",
                default => "Perubahan status tiket dari {$ticket->status->label} ke {$request->status} tidak diizinkan.",
            };
            return redirect()->route('staff.tickets.show', $ticket)->with('error', $msg);
        }

        $newStatus = Status::where('name', $request->status)->firstOrFail();
        $oldStatusId = $ticket->status_id;
        $uploadedFilePaths = [];

        try {
            DB::beginTransaction();

            $updateData = [
                'status_id' => $newStatus->id,
            ];

            if ($request->status === Status::SELESAI) {
                $updateData['resolution_note'] = $request->resolution_note;
                $updateData['resolved_at'] = now();
            }

            $ticket->update($updateData);

            // Upload bukti penyelesaian dari staf jika ada
            if ($request->hasFile('proofs')) {
                foreach ($request->file('proofs') as $file) {
                    if ($file->isValid()) {
                        $storedPath = $file->store('attachments/' . date('Y/m'), 'local');
                        $uploadedFilePaths[] = $storedPath;

                        \App\Models\TicketAttachment::create([
                            'ticket_id' => $ticket->id,
                            'requirement_id' => null,
                            'original_name' => $file->getClientOriginalName(),
                            'stored_name' => basename($storedPath),
                            'file_path' => $storedPath,
                            'file_size' => $file->getSize(),
                            'mime_type' => $file->getMimeType(),
                        ]);
                    }
                }
            }

            TicketStatusHistory::create([
                'ticket_id' => $ticket->id,
                'from_status_id' => $oldStatusId,
                'to_status_id' => $newStatus->id,
                'changed_by' => auth()->id(),
                'note' => $request->note ?: ($request->status === Status::SELESAI ? $request->resolution_note : 'Staf mulai mengerjakan tiket.'),
            ]);

            ActivityLog::log(
                auth()->id(),
                $request->status === Status::SELESAI ? ActivityLog::ACTION_COMPLETE : ActivityLog::ACTION_STATUS_CHANGE,
                ActivityLog::MODULE_TICKET,
                $ticket->id,
                "Staf " . auth()->user()->nama . " memperbarui status tiket {$ticket->ticket_number} menjadi {$request->status}"
            );

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            // Bersihkan file yang sempat terupload ke storage privat
            foreach ($uploadedFilePaths as $path) {
                Storage::disk('local')->delete($path);
            }

            Log::error('Gagal memperbarui status tiket oleh staf', [
                'ticket_id' => $ticket->id,
                'error'     => $e->getMessage(),
            ]);

            return redirect()->route('staff.tickets.show', $ticket)
                ->with('error', 'Terjadi kesalahan sistem saat memperbarui status tiket. Silakan coba kembali.');
        }

        // Kirim notifikasi email otomatis ke pemohon sesuai perubahan status (DIPROSES / SELESAI)
        if ($request->status === Status::SELESAI) {
            app(\App\Services\NotificationService::class)->sendTicketCompleted($ticket, $request->resolution_note);
        } elseif ($request->status === Status::DIPROSES) {
            $processNote = $request->note ?: 'Staf pelaksana mulai memproses pengaduan/layanan akademik Anda.';
            app(\App\Services\NotificationService::class)->sendTicketProcessed($ticket, $processNote);
        }

        return redirect()->route('staff.tickets.show', $ticket)
            ->with('success', "Status tiket berhasil diperbarui menjadi {$request->status} dan notifikasi email telah dikirimkan ke pemohon ({$ticket->user->email}).");
    }

    /**
     * Send chat message in ticket.
     */
    public function storeMessage(Request $request, Ticket $ticket)
    {
        $this->authorizeAssignedStaff($ticket);

        if ($ticket->status->name !== Status::DIPROSES) {
            return back()->with('error', 'Pesan hanya dapat dikirim saat tiket berstatus DIPROSES.');
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

        return redirect()->route('staff.tickets.show', $ticket)
            ->with('success', 'Pesan terkirim ke pemohon.');
    }
}

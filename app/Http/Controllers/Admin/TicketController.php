<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Status;
use App\Models\Ticket;
use App\Models\TicketAssignment;
use App\Models\TicketMessage;
use App\Models\TicketStatusHistory;
use App\Models\User;
use App\Models\UserType;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    /**
     * Display a listing of all tickets with filters.
     */
    public function index(Request $request)
    {
        $query = Ticket::with(['service', 'category', 'status', 'user.userType', 'latestAssignment.staff', 'assignments.staff'])
            ->latest('created_at');

        // Filter by Status
        if ($request->filled('status')) {
            $query->whereHas('status', fn($q) => $q->where('name', $request->status));
        }

        // Filter by Category
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter by Assignment
        if ($request->input('assignment') === 'unassigned') {
            $query->unassigned();
        } elseif ($request->filled('staff_id')) {
            $query->assignedTo($request->staff_id);
        }

        // Filter by Search (ticket_number, subject, pemohon nama/email)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('ticket_number', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('nama', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%")
                         ->orWhere('nomor_identitas', 'like', "%{$search}%");
                  });
            });
        }

        $tickets = $query->paginate(15);
        $categories = Category::active()->orderBy('name')->get();
        $statuses = Status::ordered()->get();

        $staffUsers = User::whereHas('userType', fn($q) => $q->where('name', UserType::STAFF))
            ->active()
            ->orderBy('nama')
            ->get();

        return view('admin.tickets.index', compact('tickets', 'categories', 'statuses', 'staffUsers'));
    }

    /**
     * Display ticket details, assignment history, timeline, and messages.
     */
    public function show(Ticket $ticket)
    {
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
            'emailNotifications',
            'rating',
        ]);

        // Get all active staff, marked if they specialize in this ticket's service
        $staffTypeId = UserType::where('name', UserType::STAFF)->value('id');
        $staffMembers = User::where('user_type_id', $staffTypeId)
            ->active()
            ->with(['staffSpecialties'])
            ->withCount(['assignedTickets' => function ($q) {
                $q->whereHas('ticket.status', fn($sq) => $sq->where('is_terminal', false));
            }])
            ->get()
            ->map(function ($staff) use ($ticket) {
                $staff->is_specialist = $staff->staffSpecialties->contains('id', $ticket->service_id);
                return $staff;
            })
            // Sort specialists first, then by active workload (fewer tickets first)
            ->sortBy([
                ['is_specialist', 'desc'],
                ['assigned_tickets_count', 'asc'],
            ]);

        return view('admin.tickets.show', compact('ticket', 'staffMembers'));
    }

    /**
     * Verify ticket (DIAJUKAN -> DIVERIFIKASI).
     */
    public function verify(Request $request, Ticket $ticket)
    {
        if (!$ticket->canTransitionTo(Status::DIVERIFIKASI)) {
            return redirect()->route('admin.tickets.show', $ticket)
                ->with('error', "Tiket tidak dapat diverifikasi karena saat ini berstatus {$ticket->status->label}. Verifikasi hanya diperbolehkan untuk tiket berstatus DIAJUKAN.");
        }

        $diverifikasiStatus = Status::where('name', Status::DIVERIFIKASI)->firstOrFail();
        $oldStatusId = $ticket->status_id;

        $ticket->update(['status_id' => $diverifikasiStatus->id]);

        $verifyNote = $request->input('note', 'Tiket telah diverifikasi oleh Admin.');

        TicketStatusHistory::create([
            'ticket_id' => $ticket->id,
            'from_status_id' => $oldStatusId,
            'to_status_id' => $diverifikasiStatus->id,
            'changed_by' => auth()->id(),
            'note' => $verifyNote,
        ]);

        // Kirim Notifikasi Email Otomatis (Status: DIVERIFIKASI)
        app(\App\Services\NotificationService::class)->sendTicketVerified($ticket, $verifyNote);

        ActivityLog::log(
            auth()->id(),
            ActivityLog::ACTION_VERIFY,
            ActivityLog::MODULE_TICKET,
            $ticket->id,
            "Tiket {$ticket->ticket_number} diverifikasi"
        );

        return redirect()->route('admin.tickets.show', $ticket)
            ->with('success', "Tiket {$ticket->ticket_number} berhasil diverifikasi dan notifikasi email telah dikirimkan ke pemohon ({$ticket->user->email}).");
    }

    /**
     * Reject ticket (wajib alasan penolakan).
     */
    public function reject(Request $request, Ticket $ticket)
    {
        if (!$ticket->canTransitionTo(Status::DITOLAK)) {
            return redirect()->route('admin.tickets.show', $ticket)
                ->with('error', "Tiket sudah berstatus akhir ({$ticket->status->label}) dan tidak dapat ditolak.");
        }

        $request->validate([
            'rejection_reason' => 'required|string|min:5|max:1000',
        ]);

        $ditolakStatus = Status::where('name', Status::DITOLAK)->firstOrFail();
        $oldStatusId = $ticket->status_id;

        $ticket->update([
            'status_id' => $ditolakStatus->id,
            'rejection_reason' => $request->rejection_reason,
            'resolved_at' => now(),
        ]);

        TicketStatusHistory::create([
            'ticket_id' => $ticket->id,
            'from_status_id' => $oldStatusId,
            'to_status_id' => $ditolakStatus->id,
            'changed_by' => auth()->id(),
            'note' => 'Tiket ditolak: ' . $request->rejection_reason,
        ]);

        // Kirim Notifikasi Email ke Pemohon
        app(\App\Services\NotificationService::class)->sendTicketRejected($ticket, $request->rejection_reason);

        ActivityLog::log(
            auth()->id(),
            ActivityLog::ACTION_REJECT,
            ActivityLog::MODULE_TICKET,
            $ticket->id,
            "Tiket {$ticket->ticket_number} ditolak: {$request->rejection_reason}"
        );

        return redirect()->route('admin.tickets.show', $ticket)
            ->with('info', "Tiket {$ticket->ticket_number} telah ditolak dan notifikasi email telah dikirimkan ke pemohon ({$ticket->user->email}).");
    }

    /**
     * Assign ticket to one or more staff members (Admin Manager feature).
     */
    public function assign(Request $request, Ticket $ticket)
    {
        if ($ticket->status->is_terminal || in_array($ticket->status->name, [Status::SELESAI, Status::DITOLAK], true)) {
            return redirect()->route('admin.tickets.show', $ticket)
                ->with('error', "Tiket sudah berstatus akhir ({$ticket->status->label}) dan tidak dapat ditugaskan ke staf.");
        }

        // Backward compatibility if single staff_id is sent
        if ($request->filled('staff_id') && !$request->has('staff_ids')) {
            $request->merge(['staff_ids' => [$request->staff_id]]);
        }

        $request->validate([
            'staff_ids' => 'required|array|min:1',
            'staff_ids.*' => 'exists:users,id',
            'notes' => 'nullable|string|max:500',
        ], [
            'staff_ids.required' => 'Pilih minimal satu staf pelaksana.',
            'staff_ids.min' => 'Pilih minimal satu staf pelaksana.',
        ]);

        $staffIds = array_values(array_unique(array_map('intval', $request->staff_ids)));
        $staffMembers = User::whereIn('id', $staffIds)
            ->whereHas('userType', fn($q) => $q->where('name', UserType::STAFF))
            ->active()
            ->orderBy('nama')
            ->get();

        if ($staffMembers->isEmpty()) {
            return back()->with('error', 'Staf pelaksana yang dipilih tidak valid atau tidak aktif.');
        }

        $staffNames = $staffMembers->pluck('nama')->join(', ');
        $now = now();

        // Sinkronisasi penugasan staf pada tiket ini (mendukung multi-staf)
        $ticket->assignments()->delete();

        foreach ($staffMembers as $staff) {
            TicketAssignment::create([
                'ticket_id' => $ticket->id,
                'staff_id' => $staff->id,
                'assigned_by' => auth()->id(),
                'notes' => $request->notes,
                'assigned_at' => $now,
            ]);
        }

        // If status was DIAJUKAN, also automatically move to DIVERIFIKASI
        if ($ticket->status->name === Status::DIAJUKAN) {
            $diverifikasiStatus = Status::where('name', Status::DIVERIFIKASI)->first();
            if ($diverifikasiStatus) {
                $oldStatusId = $ticket->status_id;
                $ticket->update(['status_id' => $diverifikasiStatus->id]);

                $verifyNote = "Tiket diverifikasi dan ditugaskan ke staf: {$staffNames}";

                TicketStatusHistory::create([
                    'ticket_id' => $ticket->id,
                    'from_status_id' => $oldStatusId,
                    'to_status_id' => $diverifikasiStatus->id,
                    'changed_by' => auth()->id(),
                    'note' => $verifyNote,
                ]);

                // Kirim Notifikasi Email Otomatis (Status: DIVERIFIKASI)
                app(\App\Services\NotificationService::class)->sendTicketVerified($ticket, $verifyNote);
            }
        }

        ActivityLog::log(
            auth()->id(),
            ActivityLog::ACTION_ASSIGN,
            ActivityLog::MODULE_TICKET,
            $ticket->id,
            "Tiket {$ticket->ticket_number} ditugaskan kepada {$staffNames}"
        );

        return redirect()->route('admin.tickets.show', $ticket)
            ->with('success', "Tiket berhasil ditugaskan kepada {$staffMembers->count()} staf ({$staffNames}).");
    }

    /**
     * Send chat message in ticket (Admin/Manager).
     */
    public function storeMessage(Request $request, Ticket $ticket)
    {
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

        return redirect()->route('admin.tickets.show', $ticket)
            ->with('success', 'Pesan terkirim.');
    }

    /**
     * Send rating reminder email for a completed ticket that has not been rated yet.
     */
    public function remindRating(Ticket $ticket)
    {
        if ($ticket->status->name !== Status::SELESAI) {
            return back()->with('error', 'Pengingat rating hanya dapat dikirim untuk tiket yang sudah berstatus SELESAI.');
        }

        if ($ticket->rating()->exists()) {
            return back()->with('info', 'Pemohon sudah memberikan penilaian (rating) pada tiket ini.');
        }

        $notif = app(\App\Services\NotificationService::class)->sendRatingReminder($ticket);

        if ($notif->status === \App\Models\EmailNotification::STATUS_SENT) {
            return back()->with('success', "Email pengingat penilaian (rating) berhasil dikirimkan ke {$ticket->user->email}.");
        }

        return back()->with('error', 'Gagal mengirim email pengingat rating karena kendala koneksi server email. Detail teknis telah dicatat di log sistem.');
    }
}

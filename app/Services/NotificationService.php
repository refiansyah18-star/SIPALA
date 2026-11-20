<?php

namespace App\Services;

use App\Mail\AlumniPasswordResetMail;
use App\Mail\AlumniRegistrationApprovedMail;
use App\Mail\AlumniRegistrationRejectedMail;
use App\Mail\AlumniRegistrationSubmittedMail;
use App\Mail\TicketCompletedMail;
use App\Mail\TicketProcessedMail;
use App\Mail\TicketRatingReminderMail;
use App\Mail\TicketRejectedMail;
use App\Mail\TicketSubmittedMail;
use App\Mail\TicketVerifiedMail;
use App\Models\AlumniRegistration;
use App\Models\EmailNotification;
use App\Models\Status;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class NotificationService
{
    /**
     * Send email notification when ticket is initially submitted (DIAJUKAN).
     */
    public function sendTicketSubmitted(Ticket $ticket): EmailNotification
    {
        $ticket->loadMissing(['user.programStudy', 'service', 'category', 'programStudy']);
        $user = $ticket->user;
        $subject = "Tiket: {$ticket->ticket_number} berhasil diajukan";
        $body = "Tiket pengaduan layanan akademik Anda ({$ticket->subject}) berhasil diajukan dan sedang menunggu verifikasi.";

        $notification = EmailNotification::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'type' => EmailNotification::TYPE_SUBMITTED,
            'subject' => $subject,
            'body' => $body,
            'status' => EmailNotification::STATUS_PENDING,
        ]);

        try {
            Mail::to($user->email)->send(new TicketSubmittedMail($ticket));

            $notification->update([
                'status' => EmailNotification::STATUS_SENT,
                'sent_at' => now(),
            ]);

            Log::info("Email notifikasi pengajuan tiket {$ticket->ticket_number} berhasil dikirim ke {$user->email}");
        } catch (Throwable $e) {
            $notification->update([
                'status' => EmailNotification::STATUS_FAILED,
                'error_message' => $e->getMessage(),
            ]);

            Log::error("Gagal mengirim email pengajuan tiket {$ticket->ticket_number} ke {$user->email}: " . $e->getMessage());
        }

        return $notification;
    }

    /**
     * Send email notification when ticket is verified (DIVERIFIKASI).
     */
    public function sendTicketVerified(Ticket $ticket, ?string $note = null): EmailNotification
    {
        $ticket->loadMissing(['user.programStudy', 'service', 'category', 'programStudy']);
        $user = $ticket->user;
        $subject = "Tiket: {$ticket->ticket_number} telah diverifikasi";
        $noteText = $note ?: 'Tiket pengajuan layanan akademik Anda telah diverifikasi oleh Admin SIPALA dan diteruskan ke petugas pelaksana.';

        $notification = EmailNotification::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'type' => EmailNotification::TYPE_VERIFIED,
            'subject' => $subject,
            'body' => $noteText,
            'status' => EmailNotification::STATUS_PENDING,
        ]);

        try {
            Mail::to($user->email)->send(new TicketVerifiedMail($ticket, $noteText));

            $notification->update([
                'status' => EmailNotification::STATUS_SENT,
                'sent_at' => now(),
            ]);

            Log::info("Email notifikasi verifikasi tiket {$ticket->ticket_number} berhasil dikirim ke {$user->email}");
        } catch (Throwable $e) {
            $notification->update([
                'status' => EmailNotification::STATUS_FAILED,
                'error_message' => $e->getMessage(),
            ]);

            Log::error("Gagal mengirim email verifikasi tiket {$ticket->ticket_number} ke {$user->email}: " . $e->getMessage());
        }

        return $notification;
    }

    /**
     * Send email notification when ticket is being processed by staff (DIPROSES).
     */
    public function sendTicketProcessed(Ticket $ticket, ?string $note = null): EmailNotification
    {
        $ticket->loadMissing(['user.programStudy', 'service', 'category', 'programStudy']);
        $user = $ticket->user;
        $subject = "Tiket: {$ticket->ticket_number} sedang diproses";
        $noteText = $note ?: 'Staf pelaksana SIPALA sedang memproses pengajuan/pengaduan layanan akademik Anda.';

        $notification = EmailNotification::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'type' => EmailNotification::TYPE_PROCESSED,
            'subject' => $subject,
            'body' => $noteText,
            'status' => EmailNotification::STATUS_PENDING,
        ]);

        try {
            Mail::to($user->email)->send(new TicketProcessedMail($ticket, $noteText));

            $notification->update([
                'status' => EmailNotification::STATUS_SENT,
                'sent_at' => now(),
            ]);

            Log::info("Email notifikasi pengerjaan tiket {$ticket->ticket_number} berhasil dikirim ke {$user->email}");
        } catch (Throwable $e) {
            $notification->update([
                'status' => EmailNotification::STATUS_FAILED,
                'error_message' => $e->getMessage(),
            ]);

            Log::error("Gagal mengirim email pengerjaan tiket {$ticket->ticket_number} ke {$user->email}: " . $e->getMessage());
        }

        return $notification;
    }

    /**
     * Send email notification when ticket is rejected (DITOLAK).
     */
    public function sendTicketRejected(Ticket $ticket, string $reason): EmailNotification
    {
        $ticket->loadMissing(['user.programStudy', 'service', 'category', 'programStudy']);
        $user = $ticket->user;
        $subject = "Tiket: {$ticket->ticket_number} ditolak";

        // 1. Create notification log in database
        $notification = EmailNotification::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'type' => EmailNotification::TYPE_REJECTED,
            'subject' => $subject,
            'body' => $reason,
            'status' => EmailNotification::STATUS_PENDING,
        ]);

        // 2. Attempt to dispatch email
        try {
            Mail::to($user->email)->send(new TicketRejectedMail($ticket, $reason));

            $notification->update([
                'status' => EmailNotification::STATUS_SENT,
                'sent_at' => now(),
            ]);

            Log::info("Email notifikasi penolakan tiket {$ticket->ticket_number} berhasil dikirim ke {$user->email}");
        } catch (Throwable $e) {
            $notification->update([
                'status' => EmailNotification::STATUS_FAILED,
                'error_message' => $e->getMessage(),
            ]);

            Log::error("Gagal mengirim email penolakan tiket {$ticket->ticket_number} ke {$user->email}: " . $e->getMessage());
        }

        return $notification;
    }

    /**
     * Send email notification when ticket is completed (SELESAI).
     */
    public function sendTicketCompleted(Ticket $ticket, string $resolutionNote): EmailNotification
    {
        $ticket->loadMissing(['user.programStudy', 'service', 'category', 'programStudy']);
        $user = $ticket->user;
        $subject = "Tiket: {$ticket->ticket_number} selesai";

        // 1. Create notification log in database
        $notification = EmailNotification::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'type' => EmailNotification::TYPE_COMPLETED,
            'subject' => $subject,
            'body' => $resolutionNote,
            'status' => EmailNotification::STATUS_PENDING,
        ]);

        // 2. Attempt to dispatch email
        try {
            Mail::to($user->email)->send(new TicketCompletedMail($ticket, $resolutionNote));

            $notification->update([
                'status' => EmailNotification::STATUS_SENT,
                'sent_at' => now(),
            ]);

            Log::info("Email notifikasi penyelesaian tiket {$ticket->ticket_number} berhasil dikirim ke {$user->email}");
        } catch (Throwable $e) {
            $notification->update([
                'status' => EmailNotification::STATUS_FAILED,
                'error_message' => $e->getMessage(),
            ]);

            Log::error("Gagal mengirim email penyelesaian tiket {$ticket->ticket_number} ke {$user->email}: " . $e->getMessage());
        }

        return $notification;
    }

    /**
     * Send email reminder to user to rate a completed ticket.
     */
    public function sendRatingReminder(Ticket $ticket): EmailNotification
    {
        $ticket->loadMissing(['user.programStudy', 'service', 'category', 'programStudy']);
        $user = $ticket->user;
        $subject = "Pengingat Penilaian Layanan Tiket: {$ticket->ticket_number}";
        $body = "Pengingat untuk memberikan penilaian (rating) kepuasan layanan pada tiket {$ticket->ticket_number} ({$ticket->subject}).";

        $notification = EmailNotification::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'type' => EmailNotification::TYPE_RATING_REMINDER,
            'subject' => $subject,
            'body' => $body,
            'status' => EmailNotification::STATUS_PENDING,
        ]);

        try {
            Mail::to($user->email)->send(new TicketRatingReminderMail($ticket));

            $notification->update([
                'status' => EmailNotification::STATUS_SENT,
                'sent_at' => now(),
            ]);

            Log::info("Email pengingat rating tiket {$ticket->ticket_number} berhasil dikirim ke {$user->email}");
        } catch (Throwable $e) {
            $notification->update([
                'status' => EmailNotification::STATUS_FAILED,
                'error_message' => $e->getMessage(),
            ]);

            Log::error("Gagal mengirim email pengingat rating tiket {$ticket->ticket_number} ke {$user->email}: " . $e->getMessage());
        }

        return $notification;
    }

    /**
     * Automatically find completed tickets without rating and send reminder emails.
     * By default, reminds once every $intervalDays (e.g., 7 days / weekly) after completion.
     */
    public function sendDueRatingReminders(int $intervalDays = 7, bool $forceAllUnrated = false): int
    {
        $cutoff = now()->subDays($intervalDays);

        $query = Ticket::byStatus(Status::SELESAI)
            ->doesntHave('rating')
            ->with(['user.programStudy', 'service', 'category', 'programStudy']);

        if (!$forceAllUnrated) {
            $query->where(function ($q) use ($cutoff) {
                $q->where('resolved_at', '<=', $cutoff)
                  ->orWhere(function ($sq) use ($cutoff) {
                      $sq->whereNull('resolved_at')->where('updated_at', '<=', $cutoff);
                  });
            })->whereDoesntHave('emailNotifications', function ($nq) use ($cutoff) {
                $nq->where('type', EmailNotification::TYPE_RATING_REMINDER)
                   ->where('status', EmailNotification::STATUS_SENT)
                   ->where('created_at', '>=', $cutoff);
            });
        }

        $tickets = $query->get();
        $sentCount = 0;

        foreach ($tickets as $ticket) {
            $notif = $this->sendRatingReminder($ticket);
            if ($notif->status === EmailNotification::STATUS_SENT) {
                $sentCount++;
            }
        }

        return $sentCount;
    }

    /**
     * Kirim email konfirmasi bahwa pendaftaran akun alumni telah diterima dan menunggu verifikasi.
     */
    public function sendAlumniRegistrationSubmitted(AlumniRegistration $registration): EmailNotification
    {
        $registration->loadMissing('programStudy');

        return $this->dispatchAccountEmail(
            recipientEmail: $registration->email,
            type: EmailNotification::TYPE_ALUMNI_SUBMITTED,
            subject: 'Pendaftaran Akun Alumni SIPALA Diterima — Menunggu Verifikasi',
            body: "Pendaftaran akun alumni atas nama {$registration->nama} (NIM: {$registration->nim}) telah diterima dan menunggu verifikasi Admin.",
            mailable: new AlumniRegistrationSubmittedMail($registration),
            alumniRegistrationId: $registration->id,
        );
    }

    /**
     * Kirim email pemberitahuan bahwa akun alumni telah disetujui dan siap digunakan.
     */
    public function sendAlumniRegistrationApproved(AlumniRegistration $registration, User $user): EmailNotification
    {
        $user->loadMissing('programStudy');

        return $this->dispatchAccountEmail(
            recipientEmail: $user->email,
            type: EmailNotification::TYPE_ALUMNI_APPROVED,
            subject: 'Akun Alumni SIPALA Anda Telah Disetujui & Aktif',
            body: "Akun alumni atas nama {$user->nama} (NIM: {$user->nomor_identitas}) telah disetujui dan aktif.",
            mailable: new AlumniRegistrationApprovedMail($registration, $user),
            alumniRegistrationId: $registration->id,
            userId: $user->id,
        );
    }

    /**
     * Kirim email pemberitahuan penolakan pendaftaran akun alumni beserta alasannya.
     */
    public function sendAlumniRegistrationRejected(AlumniRegistration $registration): EmailNotification
    {
        return $this->dispatchAccountEmail(
            recipientEmail: $registration->email,
            type: EmailNotification::TYPE_ALUMNI_REJECTED,
            subject: 'Pendaftaran Akun Alumni SIPALA Ditolak',
            body: (string) $registration->rejection_reason,
            mailable: new AlumniRegistrationRejectedMail($registration),
            alumniRegistrationId: $registration->id,
        );
    }

    /**
     * Kirim tautan reset kata sandi untuk akun Alumni lokal.
     */
    public function sendAlumniPasswordReset(User $user, string $resetUrl): EmailNotification
    {
        return $this->dispatchAccountEmail(
            recipientEmail: $user->email,
            type: EmailNotification::TYPE_PASSWORD_RESET,
            subject: 'Atur Ulang Kata Sandi Akun Alumni SIPALA',
            body: "Permintaan reset kata sandi akun alumni untuk {$user->email}.",
            mailable: new AlumniPasswordResetMail($user, $resetUrl),
            userId: $user->id,
        );
    }

    /**
     * Helper bersama untuk mencatat log email non-tiket dan mengirim Mailable dengan error handling aman.
     */
    protected function dispatchAccountEmail(
        string $recipientEmail,
        string $type,
        string $subject,
        string $body,
        Mailable $mailable,
        ?int $alumniRegistrationId = null,
        ?int $userId = null,
    ): EmailNotification {
        $notification = EmailNotification::create([
            'ticket_id' => null,
            'alumni_registration_id' => $alumniRegistrationId,
            'user_id' => $userId,
            'recipient_email' => $recipientEmail,
            'type' => $type,
            'subject' => $subject,
            'body' => $body,
            'status' => EmailNotification::STATUS_PENDING,
        ]);

        try {
            Mail::to($recipientEmail)->send($mailable);

            $notification->update([
                'status' => EmailNotification::STATUS_SENT,
                'sent_at' => now(),
            ]);

            Log::info("Email {$type} berhasil dikirim ke {$recipientEmail}");
        } catch (Throwable $e) {
            $notification->update([
                'status' => EmailNotification::STATUS_FAILED,
                'error_message' => $e->getMessage(),
            ]);

            Log::error("Gagal mengirim email {$type} ke {$recipientEmail}: " . $e->getMessage());
        }

        return $notification;
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailNotification extends Model
{
    protected $fillable = [
        'ticket_id',
        'alumni_registration_id',
        'user_id',
        'recipient_email',
        'type',
        'subject',
        'body',
        'sent_at',
        'status',
        'error_message',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    const TYPE_SUBMITTED        = 'SUBMITTED';
    const TYPE_VERIFIED         = 'VERIFIED';
    const TYPE_PROCESSED        = 'PROCESSED';
    const TYPE_REJECTED         = 'REJECTED';
    const TYPE_COMPLETED        = 'COMPLETED';
    const TYPE_RATING_REMINDER  = 'RATING_REMINDER';
    const TYPE_ALUMNI_SUBMITTED = 'ALUMNI_SUBMITTED';
    const TYPE_ALUMNI_APPROVED  = 'ALUMNI_APPROVED';
    const TYPE_ALUMNI_REJECTED  = 'ALUMNI_REJECTED';
    const TYPE_PASSWORD_RESET   = 'PASSWORD_RESET';

    const STATUS_PENDING = 'PENDING';
    const STATUS_SENT = 'SENT';
    const STATUS_FAILED = 'FAILED';

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            self::TYPE_SUBMITTED, 'DIAJUKAN'    => 'Notif Tiket Diajukan',
            self::TYPE_VERIFIED, 'DIVERIFIKASI' => 'Notif Diverifikasi',
            self::TYPE_PROCESSED, 'DIPROSES'    => 'Notif Sedang Diproses',
            self::TYPE_COMPLETED, 'SELESAI'     => 'Notif Selesai',
            self::TYPE_REJECTED, 'DITOLAK'      => 'Notif Penolakan',
            'RATING_REMINDER'                   => 'Pengingat Rating',
            self::TYPE_ALUMNI_SUBMITTED         => 'Notif Pendaftaran Alumni',
            self::TYPE_ALUMNI_APPROVED          => 'Notif Akun Alumni Disetujui',
            self::TYPE_ALUMNI_REJECTED          => 'Notif Pendaftaran Alumni Ditolak',
            self::TYPE_PASSWORD_RESET           => 'Reset Password Alumni',
            default                             => 'Notif Status (' . $this->type . ')',
        };
    }

    // =============================================
    // RELATIONSHIPS
    // =============================================

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function alumniRegistration(): BelongsTo
    {
        return $this->belongsTo(AlumniRegistration::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

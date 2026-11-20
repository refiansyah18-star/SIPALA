<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Ticket extends Model
{
    protected $fillable = [
        'ticket_number',
        'user_id',
        'service_id',
        'category_id',
        'program_study_id',
        'unit_id',
        'status_id',
        'subject',
        'description',
        'sla_duration',
        'sla_unit',
        'deadline',
        'rejection_reason',
        'resolution_note',
        'resolved_at',
    ];

    protected $casts = [
        'deadline' => 'datetime',
        'resolved_at' => 'datetime',
        'sla_duration' => 'integer',
    ];

    // =============================================
    // RELATIONSHIPS
    // =============================================

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function programStudy(): BelongsTo
    {
        return $this->belongsTo(ProgramStudy::class);
    }

    public function serviceUnit(): BelongsTo
    {
        return $this->belongsTo(ServiceUnit::class, 'unit_id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class);
    }

    public function fieldValues(): HasMany
    {
        return $this->hasMany(TicketFieldValue::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(TicketAttachment::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(TicketAssignment::class);
    }

    public function latestAssignment(): HasOne
    {
        return $this->hasOne(TicketAssignment::class)->latestOfMany('assigned_at');
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(TicketStatusHistory::class)->orderBy('created_at');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(TicketMessage::class)->orderBy('created_at');
    }

    public function emailNotifications(): HasMany
    {
        return $this->hasMany(EmailNotification::class);
    }

    public function rating(): HasOne
    {
        return $this->hasOne(TicketRating::class);
    }

    // =============================================
    // SCOPES
    // =============================================

    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByStatus($query, string $statusName)
    {
        return $query->whereHas('status', fn($q) => $q->where('name', $statusName));
    }

    public function scopeOverdue($query)
    {
        return $query->where('deadline', '<', now())
                     ->whereHas('status', fn($q) => $q->where('is_terminal', false));
    }

    public function scopeNearDeadline($query, int $hours = 24)
    {
        return $query->whereBetween('deadline', [now(), now()->addHours($hours)])
                     ->whereHas('status', fn($q) => $q->where('is_terminal', false));
    }

    public function scopeAssignedTo($query, int $staffId)
    {
        return $query->whereHas('assignments', fn($q) => $q->where('staff_id', $staffId));
    }

    public function scopeUnassigned($query)
    {
        return $query->doesntHave('assignments');
    }

    // =============================================
    // ACCESSORS
    // =============================================

    /**
     * Get SLA status: NORMAL, NEAR_DEADLINE, OVERDUE.
     */
    public function getSlaStatusAttribute(): string
    {
        if ($this->status && $this->status->is_terminal) {
            return 'COMPLETED';
        }

        $now = Carbon::now();

        if ($now->greaterThan($this->deadline)) {
            return 'OVERDUE';
        }

        if ($now->diffInHours($this->deadline) <= 24) {
            return 'NEAR_DEADLINE';
        }

        return 'NORMAL';
    }

    /**
     * Check if chat is open (ticket not in terminal status).
     */
    public function getIsChatOpenAttribute(): bool
    {
        return $this->status && !$this->status->is_terminal;
    }

    // =============================================
    // STATIC HELPERS
    // =============================================

    /**
     * Generate unique ticket number.
     * Format: SIPALA-YYYY-NNNNNN
     */
    public static function generateTicketNumber(): string
    {
        $prefix = config('sipala.ticket_prefix', 'SIPALA');
        $year = date('Y');

        $lastTicket = static::where('ticket_number', 'like', "{$prefix}-{$year}-%")
                            ->orderByRaw('CAST(SUBSTRING_INDEX(ticket_number, "-", -1) AS UNSIGNED) DESC')
                            ->first();

        if ($lastTicket) {
            $lastNumber = (int) substr($lastTicket->ticket_number, -6);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return sprintf('%s-%s-%06d', $prefix, $year, $nextNumber);
    }

    /**
     * Calculate deadline based on service SLA.
     */
    public static function calculateDeadline(int $duration, string $unit): Carbon
    {
        $now = Carbon::now();

        return match ($unit) {
            'jam' => $now->addHours($duration),
            'hari' => $now->addDays($duration),
            'hari_kerja' => static::addBusinessDays($now, $duration),
            default => $now->addDays($duration),
        };
    }

    /**
     * Add business days (skip Saturday & Sunday).
     */
    private static function addBusinessDays(Carbon $date, int $days): Carbon
    {
        $result = $date->copy();
        $added = 0;

        while ($added < $days) {
            $result->addDay();
            if (!$result->isWeekend()) {
                $added++;
            }
        }

        return $result;
    }

    /**
     * Memeriksa apakah status tiket dapat dialihkan ke status target (State Machine Enforcer).
     */
    public function canTransitionTo(string $targetStatusName): bool
    {
        $current = $this->status ? $this->status->name : null;

        // Tiket yang sudah berstatus akhir (SELESAI / DITOLAK) tidak dapat dipindahkan lagi
        if (in_array($current, [Status::SELESAI, Status::DITOLAK], true) || ($this->status && $this->status->is_terminal)) {
            return false;
        }

        return match ($targetStatusName) {
            // DIAJUKAN -> DIVERIFIKASI
            Status::DIVERIFIKASI => ($current === Status::DIAJUKAN),

            // DIVERIFIKASI -> DIPROSES (atau perpanjangan pengerjaan saat sudah DIPROSES)
            Status::DIPROSES => in_array($current, [Status::DIVERIFIKASI, Status::DIPROSES], true),

            // DIPROSES -> SELESAI (wajib melalui tahap pengerjaan terlebih dahulu)
            Status::SELESAI => ($current === Status::DIPROSES),

            // DIAJUKAN / DIVERIFIKASI / DIPROSES -> DITOLAK
            Status::DITOLAK => in_array($current, [Status::DIAJUKAN, Status::DIVERIFIKASI, Status::DIPROSES], true),

            default => false,
        };
    }
}

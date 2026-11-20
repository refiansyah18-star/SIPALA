<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'action',
        'module',
        'reference_id',
        'description',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function ($model) {
            $model->created_at = $model->created_at ?? now();
        });
    }

    // =============================================
    // ACTION CONSTANTS
    // =============================================

    const ACTION_LOGIN = 'LOGIN';
    const ACTION_CREATE = 'CREATE';
    const ACTION_UPDATE = 'UPDATE';
    const ACTION_DELETE = 'DELETE';
    const ACTION_VERIFY = 'VERIFY';
    const ACTION_REJECT = 'REJECT';
    const ACTION_ASSIGN = 'ASSIGN';
    const ACTION_COMPLETE = 'COMPLETE';
    const ACTION_STATUS_CHANGE = 'STATUS_CHANGE';

    // =============================================
    // MODULE CONSTANTS
    // =============================================

    const MODULE_AUTH = 'AUTH';
    const MODULE_CATEGORY = 'CATEGORY';
    const MODULE_SERVICE = 'SERVICE';
    const MODULE_TICKET = 'TICKET';
    const MODULE_USER = 'USER';
    const MODULE_STAFF = 'STAFF';
    const MODULE_STATUS = 'STATUS';
    const MODULE_UNIT = 'UNIT';
    const MODULE_PRODI = 'PRODI';
    const MODULE_ALUMNI = 'ALUMNI';

    // =============================================
    // RELATIONSHIPS
    // =============================================

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // =============================================
    // STATIC HELPERS
    // =============================================

    /**
     * Log an activity.
     */
    public static function log(
        int $userId,
        string $action,
        string $module,
        ?int $referenceId = null,
        ?string $description = null
    ): self {
        return static::create([
            'user_id' => $userId,
            'action' => $action,
            'module' => $module,
            'reference_id' => $referenceId,
            'description' => $description,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    // =============================================
    // SCOPES
    // =============================================

    public function scopeByModule($query, string $module)
    {
        return $query->where('module', $module);
    }

    public function scopeByAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}

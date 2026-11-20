<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceTarget extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'service_id',
        'user_type_id',
    ];

    // Manually handle created_at since we only have one timestamp column
    protected static function booted(): void
    {
        static::creating(function ($model) {
            $model->created_at = $model->created_at ?? now();
        });
    }

    // =============================================
    // RELATIONSHIPS
    // =============================================

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function userType(): BelongsTo
    {
        return $this->belongsTo(UserType::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceField extends Model
{
    protected $fillable = [
        'service_id',
        'label',
        'field_type',
        'options',
        'placeholder',
        'is_required',
        'sort_order',
    ];

    protected $casts = [
        'options' => 'array',
        'is_required' => 'boolean',
    ];

    // =============================================
    // RELATIONSHIPS
    // =============================================

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function ticketValues(): HasMany
    {
        return $this->hasMany(TicketFieldValue::class);
    }
}

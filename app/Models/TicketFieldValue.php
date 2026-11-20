<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketFieldValue extends Model
{
    protected $fillable = [
        'ticket_id',
        'service_field_id',
        'value',
    ];

    // =============================================
    // RELATIONSHIPS
    // =============================================

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function serviceField(): BelongsTo
    {
        return $this->belongsTo(ServiceField::class);
    }
}

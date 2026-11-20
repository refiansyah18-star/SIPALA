<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketMessage extends Model
{
    protected $fillable = [
        'ticket_id',
        'user_id',
        'message',
    ];

    // =============================================
    // CONSTANTS
    // =============================================

    const MAX_WORDS = 50;

    // =============================================
    // RELATIONSHIPS
    // =============================================

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // =============================================
    // VALIDATION HELPERS
    // =============================================

    /**
     * Validate that message does not exceed word limit.
     */
    public static function validateWordCount(string $message): bool
    {
        return str_word_count($message) <= static::MAX_WORDS;
    }
}

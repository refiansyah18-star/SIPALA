<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceRequirement extends Model
{
    protected $fillable = [
        'service_id',
        'name',
        'description',
        'is_required',
        'allowed_file_types',
        'max_file_size',
        'max_file_count',
        'sort_order',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'max_file_size' => 'integer',
        'max_file_count' => 'integer',
    ];

    // =============================================
    // RELATIONSHIPS
    // =============================================

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(TicketAttachment::class, 'requirement_id');
    }

    // =============================================
    // HELPERS
    // =============================================

    /**
     * Get allowed file types as array.
     */
    public function getAllowedTypesArrayAttribute(): array
    {
        return array_map('trim', explode(',', $this->allowed_file_types));
    }

    /**
     * Get max file size in bytes.
     */
    public function getMaxFileSizeBytesAttribute(): int
    {
        return $this->max_file_size * 1024;
    }
}

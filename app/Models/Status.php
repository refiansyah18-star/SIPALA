<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Status extends Model
{
    protected $fillable = [
        'name',
        'label',
        'color',
        'icon',
        'sort_order',
        'is_default',
        'is_terminal',
        'is_active',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_terminal' => 'boolean',
        'is_active' => 'boolean',
    ];

    // =============================================
    // CONSTANTS
    // =============================================

    const DIAJUKAN = 'DIAJUKAN';
    const DIVERIFIKASI = 'DIVERIFIKASI';
    const DIPROSES = 'DIPROSES';
    const SELESAI = 'SELESAI';
    const DITOLAK = 'DITOLAK';

    // =============================================
    // RELATIONSHIPS
    // =============================================

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function historiesFrom(): HasMany
    {
        return $this->hasMany(TicketStatusHistory::class, 'from_status_id');
    }

    public function historiesTo(): HasMany
    {
        return $this->hasMany(TicketStatusHistory::class, 'to_status_id');
    }

    // =============================================
    // SCOPES
    // =============================================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    // =============================================
    // STATIC HELPERS
    // =============================================

    /**
     * Get the default initial status.
     */
    public static function getDefault(): ?self
    {
        return static::where('is_default', true)->first();
    }

    /**
     * Get status by name.
     */
    public static function findByName(string $name): ?self
    {
        return static::where('name', $name)->first();
    }
}

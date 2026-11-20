<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceUnit extends Model
{
    protected $fillable = [
        'name',
        'type',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // =============================================
    // RELATIONSHIPS
    // =============================================

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'unit_id');
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class, 'unit_id');
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'unit_id');
    }

    // =============================================
    // SCOPES
    // =============================================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProgramStudy extends Model
{
    protected $table = 'program_studies';

    protected $fillable = [
        'code',
        'name',
        'level',
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
        return $this->hasMany(User::class, 'program_study_id');
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'program_study_id');
    }

    // =============================================
    // SCOPES
    // =============================================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}

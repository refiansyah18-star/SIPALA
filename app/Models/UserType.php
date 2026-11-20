<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserType extends Model
{
    protected $fillable = [
        'name',
        'label',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // =============================================
    // CONSTANTS
    // =============================================

    const MAHASISWA = 'MAHASISWA';
    const ALUMNI = 'ALUMNI';
    const DOSEN_PEGAWAI = 'DOSEN_PEGAWAI';
    const STAFF = 'STAFF';
    const ADMIN = 'ADMIN';
    const SUPER_ADMIN = 'SUPER_ADMIN';

    // =============================================
    // RELATIONSHIPS
    // =============================================

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function serviceTargets(): HasMany
    {
        return $this->hasMany(ServiceTarget::class);
    }

    // =============================================
    // SCOPES
    // =============================================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}

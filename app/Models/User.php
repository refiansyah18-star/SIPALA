<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    const AUTH_SOURCE_BRIDGE = 'BRIDGE';
    const AUTH_SOURCE_LOCAL  = 'LOCAL';

    protected $fillable = [
        'user_type_id',
        'program_study_id',
        'unit_id',
        'nama',
        'email',
        'nomor_identitas',
        'no_hp',
        'tahun_lulus',
        'password',
        'auth_source',
        'avatar',
        'is_active',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    // =============================================
    // RELATIONSHIPS
    // =============================================

    public function userType(): BelongsTo
    {
        return $this->belongsTo(UserType::class);
    }

    public function programStudy(): BelongsTo
    {
        return $this->belongsTo(ProgramStudy::class);
    }

    public function serviceUnit(): BelongsTo
    {
        return $this->belongsTo(ServiceUnit::class, 'unit_id');
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function assignedTickets(): HasMany
    {
        return $this->hasMany(TicketAssignment::class, 'staff_id');
    }

    public function staffSpecialties(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'staff_specialties', 'user_id', 'service_id')
                     ->withPivot('created_at');
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function ticketMessages(): HasMany
    {
        return $this->hasMany(TicketMessage::class);
    }

    public function alumniRegistrations(): HasMany
    {
        return $this->hasMany(AlumniRegistration::class, 'user_id');
    }

    // =============================================
    // ROLE HELPERS
    // =============================================

    public function isMahasiswa(): bool
    {
        return $this->userType->name === UserType::MAHASISWA;
    }

    public function isAlumni(): bool
    {
        return $this->userType->name === UserType::ALUMNI;
    }

    public function isDosenPegawai(): bool
    {
        return $this->userType->name === UserType::DOSEN_PEGAWAI;
    }

    public function isStaff(): bool
    {
        return $this->userType->name === UserType::STAFF;
    }

    /**
     * Check if user is ADMIN (manajer — assign tugas, handle tiket).
     */
    public function isAdmin(): bool
    {
        return $this->userType->name === UserType::ADMIN;
    }

    /**
     * Check if user is SUPER_ADMIN (Pustikom — akses semua, maintenance).
     */
    public function isSuperAdmin(): bool
    {
        return $this->userType->name === UserType::SUPER_ADMIN;
    }

    /**
     * Check if user is a pengelola (SUPER_ADMIN, ADMIN, atau STAFF).
     */
    public function isPengelola(): bool
    {
        return in_array($this->userType->name, [
            UserType::SUPER_ADMIN,
            UserType::ADMIN,
            UserType::STAFF,
        ]);
    }

    /**
     * Check if the user is an end-user (non-admin, non-staff).
     */
    public function isEndUser(): bool
    {
        return in_array($this->userType->name, [
            UserType::MAHASISWA,
            UserType::ALUMNI,
            UserType::DOSEN_PEGAWAI,
        ]);
    }

    /**
     * Check if the user authenticates against SIPALA local database.
     */
    public function isLocalAuth(): bool
    {
        return $this->auth_source === self::AUTH_SOURCE_LOCAL;
    }

    /**
     * Check if the user authenticates via campus SSO Bridge API.
     */
    public function isBridgeAuth(): bool
    {
        return !$this->isLocalAuth();
    }

    // =============================================
    // SCOPES
    // =============================================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, string $typeName)
    {
        return $query->whereHas('userType', function ($q) use ($typeName) {
            $q->where('name', $typeName);
        });
    }

    public function scopeStaffUsers($query)
    {
        return $query->byType(UserType::STAFF);
    }
}

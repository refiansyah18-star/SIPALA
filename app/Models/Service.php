<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    protected $fillable = [
        'category_id',
        'unit_id',
        'name',
        'description',
        'sla_duration',
        'sla_unit',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sla_duration' => 'integer',
    ];

    // =============================================
    // RELATIONSHIPS
    // =============================================

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function serviceUnit(): BelongsTo
    {
        return $this->belongsTo(ServiceUnit::class, 'unit_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(ServiceUnit::class, 'unit_id');
    }

    public function targets(): HasMany
    {
        return $this->hasMany(ServiceTarget::class);
    }

    public function targetUserTypes(): BelongsToMany
    {
        return $this->belongsToMany(UserType::class, 'service_targets', 'service_id', 'user_type_id');
    }

    public function requirements(): HasMany
    {
        return $this->hasMany(ServiceRequirement::class)->orderBy('sort_order');
    }

    public function fields(): HasMany
    {
        return $this->hasMany(ServiceField::class)->orderBy('sort_order');
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function specializedStaff(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'staff_specialties', 'service_id', 'user_id');
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
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Filter services by user type name.
     * Only returns services that target the given user type.
     */
    public function scopeForUserType($query, string $userTypeName)
    {
        return $query->whereHas('targetUserTypes', function ($q) use ($userTypeName) {
            $q->where('name', $userTypeName);
        });
    }

    /**
     * Filter services available for a specific user type (active only).
     */
    public function scopeAvailableFor($query, string $userTypeName)
    {
        return $query->active()->forUserType($userTypeName);
    }

    // =============================================
    // HELPERS
    // =============================================

    /**
     * Get SLA description text.
     */
    public function getSlaDescriptionAttribute(): string
    {
        $unitLabels = [
            'jam' => 'jam',
            'hari' => 'hari',
            'hari_kerja' => 'hari kerja',
        ];

        $unit = $unitLabels[$this->sla_unit] ?? $this->sla_unit;
        return "{$this->sla_duration} {$unit}";
    }
}

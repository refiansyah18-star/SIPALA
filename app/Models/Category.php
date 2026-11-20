<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = [
        'name',
        'description',
        'icon',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Map of common icon aliases to valid FontAwesome 6 Free solid icon names.
     */
    public static function normalizeIconName(?string $value): string
    {
        $icon = strtolower(trim((string) $value));

        if ($icon === '') {
            return 'folder';
        }

        // Strip FontAwesome prefixes if user typed "fas fa-desktop" or "fa-desktop"
        $icon = preg_replace('/^(fa-solid|fa-regular|fa-brands|fas|far|fab)\s+/i', '', $icon);
        $icon = preg_replace('/^fa-/i', '', trim($icon));
        $icon = str_replace(' ', '-', trim($icon));

        $aliases = [
            'monitor'    => 'desktop',
            'screen'     => 'desktop',
            'computer'   => 'desktop',
            'komputer'   => 'desktop',
            'pc'         => 'desktop',
            'sistem'     => 'desktop',
            'system'     => 'desktop',
            'it'         => 'laptop-code',
            'akademik'   => 'book-open',
            'buku'       => 'book-open',
            'dokumen'    => 'folder',
            'data'       => 'folder',
            'file'       => 'file-alt',
            'wisuda'     => 'graduation-cap',
            'kelulusan'  => 'award',
            'alumni'     => 'users',
            'dosen'      => 'briefcase',
            'pegawai'    => 'briefcase',
            'settings'   => 'cogs',
            'setting'    => 'cog',
            'pengaturan' => 'cogs',
            'kendala'    => 'desktop',
            'help'       => 'question-circle',
            'bantuan'    => 'headset',
        ];

        return $aliases[$icon] ?? ($icon ?: 'folder');
    }

    public function getIconAttribute($value): string
    {
        return self::normalizeIconName($value);
    }

    public function setIconAttribute($value): void
    {
        $this->attributes['icon'] = self::normalizeIconName($value);
    }

    // =============================================
    // RELATIONSHIPS
    // =============================================

    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
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
}

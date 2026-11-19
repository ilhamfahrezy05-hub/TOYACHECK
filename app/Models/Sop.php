<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Sop extends Model
{
    /**
     * Daftar resmi role beserta urutan tampil sesuai dokumen SOP.
     */
    public const ROLES = [
        'driver'    => 'Driver',
        'hse'       => 'HSE',
        'mekanik'   => 'Mekanik',
        'admin'     => 'Admin',
        'pengadaan' => 'Pengadaan (Procurement)',
    ];

    /**
     * Metadata ikon dan warna badge tiap role untuk tampilan UI.
     */
    public const ROLE_META = [
        'driver'    => ['icon' => 'fa-truck',           'color' => '#2563EB', 'bg' => '#EFF6FF'],
        'hse'       => ['icon' => 'fa-shield-halved',   'color' => '#059669', 'bg' => '#ECFDF5'],
        'mekanik'   => ['icon' => 'fa-wrench',          'color' => '#D97706', 'bg' => '#FFFBEB'],
        'admin'     => ['icon' => 'fa-user-tie',        'color' => '#4F46E5', 'bg' => '#EEF2FF'],
        'pengadaan' => ['icon' => 'fa-cart-flatbed',    'color' => '#0891B2', 'bg' => '#ECFEFF'],
    ];

    protected $fillable = [
        'role_target',
        'urutan',
        'isi_prosedur',
    ];

    protected $casts = [
        'urutan' => 'integer',
    ];

    /**
     * Label nama role yang mudah dibaca.
     */
    public function getRoleLabelAttribute(): string
    {
        return self::ROLES[$this->role_target] ?? ucfirst($this->role_target);
    }

    /**
     * Scope pengurutan standar berdasarkan urutan role & nomor urut prosedur.
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query
            ->orderByRaw("FIELD(role_target, 'driver', 'hse', 'mekanik', 'admin', 'pengadaan')")
            ->orderBy('urutan')
            ->orderBy('id');
    }
}

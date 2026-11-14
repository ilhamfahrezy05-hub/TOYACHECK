<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Master parameter inspeksi (SRS: tabel Checklist).
 *
 * Hirarki 2 level via parent_id:
 *  - parent_id NULL      => grup (mis. "Ban & Rem")
 *  - parent_id terisi    => item pemeriksaan yang dijawab saat inspeksi
 */
class InspectionItem extends Model
{
    public const CATEGORIES = [
        'fisik' => 'Fisik',
        'k3'    => 'K3 & Kebersihan',
    ];

    protected $fillable = [
        'parent_id',
        'nama',
        'category',
        'criteria',
        'urutan',
        'is_active',
        'user_edit',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')
            ->orderBy('urutan')
            ->orderBy('id');
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_edit');
    }

    public function checklistItems(): HasMany
    {
        return $this->hasMany(ChecklistItem::class);
    }

    public function isGroup(): bool
    {
        return $this->parent_id === null;
    }

    public function scopeGroups(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Item yang harus dijawab pada inspeksi tipe tertentu:
     * item aktif, dengan grup yang juga aktif.
     */
    public function scopeAnswerable(Builder $query, string $type): Builder
    {
        return $query->whereNotNull('parent_id')
            ->where('is_active', true)
            ->where('category', $type)
            ->whereHas('parent', fn (Builder $q) => $q->where('is_active', true))
            ->orderBy('parent_id')
            ->orderBy('urutan')
            ->orderBy('id');
    }
}

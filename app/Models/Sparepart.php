<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sparepart extends Model
{
    protected $fillable = [
        'kode_sparepart',
        'nama_sparepart',
        'kategori',
        'kategori_abc',
        'tipe_item',
        'frekuensi',
        'stok',
        'satuan',
        'harga',
        'minimal_stok',
        'keterangan',
        'lokasi',
    ];

    protected $casts = [
        'stok'         => 'integer',
        'harga'        => 'decimal:2',
        'minimal_stok' => 'integer',
    ];

    public function maintenanceDetails(): HasMany
    {
        return $this->hasMany(MaintenanceDetail::class);
    }

    public function isLowStock(): bool
    {
        return $this->stok <= $this->minimal_stok;
    }

    public function isMinimumStock(): bool
    {
        return $this->stok === $this->minimal_stok;
    }
}

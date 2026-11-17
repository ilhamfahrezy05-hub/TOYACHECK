<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vendor extends Model
{
    protected $fillable = [
        'nama_vendor',
        'kontak_person',
        'telepon',
        'email',
        'alamat',
        'kota',
        'provinsi',
        'latitude',
        'longitude',
        'keahlian',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    protected $fillable = [
        'nama_vendor',
        'kontak_person',
        'telepon',
        'email',
        'alamat',
        'keahlian',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}

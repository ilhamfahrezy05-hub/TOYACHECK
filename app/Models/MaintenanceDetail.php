<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaintenanceDetail extends Model
{
    protected $fillable = [
        'maintenance_id',
        'sparepart_id',
        'nama_sparepart',
        'qty',
    ];

    protected $casts = [
        'qty' => 'integer',
    ];

    public function maintenance(): BelongsTo
    {
        return $this->belongsTo(Maintenance::class);
    }

    public function sparepart(): BelongsTo
    {
        return $this->belongsTo(Sparepart::class);
    }
}

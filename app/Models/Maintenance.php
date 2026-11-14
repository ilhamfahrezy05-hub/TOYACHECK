<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Maintenance extends Model
{
    public const STATUS_PENDING     = 'pending';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED   = 'completed';

    public const STATUSES = [
        self::STATUS_PENDING     => 'Pending',
        self::STATUS_IN_PROGRESS => 'Sedang Dikerjakan',
        self::STATUS_COMPLETED   => 'Selesai',
    ];

    protected $fillable = [
        'vehicle_id',
        'mekanik_id',
        'nama_driver',
        'checklist_id',
        'tanggal_maintenance',
        'status_maintenance',
        'kategori_kendala',
        'estimasi_downtime',
        'deskripsi_masalah',
        'solusi',
        'catatan',
    ];

    protected $casts = [
        'tanggal_maintenance' => 'datetime',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function mekanik(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mekanik_id');
    }

    public function checklist(): BelongsTo
    {
        return $this->belongsTo(Checklist::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(MaintenanceDetail::class);
    }

    public function sparepartRequests(): HasMany
    {
        return $this->hasMany(SparepartRequest::class);
    }
}

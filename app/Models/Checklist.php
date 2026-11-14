<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Checklist extends Model
{
    protected $fillable = [

        'vehicle_id',
        'checklist_type',
        'driver_id',
        'user_id',
        'tanggal',
        'status',
        'catatan',
    ];

    protected $casts = [

        'tanggal' => 'datetime',
    ];

    public function vehicle()
    {
        return $this->belongsTo(
            Vehicle::class
        );
    }

    /**
     * User yang menginput data checklist.
     */
    public function user()
    {
        return $this->belongsTo(
            User::class
        );
    }

    /**
     * Driver / petugas yang melakukan inspeksi.
     */
    public function driver()
    {
        return $this->belongsTo(
            User::class,
            'driver_id'
        );
    }

    public function items()
    {
        return $this->hasMany(
            ChecklistItem::class
        );
    }
}
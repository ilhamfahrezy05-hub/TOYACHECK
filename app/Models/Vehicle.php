<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    /**
     * Status kendaraan sesuai SRS.
     */
    public const STATUS_SIAP_JALAN  = 'siap_jalan';
    public const STATUS_MAINTENANCE = 'maintenance';
    public const STATUS_RUSAK       = 'rusak';
    public const STATUS_NONAKTIF    = 'nonaktif';

    public const STATUSES = [
        self::STATUS_SIAP_JALAN  => 'Siap Jalan',
        self::STATUS_MAINTENANCE => 'Maintenance',
        self::STATUS_RUSAK       => 'Rusak',
        self::STATUS_NONAKTIF    => 'Nonaktif',
    ];

    public const KEPEMILIKAN_INTERNAL  = 'internal';
    public const KEPEMILIKAN_EKSTERNAL = 'eksternal';

    public const KEPEMILIKAN_LABELS = [
        self::KEPEMILIKAN_INTERNAL  => 'Internal',
        self::KEPEMILIKAN_EKSTERNAL => 'Eksternal',
    ];

    protected $fillable = [
        'plat_nomor',
        'merk',
        'jenis_kendaraan',
        'kepemilikan',
        'tahun',
        'dimensi_box',
        'tahun_stnk',
        'tahun_kir',
        'status',
    ];

    public function checklists()
    {
        return $this->hasMany(Checklist::class);
    }

    public function maintenances()
    {
        return $this->hasMany(Maintenance::class);
    }

    /**
     * Driver yang saat ini terikat / mengoperasikan armada ini.
     */
    public function assignedDriver()
    {
        return $this->hasOne(User::class, 'assigned_vehicle_id');
    }
}

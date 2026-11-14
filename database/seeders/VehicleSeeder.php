<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Vehicle;
class VehicleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $vehicles = [
            [
                'plat_nomor'      => 'E 8544 KI',
                'merk'            => 'Toyota Dyna',
                'jenis_kendaraan' => 'Box',
                'tahun'           => 2023,
                'status'          => 'siap_jalan',
            ],
            [
                'plat_nomor'      => 'E 8325 KP',
                'merk'            => 'Toyota Dyna',
                'jenis_kendaraan' => 'Box',
                'tahun'           => 2022,
                'status'          => 'siap_jalan',
            ],
            [
                'plat_nomor'      => 'E 8325 KI',
                'merk'            => 'Granmax',
                'jenis_kendaraan' => 'Pickup Box',
                'tahun'           => 2023,
                'status'          => 'siap_jalan',
            ],
            [
                'plat_nomor'      => 'E 8869 KW',
                'merk'            => 'Toyota Dyna',
                'jenis_kendaraan' => 'Box',
                'tahun'           => 2021,
                'status'          => 'siap_jalan',
            ],
        ];

        foreach ($vehicles as $data) {
            Vehicle::updateOrCreate(
                ['kode_armada' => $data['kode_armada']],
                $data
            );
        }
    }
}

<?php

namespace Database\Seeders;

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
                'merk'            => 'Toyota Dyna 110 FT',
                'jenis_kendaraan' => 'Truk Engkel',
                'tahun'           => 2023,
                'dimensi_box'     => '310 x 175 x 175 cm',
                'tahun_stnk'      => '2028',
                'tahun_kir'       => '2027',
                'status'          => 'siap_jalan',
            ],
            [
                'plat_nomor'      => 'E 8325 KP',
                'merk'            => 'Toyota Dyna 130 HT',
                'jenis_kendaraan' => 'CDD Box',
                'tahun'           => 2022,
                'dimensi_box'     => '430 x 200 x 200 cm',
                'tahun_stnk'      => '2027',
                'tahun_kir'       => '2026',
                'status'          => 'siap_jalan',
            ],
            [
                'plat_nomor'      => 'E 8325 KI',
                'merk'            => 'Daihatsu Gran Max',
                'jenis_kendaraan' => 'Pickup Box',
                'tahun'           => 2023,
                'dimensi_box'     => '235 x 155 x 135 cm',
                'tahun_stnk'      => '2028',
                'tahun_kir'       => '2027',
                'status'          => 'siap_jalan',
            ],
            [
                'plat_nomor'      => 'E 8869 KW',
                'merk'            => 'Toyota Dyna 110 FT',
                'jenis_kendaraan' => 'Truk Engkel',
                'tahun'           => 2021,
                'dimensi_box'     => '310 x 175 x 175 cm',
                'tahun_stnk'      => '2026',
                'tahun_kir'       => '2026',
                'status'          => 'siap_jalan',
            ],
            [
                'plat_nomor'      => 'E 9142 AB',
                'merk'            => 'Mitsubishi Canter FE 74 HD',
                'jenis_kendaraan' => 'CDD Box',
                'tahun'           => 2022,
                'dimensi_box'     => '435 x 200 x 205 cm',
                'tahun_stnk'      => '2027',
                'tahun_kir'       => '2027',
                'status'          => 'siap_jalan',
            ],
            [
                'plat_nomor'      => 'E 8091 KA',
                'merk'            => 'Hino Dutro 130 HD',
                'jenis_kendaraan' => 'CDD Box',
                'tahun'           => 2021,
                'dimensi_box'     => '440 x 205 x 205 cm',
                'tahun_stnk'      => '2026',
                'tahun_kir'       => '2026',
                'status'          => 'maintenance',
            ],
            [
                'plat_nomor'      => 'E 9055 YZ',
                'merk'            => 'Isuzu Elf NMR 71',
                'jenis_kendaraan' => 'CDD Box',
                'tahun'           => 2024,
                'dimensi_box'     => '430 x 200 x 200 cm',
                'tahun_stnk'      => '2029',
                'tahun_kir'       => '2027',
                'status'          => 'siap_jalan',
            ],
            [
                'plat_nomor'      => 'E 8710 HG',
                'merk'            => 'Daihatsu Gran Max',
                'jenis_kendaraan' => 'Pickup Box',
                'tahun'           => 2023,
                'dimensi_box'     => '235 x 155 x 135 cm',
                'tahun_stnk'      => '2028',
                'tahun_kir'       => '2027',
                'status'          => 'siap_jalan',
            ],
        ];

        foreach ($vehicles as $vehicle) {
            Vehicle::updateOrCreate(
                ['plat_nomor' => $vehicle['plat_nomor']],
                $vehicle
            );
        }
    }
}

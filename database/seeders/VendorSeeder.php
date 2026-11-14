<?php

namespace Database\Seeders;

use App\Models\Vendor;
use Illuminate\Database\Seeder;

class VendorSeeder extends Seeder
{
    /**
     * Contoh vendor supplier suku cadang. Aman dijalankan berulang.
     */
    public function run(): void
    {
        $vendors = [
            [
                'nama_vendor'   => 'PT Ban Nusantara',
                'kontak_person' => 'Hendra',
                'telepon'       => '081234567801',
                'email'         => 'sales@bannusantara.test',
                'alamat'        => 'Jl. Industri Raya No. 12, Bekasi',
                'keahlian'      => 'Ban & Velg',
            ],
            [
                'nama_vendor'   => 'CV Aki Sentosa',
                'kontak_person' => 'Lina',
                'telepon'       => '081234567802',
                'email'         => 'order@akisentosa.test',
                'alamat'        => 'Jl. Raya Cibitung No. 45, Bekasi',
                'keahlian'      => 'Aki & Kelistrikan',
            ],
            [
                'nama_vendor'   => 'Bengkel Pusat Oli Jaya',
                'kontak_person' => 'Agus',
                'telepon'       => '081234567803',
                'email'         => null,
                'alamat'        => 'Jl. Pahlawan No. 8, Karawang',
                'keahlian'      => 'Oli & Filter',
            ],
        ];

        foreach ($vendors as $data) {
            Vendor::updateOrCreate(
                ['nama_vendor' => $data['nama_vendor']],
                $data + ['is_active' => true]
            );
        }
    }
}

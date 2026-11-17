<?php

namespace Database\Seeders;

use App\Models\Vendor;
use Illuminate\Database\Seeder;

class VendorSeeder extends Seeder
{
    /**
     * Direktori vendor supplier suku cadang sesuai Figma Pengadaan (node 917-10746).
     * Aman dijalankan berulang (idempotent).
     */
    public function run(): void
    {
        $vendors = [
            [
                'nama_vendor'   => 'Champion ban',
                'kontak_person' => 'Hendra Wijaya',
                'telepon'       => '081234567801',
                'email'         => 'champion.ban@gmail.com',
                'alamat'        => 'Jl. Raya No.KM.8, Ciperna',
                'kota'          => 'Cirebon',
                'provinsi'      => 'Jawa Barat',
                'latitude'      => '-6.7645',
                'longitude'     => '108.5256',
                'keahlian'      => 'Tires',
            ],
            [
                'nama_vendor'   => 'Maju Abadi',
                'kontak_person' => 'Bambang Sutrisno',
                'telepon'       => '081234567802',
                'email'         => 'maju.abadi@gmail.com',
                'alamat'        => 'Jl. Kalitanjung No.55',
                'kota'          => 'Cirebon',
                'provinsi'      => 'Jawa Barat',
                'latitude'      => '-6.7421',
                'longitude'     => '108.5412',
                'keahlian'      => 'Engines',
            ],
            [
                'nama_vendor'   => 'Sinar Jaya Diesel',
                'kontak_person' => 'Surya Darma',
                'telepon'       => '081234567803',
                'email'         => 'sinar.jaya@gmail.com',
                'alamat'        => 'Kawasan Industri Arjawinangun',
                'kota'          => 'Cirebon',
                'provinsi'      => 'Jawa Barat',
                'latitude'      => '-6.6475',
                'longitude'     => '108.4112',
                'keahlian'      => 'Diesel Parts',
            ],
            [
                'nama_vendor'   => 'Global Truck Parts',
                'kontak_person' => 'Rudi Hartono',
                'telepon'       => '081234567804',
                'email'         => 'global.truck@gmail.com',
                'alamat'        => 'Jl. Kesambi No.102',
                'kota'          => 'Cirebon',
                'provinsi'      => 'Jawa Barat',
                'latitude'      => '-6.7320',
                'longitude'     => '108.5523',
                'keahlian'      => 'Body Work',
            ],
            [
                'nama_vendor'   => 'Tunggal Mandiri',
                'kontak_person' => 'Antonius',
                'telepon'       => '081234567805',
                'email'         => 'tunggal.mandiri@gmail.com',
                'alamat'        => 'Jl. Brigjen Darsono No.12',
                'kota'          => 'Cirebon',
                'provinsi'      => 'Jawa Barat',
                'latitude'      => '-6.7235',
                'longitude'     => '108.5380',
                'keahlian'      => 'Brakes',
            ],
            [
                'nama_vendor'   => 'Prima Spareparts',
                'kontak_person' => 'Lina Marlina',
                'telepon'       => '081234567806',
                'email'         => 'prima.parts@gmail.com',
                'alamat'        => 'Jl. Tentara Pelajar No.4',
                'kota'          => 'Cirebon',
                'provinsi'      => 'Jawa Barat',
                'latitude'      => '-6.7185',
                'longitude'     => '108.5590',
                'keahlian'      => 'Electronics',
            ],
            [
                'nama_vendor'   => 'Wahana Filter',
                'kontak_person' => 'Dedi Kurniawan',
                'telepon'       => '081234567807',
                'email'         => 'wahana.filter@gmail.com',
                'alamat'        => 'Pergudangan Plumbon Blok C',
                'kota'          => 'Cirebon',
                'provinsi'      => 'Jawa Barat',
                'latitude'      => '-6.7042',
                'longitude'     => '108.4745',
                'keahlian'      => 'Filters',
            ],
            [
                'nama_vendor'   => 'Mitra Otomotif Sejahtera',
                'kontak_person' => 'Gunawan',
                'telepon'       => '081234567808',
                'email'         => 'mitra.otomotif@gmail.com',
                'alamat'        => 'Jl. Tuparev No. 88, Kedawung',
                'kota'          => 'Cirebon',
                'provinsi'      => 'Jawa Barat',
                'latitude'      => '-6.7112',
                'longitude'     => '108.5340',
                'keahlian'      => 'Brakes & Suspension',
            ],
            [
                'nama_vendor'   => 'PT. Nusantara Perkasa',
                'kontak_person' => 'Haryanto',
                'telepon'       => '081234567809',
                'email'         => 'info@nusantaraperkasa.co.id',
                'alamat'        => 'Jl. Raya Pantura KM 14, Mundu',
                'kota'          => 'Cirebon',
                'provinsi'      => 'Jawa Barat',
                'latitude'      => '-6.7580',
                'longitude'     => '108.5890',
                'keahlian'      => 'Suku Cadang Umum',
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

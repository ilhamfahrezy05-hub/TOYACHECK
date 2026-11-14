<?php

namespace Database\Seeders;

use App\Models\InspectionItem;
use Illuminate\Database\Seeder;

class InspectionItemSeeder extends Seeder
{
    /**
     * Contoh master item pemeriksaan.
     *
     * Format: jenis => grup => [nama item => kriteria / SOP]
     * Aman dijalankan berulang (updateOrCreate).
     */
    public function run(): void
    {
        $data = [

            'fisik' => [

                'Mesin & Fluida' => [
                    'Oli Mesin'     => 'Level oli di antara batas MIN dan MAX, tidak ada kebocoran.',
                    'Air Radiator'  => 'Level air cukup, tidak ada kebocoran selang.',
                    'Aki / Baterai' => 'Terminal bersih dan kencang, mesin menyala normal.',
                    'Bahan Bakar'   => 'Level bahan bakar cukup untuk rute hari ini.',
                ],

                'Ban & Rem' => [
                    'Ban Depan'    => 'Tekanan sesuai standar, alur ban masih terlihat, tidak ada benjolan.',
                    'Ban Belakang' => 'Tekanan sesuai standar, alur ban masih terlihat, tidak ada benjolan.',
                    'Ban Serep'    => 'Tersedia, tekanan cukup, dan layak pakai.',
                    'Rem'          => 'Pedal rem tidak blong dan tidak berbunyi.',
                ],

                'Kelistrikan' => [
                    'Lampu Utama' => 'Lampu jauh dan dekat menyala.',
                    'Lampu Sein'  => 'Sein kiri dan kanan menyala.',
                    'Lampu Rem'   => 'Menyala saat pedal rem diinjak.',
                    'Klakson'     => 'Berbunyi nyaring.',
                    'Wiper'       => 'Bergerak normal, karet tidak sobek.',
                ],

                'Bodi & Kelengkapan' => [
                    'Bak / Box Muatan' => 'Tidak bocor, pintu bisa terkunci dengan baik.',
                    'Kaca & Spion'     => 'Tidak retak, spion lengkap dan bisa diatur.',
                    'STNK & KIR'       => 'Dokumen ada dan masa berlaku belum habis.',
                ],
            ],

            'k3' => [

                'Peralatan Keselamatan' => [
                    'APAR'              => 'Tersedia, segel utuh, jarum indikator di area hijau.',
                    'Kotak P3K'         => 'Tersedia dan isi lengkap.',
                    'Segitiga Pengaman' => 'Tersedia minimal 1 buah.',
                    'Ganjal Roda'       => 'Tersedia dan kondisi baik.',
                    'Sabuk Pengaman'    => 'Berfungsi dan tidak aus.',
                ],

                'Kebersihan Kendaraan' => [
                    'Bagian luar'                                      => 'Dalam keadaan bersih',
                    'Bak Kendaraan'                                    => 'Dalam keadaan bersih dan bebas kontaminasi najis',
                    'Bak Kendaraan (Kondisi & Kelembapan)'             => 'Bebas dari bau, tidak lembab atau berjamur, tidak berminyak dan kondisinya kering',
                    'Bak kendaraan (Keutuhan)'                         => 'Tidak berlubang atau bocor',
                    'Tidak ditemukan pest atau jejak keberadaan pest'  => '(kecoa, kutu, tikus, dll) dalam bak kendaraan',
                    'Jika ada sisa muatan'                             => 'Pastikan telah dibersihkan, tidak ada bau tak standar',
                    'Dilengkapi dengan terpal'                         => '(khusus kendaraan truk terbuka)',
                    'Tersedia kayu/papan'                              => 'Untuk penahan produk',
                ],
            ],
        ];

        foreach ($data as $category => $groups) {

            $groupOrder = 1;

            foreach ($groups as $groupName => $items) {

                $group = InspectionItem::updateOrCreate(
                    [
                        'parent_id' => null,
                        'nama'      => $groupName,
                    ],
                    [
                        'category'  => $category,
                        'urutan'    => $groupOrder++,
                        'is_active' => true,
                    ]
                );

                $itemOrder = 1;

                foreach ($items as $itemName => $criteria) {

                    InspectionItem::updateOrCreate(
                        [
                            'parent_id' => $group->id,
                            'nama'      => $itemName,
                        ],
                        [
                            'category'  => $category,
                            'criteria'  => $criteria,
                            'urutan'    => $itemOrder++,
                            'is_active' => true,
                        ]
                    );
                }
            }
        }
    }
}

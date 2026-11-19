<?php

namespace Database\Seeders;

use App\Models\Sop;
use Illuminate\Database\Seeder;

class SopSeeder extends Seeder
{
    /**
     * Seed Standar Operasional Prosedur (SOP) awal sesuai dokumen resmi klien.
     */
    public function run(): void
    {
        $sopByRole = [
            'driver' => [
                'Melakukan pemeriksaan kendaraan sebelum kendaraan digunakan untuk operasional distribusi.',
                'Mengisi seluruh item checklist berdasarkan kondisi kendaraan yang sebenarnya.',
                'Mengunggah dokumentasi apabila ditemukan kerusakan.',
                'Menyampaikan laporan apabila ditemukan kondisi kendaraan yang tidak sesuai.',
                'Tidak mengoperasikan kendaraan apabila ditemukan kerusakan yang berpotensi membahayakan keselamatan.',
                'Memilih fitur "Armada Rusak" apabila kendaraan mengalami kerusakan pada hari operasional dan tidak dapat digunakan. Driver wajib mengisi informasi kerusakan serta mengunggah dokumentasi pendukung sebagai laporan kondisi armada.',
                'Memantau status perbaikan kendaraan melalui aplikasi TOYACHECK selama kendaraan berada dalam kondisi rusak atau tidak dapat dioperasikan.',
                'Memilih fitur "Armada Selesai/Siap Dipakai" setelah kendaraan selesai diperbaiki dan telah dinyatakan layak digunakan kembali berdasarkan keputusan pihak berwenang sesuai prosedur perusahaan.',
                'Memantau informasi tindak lanjut atas laporan yang disampaikan.',
            ],
            'hse' => [
                'Melakukan monitoring hasil pemeriksaan kendaraan.',
                'Memeriksa kelengkapan data inspeksi yang dikirimkan Driver.',
                'Mengidentifikasi temuan yang berkaitan dengan keselamatan kendaraan.',
                'Berkoordinasi dengan Mekanik dan Admin Distribusi terkait temuan kerusakan.',
                'Melakukan rekapitulasi kondisi armada.',
                'Menyusun laporan kondisi armada secara bulanan.',
            ],
            'mekanik' => [
                'Melakukan pencatatan inventaris sparepart.',
                'Memperbarui informasi stok barang masuk dan keluar.',
                'Memeriksa ketersediaan sparepart.',
                'Mengidentifikasi kebutuhan barang untuk perawatan dan perbaikan kendaraan.',
                'Membuat pengajuan kebutuhan sparepart melalui aplikasi.',
                'Memeriksa kesesuaian barang yang diterima dengan pengajuan.',
                'Memperbarui data inventaris setelah barang diterima.',
            ],
            'admin' => [
                'Menerima dan memantau seluruh informasi dari Driver, HSE, dan Mekanik melalui aplikasi TOYACHECK.',
                'Melakukan monitoring terhadap pengisian checklist harian Driver untuk memastikan pemeriksaan kendaraan telah dilakukan sebelum kendaraan digunakan untuk operasional.',
                'Memeriksa kelengkapan laporan dan pengajuan sparepart.',
                'Melakukan peninjauan terhadap kebutuhan barang yang diajukan Mekanik.',
                'Memberikan persetujuan awal (approve) atau penolakan (reject) pengajuan sesuai kewenangannya.',
                'Meneruskan pengajuan yang disetujui kepada atasan untuk memperoleh persetujuan lanjutan.',
                'Meneruskan pengajuan yang telah memperoleh persetujuan akhir kepada bagian Pengadaan.',
                'Memantau perkembangan tindak lanjut laporan kerusakan kendaraan dan proses pengadaan sparepart.',
            ],
            'pengadaan' => [
                'Menerima pengajuan yang telah mendapatkan persetujuan sesuai hierarki perusahaan.',
                'Memeriksa spesifikasi dan jumlah kebutuhan barang.',
                'Melakukan pencarian dan pemilihan vendor.',
                'Membandingkan harga, kualitas, serta ketersediaan barang.',
                'Melaksanakan pembelian berdasarkan persetujuan dan prosedur perusahaan.',
                'Mencatat informasi pembelian dan vendor.',
                'Melaporkan perkembangan dan hasil pengadaan kepada pihak terkait.',
            ],
        ];

        foreach ($sopByRole as $role => $items) {
            foreach ($items as $idx => $isi) {
                Sop::updateOrCreate(
                    [
                        'role_target'  => $role,
                        'isi_prosedur' => $isi,
                    ],
                    [
                        'urutan' => $idx + 1,
                    ]
                );
            }
        }
    }
}

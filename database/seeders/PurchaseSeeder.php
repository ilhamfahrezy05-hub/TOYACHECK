<?php

namespace Database\Seeders;

use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Sparepart;
use App\Models\SparepartRequest;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Seeder;

class PurchaseSeeder extends Seeder
{
    /**
     * Seeder data awal Permintaan Suku Cadang & Riwayat Belanja Pengadaan (Figma node 917-10746).
     */
    public function run(): void
    {
        $mekanik   = User::where('username', 'mekanik1')->first() ?? User::first();
        $pengadaan = User::where('username', 'pengadaan1')->first() ?? User::first();

        // Pastikan beberapa permintaan suku cadang tersedia jika tabel masih kosong
        if (SparepartRequest::count() < 5) {
            $sampleRequests = [
                [
                    'nama_sparepart'         => 'Brake Pad Dyna',
                    'qty'                    => 40,
                    'priority_level'         => 'critical',
                    'vendor_name'            => 'Mitra Otomotif Sejahtera',
                    'alasan'                 => 'Penggantian kampas rem utama armada distribusi (Klasifikasi A).',
                    'status'                 => SparepartRequest::STATUS_PENDING,
                    'expected_delivery_date' => now()->addDays(2)->toDateString(),
                ],
                [
                    'nama_sparepart'         => 'Filter Oli Grand max',
                    'qty'                    => 15,
                    'priority_level'         => 'normal',
                    'vendor_name'            => 'Wahana Filter',
                    'alasan'                 => 'Stok rutin service berkala unit Grand Max Box.',
                    'status'                 => SparepartRequest::STATUS_PENDING,
                    'expected_delivery_date' => now()->addDays(4)->toDateString(),
                ],
                [
                    'nama_sparepart'         => 'Ban Michelin R 15',
                    'qty'                    => 8,
                    'priority_level'         => 'critical',
                    'vendor_name'            => 'Champion ban',
                    'alasan'                 => 'Penggantian ban aus hasil temuan checklist fisik driver.',
                    'status'                 => SparepartRequest::STATUS_PENDING,
                    'expected_delivery_date' => now()->addDays(2)->toDateString(),
                ],
                [
                    'nama_sparepart'         => 'Alternator 24V 100A',
                    'qty'                    => 4,
                    'priority_level'         => 'normal',
                    'vendor_name'            => 'Prima Spareparts',
                    'alasan'                 => 'Cadangan kelistrikan untuk unit truk engkel & CDD.',
                    'status'                 => SparepartRequest::STATUS_APPROVED,
                    'expected_delivery_date' => now()->subDays(1)->toDateString(),
                    'reviewed_by'            => $pengadaan?->id,
                    'reviewed_at'            => now()->subDays(1),
                ],
                [
                    'nama_sparepart'         => 'Kit Plat Kopling',
                    'qty'                    => 6,
                    'priority_level'         => 'critical',
                    'vendor_name'            => 'PT. Nusantara Perkasa',
                    'alasan'                 => 'Kebutuhan perbaikan transmisi unit Toyota Dyna.',
                    'status'                 => SparepartRequest::STATUS_APPROVED,
                    'expected_delivery_date' => now()->subDays(2)->toDateString(),
                    'reviewed_by'            => $pengadaan?->id,
                    'reviewed_at'            => now()->subDays(2),
                ],
                [
                    'nama_sparepart'         => 'Shock Breaker Depan',
                    'qty'                    => 10,
                    'priority_level'         => 'normal',
                    'vendor_name'            => 'Tunggal Mandiri',
                    'alasan'                 => 'Restock komponen kaki-kaki bengkel.',
                    'status'                 => SparepartRequest::STATUS_APPROVED,
                    'expected_delivery_date' => now()->subDays(3)->toDateString(),
                    'reviewed_by'            => $pengadaan?->id,
                    'reviewed_at'            => now()->subDays(3),
                ],
            ];

            foreach ($sampleRequests as $reqData) {
                $matchedPart = Sparepart::where('nama_sparepart', 'like', '%' . explode(' ', $reqData['nama_sparepart'])[0] . '%')->first();

                SparepartRequest::firstOrCreate(
                    ['nama_sparepart' => $reqData['nama_sparepart']],
                    [
                        'sparepart_id'           => $matchedPart?->id,
                        'qty'                    => $reqData['qty'],
                        'priority_level'         => $reqData['priority_level'],
                        'vendor_name'            => $reqData['vendor_name'],
                        'requested_by'           => $mekanik?->id,
                        'alasan'                 => $reqData['alasan'],
                        'status'                 => $reqData['status'],
                        'expected_delivery_date' => $reqData['expected_delivery_date'],
                        'reviewed_by'            => $reqData['reviewed_by'] ?? null,
                        'reviewed_at'            => $reqData['reviewed_at'] ?? null,
                    ]
                );
            }
        }

        // Seed contoh Riwayat Belanja (Purchases) sesuai Figma History
        $samplePurchases = [
            [
                'kode_pembelian'    => '#PR-88289',
                'vendor_name'       => 'Mitra Otomotif Sejahtera',
                'tanggal_pembelian' => now()->subDays(1)->toDateString(),
                'waktu_pembelian'   => '14:25',
                'total_harga'       => 8750000,
                'catatan'           => 'Pembelian kampas rem heavy duty armada distribusi.',
                'item_name'         => 'Brake Pad Heavy Duty X-200',
                'item_sku'          => 'BP-HDX200-S1',
                'item_qty'          => 25,
            ],
            [
                'kode_pembelian'    => '#PR-88290',
                'vendor_name'       => 'Sinar Jaya Diesel',
                'tanggal_pembelian' => now()->subDays(2)->toDateString(),
                'waktu_pembelian'   => '09:12',
                'total_harga'       => 12450000,
                'catatan'           => 'Pengadaan unit turbocharger dan kit transmisi.',
                'item_name'         => 'Turbocharger Assembly V8',
                'item_sku'          => 'TB-V8-ASM',
                'item_qty'          => 2,
            ],
            [
                'kode_pembelian'    => '#PR-88291',
                'vendor_name'       => 'Global Truck Parts',
                'tanggal_pembelian' => now()->subDays(3)->toDateString(),
                'waktu_pembelian'   => '16:45',
                'total_harga'       => 3800000,
                'catatan'           => 'Pembelian tabung reservoir coolant radiator.',
                'item_name'         => 'Radiator Coolant Reservoir',
                'item_sku'          => 'RAD-RES-99',
                'item_qty'          => 10,
            ],
            [
                'kode_pembelian'    => '#PR-88292',
                'vendor_name'       => 'Prima Spareparts',
                'tanggal_pembelian' => now()->subDays(4)->toDateString(),
                'waktu_pembelian'   => '11:30',
                'total_harga'       => 4200000,
                'catatan'           => 'Pengadaan unit lampu depan LED 24V.',
                'item_name'         => 'LED Headlight Unit Left',
                'item_sku'          => 'LED-HL-L-01',
                'item_qty'          => 12,
            ],
        ];

        $approvedRequests = SparepartRequest::where('status', SparepartRequest::STATUS_APPROVED)->orderBy('id')->get()->values();

        foreach ($samplePurchases as $idx => $pData) {
            $vendor     = Vendor::where('nama_vendor', $pData['vendor_name'])->first();
            $sp         = Sparepart::first();
            $matchedReq = $approvedRequests->get($idx);

            $purchase = Purchase::updateOrCreate(
                ['kode_pembelian' => $pData['kode_pembelian']],
                [
                    'user_id'              => $pengadaan?->id,
                    'vendor_id'            => $vendor?->id,
                    'vendor_name'          => $pData['vendor_name'],
                    'sparepart_request_id' => $matchedReq?->id,
                    'tanggal_pembelian'    => $pData['tanggal_pembelian'],
                    'waktu_pembelian'      => $pData['waktu_pembelian'],
                    'total_harga'          => $pData['total_harga'],
                    'catatan'              => $pData['catatan'],
                    'status_pembelian'     => 'selesai',
                ]
            );

            if ($purchase->items()->count() === 0) {
                PurchaseItem::create([
                    'purchase_id'    => $purchase->id,
                    'sparepart_id'   => $sp?->id,
                    'nama_sparepart' => $pData['item_name'],
                    'kode_barang'    => $pData['item_sku'],
                    'qty'            => $pData['item_qty'],
                    'harga_satuan'   => $pData['item_qty'] > 0 ? round($pData['total_harga'] / $pData['item_qty'], 2) : 0,
                ]);
            }
        }
    }
}

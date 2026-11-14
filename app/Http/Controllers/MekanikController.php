<?php

namespace App\Http\Controllers;

use App\Models\Checklist;
use App\Models\Maintenance;
use App\Models\MaintenanceDetail;
use App\Models\Sparepart;
use App\Models\SparepartRequest;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Controller Modul Operasional Mekanik & Bengkel (Web Desktop Terintegrasi).
 * Seluruh halaman menggunakan layout utama (layouts.app) & sidebar utama,
 * serta 100% tersinkronisasi dengan database.
 */
class MekanikController extends Controller
{
    /**
     * Hitung statistik Tren Perbaikan Teratas langsung dari data aktual tabel `maintenances`.
     */
    public function calculateRepairTrendsFromDb(): array
    {
        $allMaintenances = Maintenance::all();
        $total = $allMaintenances->count();

        if ($total === 0) {
            return [
                'items' => [
                    ['label' => 'Kelistrikan', 'count' => 0, 'percent' => 0, 'color' => '#B91C1C'],
                    ['label' => 'Kaki-kaki',   'count' => 0, 'percent' => 0, 'color' => '#0F2942'],
                    ['label' => 'Ban',         'count' => 0, 'percent' => 0, 'color' => '#5C2D0C'],
                ],
                'top_category' => 'belum ada kendala tercatat',
                'month_label'  => now()->translatedFormat('F Y'),
                'total_logs'   => 0,
            ];
        }

        $buckets = [
            'Kelistrikan'    => ['count' => 0, 'color' => '#B91C1C'],
            'Kaki-kaki'      => ['count' => 0, 'color' => '#0F2942'],
            'Ban'            => ['count' => 0, 'color' => '#5C2D0C'],
            'Mesin'          => ['count' => 0, 'color' => '#1D4ED8'],
            'Service Ringan' => ['count' => 0, 'color' => '#475569'],
        ];

        foreach ($allMaintenances as $m) {
            $kat  = strtolower($m->kategori_kendala ?? '');
            $desc = strtolower(($m->deskripsi_masalah ?? '') . ' ' . ($m->solusi ?? ''));

            if (str_contains($kat, 'listrik') || str_contains($desc, 'lampu') || str_contains($desc, 'klakson') || str_contains($desc, 'aki') || str_contains($desc, 'alternator') || str_contains($desc, 'start')) {
                $buckets['Kelistrikan']['count']++;
            } elseif (str_contains($kat, 'kaki') || str_contains($kat, 'rem') || str_contains($desc, 'rem') || str_contains($desc, 'bearing') || str_contains($desc, 'kopling')) {
                $buckets['Kaki-kaki']['count']++;
            } elseif (str_contains($kat, 'ban') || str_contains($desc, 'ban')) {
                $buckets['Ban']['count']++;
            } elseif (str_contains($kat, 'mesin') || str_contains($desc, 'oli') || str_contains($desc, 'filter') || str_contains($desc, 'radiator') || str_contains($desc, 'turbo')) {
                $buckets['Mesin']['count']++;
            } else {
                $buckets['Service Ringan']['count']++;
            }
        }

        $items = [];
        foreach ($buckets as $label => $meta) {
            if ($meta['count'] > 0) {
                $items[] = [
                    'label'   => $label,
                    'count'   => $meta['count'],
                    'percent' => (int) round(($meta['count'] / $total) * 100),
                    'color'   => $meta['color'],
                ];
            }
        }

        usort($items, fn ($a, $b) => $b['count'] <=> $a['count']);
        $topItems = array_slice($items, 0, 4);

        return [
            'items'        => $topItems,
            'top_category' => strtolower($topItems[0]['label'] ?? 'kelistrikan'),
            'month_label'  => now()->translatedFormat('F Y'),
            'total_logs'   => $total,
        ];
    }

    /**
     * Redirect /mekanik/dashboard ke Dashboard Utama (/dashboard)
     */
    public function dashboard(Request $request)
    {
        return redirect()->route('dashboard', $request->query());
    }

    /**
     * 2. INVENTORY MEKANIK (Daftar Sparepart Terkini & Kategori A/B/C)
     */
    public function inventory(Request $request)
    {
        $kat    = $request->query('kat'); // null | A | B | C
        $search = $request->query('q');

        $spareparts = Sparepart::where('tipe_item', 'sparepart')
            ->when($kat && in_array($kat, ['A', 'B', 'C']), fn ($q) => $q->where('kategori_abc', $kat))
            ->when($search, function ($q, $s) {
                $q->where(function ($sub) use ($s) {
                    $sub->where('nama_sparepart', 'like', "%{$s}%")
                        ->orWhere('kode_sparepart', 'like', "%{$s}%")
                        ->orWhere('kategori', 'like', "%{$s}%");
                });
            })
            ->orderByRaw('CASE WHEN stok <= minimal_stok THEN 0 ELSE 1 END')
            ->orderBy('kategori_abc')
            ->orderBy('nama_sparepart')
            ->get();

        $countA = Sparepart::where('tipe_item', 'sparepart')->where('kategori_abc', 'A')->count();
        $countB = Sparepart::where('tipe_item', 'sparepart')->where('kategori_abc', 'B')->count();
        $countC = Sparepart::where('tipe_item', 'sparepart')->where('kategori_abc', 'C')->count();

        $criticalCount = Sparepart::where('tipe_item', 'sparepart')
            ->whereColumn('stok', '<=', 'minimal_stok')
            ->count();

        $trends = $this->calculateRepairTrendsFromDb();

        return view('mekanik.inventory', compact(
            'spareparts',
            'countA',
            'countB',
            'countC',
            'criticalCount',
            'trends',
            'kat',
            'search'
        ));
    }

    /**
     * 2b. FORM REQUEST STOCK
     */
    public function requestStockForm(Request $request)
    {
        $selectedPartId = $request->query('sparepart_id');
        $spareparts     = Sparepart::orderBy('nama_sparepart')->get();
        $vendors        = Vendor::where('is_active', true)->orderBy('nama_vendor')->get();
        $maintenances   = Maintenance::with('vehicle')
            ->where('status_maintenance', '!=', Maintenance::STATUS_COMPLETED)
            ->latest('tanggal_maintenance')
            ->get();

        return view('mekanik.request-stock', compact(
            'spareparts',
            'selectedPartId',
            'vendors',
            'maintenances'
        ));
    }

    /**
     * SIMPAN REQUEST STOCK / PURCHASE REQUEST (Tersinkronisasi dengan SparepartRequest Admin)
     */
    public function storeRequestStock(Request $request)
    {
        $validated = $request->validate([
            'sparepart_id'           => 'nullable|exists:spareparts,id',
            'nama_sparepart'         => 'required_without:sparepart_id|nullable|string|max:150',
            'qty'                    => 'required|integer|min:1',
            'priority_level'         => 'nullable|string|in:critical,normal,support',
            'expected_delivery_date' => 'nullable|date',
            'vendor_name'            => 'nullable|string|max:120',
            'maintenance_id'         => 'nullable|exists:maintenances,id',
            'alasan'                 => 'nullable|string',
        ]);

        if (! empty($validated['sparepart_id'])) {
            $sp = Sparepart::find($validated['sparepart_id']);
            if ($sp && empty($validated['nama_sparepart'])) {
                $validated['nama_sparepart'] = $sp->nama_sparepart;
            }
        }

        $vendorName = $validated['vendor_name']
            ?: (Vendor::where('is_active', true)->orderBy('id')->value('nama_vendor') ?? 'PT. Nusantara Perkasa');

        SparepartRequest::create([
            'sparepart_id'           => $validated['sparepart_id'] ?? null,
            'nama_sparepart'         => $validated['nama_sparepart'],
            'qty'                    => (int) $validated['qty'],
            'priority_level'         => $validated['priority_level'] ?? 'normal',
            'expected_delivery_date' => $validated['expected_delivery_date'] ?? now()->addDays(3)->toDateString(),
            'vendor_name'            => $vendorName,
            'maintenance_id'         => $validated['maintenance_id'] ?? null,
            'requested_by'           => auth()->id(),
            'alasan'                 => $validated['alasan'] ?? 'Pengajuan pemenuhan stok suku cadang bengkel armada.',
            'status'                 => SparepartRequest::STATUS_PENDING,
        ]);

        return redirect()
            ->route('mekanik.approvals')
            ->with('success', 'Request Stock berhasil diajukan dan langsung tersinkronisasi ke daftar Permintaan Admin.');
    }

    /**
     * 3. REPAIR LOG (Rekap Log Perbaikan)
     */
    public function repairs(Request $request)
    {
        $search = $request->query('q');
        $month  = $request->query('month'); // format YYYY-MM

        $maintenances = Maintenance::with(['vehicle', 'mekanik', 'details.sparepart'])
            ->when($search, function ($q, $s) {
                $q->where(function ($sub) use ($s) {
                    $sub->where('deskripsi_masalah', 'like', "%{$s}%")
                        ->orWhere('solusi', 'like', "%{$s}%")
                        ->orWhere('nama_driver', 'like', "%{$s}%")
                        ->orWhere('kategori_kendala', 'like', "%{$s}%")
                        ->orWhereHas('vehicle', fn ($vq) => $vq->where('plat_nomor', 'like', "%{$s}%"));
                });
            })
            ->when($month, function ($q, $m) {
                if (preg_match('/^(\d{4})-(\d{2})$/', $m, $matches)) {
                    $q->whereYear('tanggal_maintenance', $matches[1])
                      ->whereMonth('tanggal_maintenance', $matches[2]);
                }
            })
            ->latest('tanggal_maintenance')
            ->latest('id')
            ->paginate(12)
            ->withQueryString();

        // 3 KPI Cards tersinkronisasi penuh dengan database
        $monthMatches  = [];
        $hasValidMonth = $month && preg_match('/^(\d{4})-(\d{2})$/', $month, $monthMatches);

        $totalBulanIni = Maintenance::when($hasValidMonth, function ($q) use ($monthMatches) {
                $q->whereYear('tanggal_maintenance', $monthMatches[1])
                  ->whereMonth('tanggal_maintenance', $monthMatches[2]);
            }, function ($q) {
                $q->whereYear('tanggal_maintenance', now()->year)
                  ->whereMonth('tanggal_maintenance', now()->month);
            })
            ->count();

        $unitDalamPerbaikan = Vehicle::whereIn('status', [
            Vehicle::STATUS_MAINTENANCE,
            Vehicle::STATUS_RUSAK,
        ])->count();

        $totalArmada  = Vehicle::where('status', '!=', Vehicle::STATUS_NONAKTIF)->count();
        $unitBerjalan = Vehicle::where('status', Vehicle::STATUS_SIAP_JALAN)->count();

        $vehicles   = Vehicle::where('status', '!=', Vehicle::STATUS_NONAKTIF)->orderBy('plat_nomor')->get();
        $spareparts = Sparepart::where('tipe_item', 'sparepart')->orderBy('nama_sparepart')->get();

        // Daftar bulan unik dari database maintenances
        $availableMonths = Maintenance::selectRaw("DATE_FORMAT(tanggal_maintenance, '%Y-%m') as ym")
            ->distinct()
            ->orderByDesc('ym')
            ->pluck('ym');

        return view('mekanik.repairs', compact(
            'maintenances',
            'totalBulanIni',
            'unitDalamPerbaikan',
            'unitBerjalan',
            'totalArmada',
            'vehicles',
            'spareparts',
            'availableMonths',
            'search',
            'month'
        ));
    }

    /**
     * EXPORT PDF REKAP LOG PERBAIKAN MEKANIK
     */
    public function exportPdfRepairs(Request $request)
    {
        $search = $request->query('q');
        $month  = $request->query('month'); // format YYYY-MM
        $status = $request->query('status');

        $maintenances = Maintenance::with(['vehicle', 'mekanik', 'details.sparepart'])
            ->when($search, function ($q, $s) {
                $q->where(function ($sub) use ($s) {
                    $sub->where('deskripsi_masalah', 'like', "%{$s}%")
                        ->orWhere('solusi', 'like', "%{$s}%")
                        ->orWhere('nama_driver', 'like', "%{$s}%")
                        ->orWhere('kategori_kendala', 'like', "%{$s}%")
                        ->orWhereHas('vehicle', fn ($vq) => $vq->where('plat_nomor', 'like', "%{$s}%"));
                });
            })
            ->when($month, function ($q, $m) {
                if (preg_match('/^(\d{4})-(\d{2})$/', $m, $matches)) {
                    $q->whereYear('tanggal_maintenance', $matches[1])
                      ->whereMonth('tanggal_maintenance', $matches[2]);
                }
            })
            ->when($status, fn ($q) => $q->where('status_maintenance', $status))
            ->latest('tanggal_maintenance')
            ->latest('id')
            ->get();

        $stats = [
            'total'       => $maintenances->count(),
            'pending'     => $maintenances->where('status_maintenance', Maintenance::STATUS_PENDING)->count(),
            'in_progress' => $maintenances->where('status_maintenance', Maintenance::STATUS_IN_PROGRESS)->count(),
            'completed'   => $maintenances->where('status_maintenance', Maintenance::STATUS_COMPLETED)->count(),
        ];

        return view('maintenances.pdf', compact('maintenances', 'stats', 'status', 'search'));
    }

    /**
     * 3b. FORM TAMBAH LOG PERBAIKAN + SELECT SPARE PART
     */
    public function createRepair(Request $request)
    {
        $vehicles   = Vehicle::with('assignedDriver')->where('status', '!=', Vehicle::STATUS_NONAKTIF)->orderBy('plat_nomor')->get();
        $drivers    = User::role('driver')->orderBy('name')->get();
        $spareparts = Sparepart::where('tipe_item', 'sparepart')->orderBy('kategori_abc')->orderBy('nama_sparepart')->get();

        // Temuan checklist bermasalah dari database yang bisa langsung ditindaklanjuti mekanik
        $openChecklists = Checklist::with(['vehicle', 'driver', 'items' => fn ($q) => $q->where('kondisi', false)])
            ->where('status', 'kendala')
            ->latest('tanggal')
            ->take(10)
            ->get();

        $nextId = (Maintenance::max('id') ?? 0) + 1;
        $nextLogNumber = 'LOG-REP-' . now()->format('Y') . '-' . str_pad($nextId, 3, '0', STR_PAD_LEFT);

        return view('mekanik.repairs-create', compact(
            'vehicles',
            'drivers',
            'spareparts',
            'openChecklists',
            'nextLogNumber'
        ));
    }

    /**
     * SIMPAN LOG PERBAIKAN BARU DARI MEKANIK
     */
    public function storeRepair(Request $request)
    {
        $validated = $request->validate([
            'tanggal_maintenance' => 'required|date',
            'vehicle_id'          => 'required|exists:vehicles,id',
            'checklist_id'        => 'nullable|exists:checklists,id',
            'nama_driver'         => 'nullable|string|max:100',
            'kategori_kendala'    => 'nullable|string|max:60',
            'estimasi_downtime'   => 'nullable|string|max:40',
            'status_maintenance'  => 'nullable|in:pending,in_progress,completed',
            'deskripsi_masalah'   => 'required|string',
            'solusi'              => 'nullable|string',
            'spareparts'          => 'nullable|array',
            'spareparts.*.id'     => 'required_with:spareparts|exists:spareparts,id',
            'spareparts.*.qty'    => 'required_with:spareparts|integer|min:1',
        ]);

        DB::transaction(function () use ($validated, $request) {
            $status  = $validated['status_maintenance'] ?? Maintenance::STATUS_COMPLETED;
            $vehicle = Vehicle::with('assignedDriver')->find($validated['vehicle_id']);

            $driverName = $validated['nama_driver']
                ?: ($vehicle?->assignedDriver?->name ?? auth()->user()->name);

            $maintenance = Maintenance::create([
                'vehicle_id'          => $validated['vehicle_id'],
                'mekanik_id'          => auth()->id(),
                'checklist_id'        => $validated['checklist_id'] ?? null,
                'nama_driver'         => $driverName,
                'tanggal_maintenance' => $validated['tanggal_maintenance'],
                'status_maintenance'  => $status,
                'kategori_kendala'    => $validated['kategori_kendala'] ?? 'Service Ringan',
                'estimasi_downtime'   => $validated['estimasi_downtime'] ?: '2 Jam',
                'deskripsi_masalah'   => $validated['deskripsi_masalah'],
                'solusi'              => $validated['solusi'] ?? null,
            ]);

            if (! empty($request->spareparts)) {
                foreach ($request->spareparts as $item) {
                    if (empty($item['id']) || empty($item['qty'])) {
                        continue;
                    }
                    $sp = Sparepart::find($item['id']);
                    if (! $sp) {
                        continue;
                    }

                    $qty = (int) $item['qty'];

                    MaintenanceDetail::create([
                        'maintenance_id' => $maintenance->id,
                        'sparepart_id'   => $sp->id,
                        'nama_sparepart' => $sp->nama_sparepart,
                        'qty'            => $qty,
                    ]);

                    if ($status !== Maintenance::STATUS_PENDING) {
                        if ($sp->stok >= $qty) {
                            $sp->decrement('stok', $qty);
                        } else {
                            $sp->update(['stok' => 0]);
                        }
                    }
                }
            }

            // Sinkronisasi status armada di database
            if ($vehicle) {
                if ($status === Maintenance::STATUS_COMPLETED) {
                    $vehicle->update(['status' => Vehicle::STATUS_SIAP_JALAN]);
                } elseif ($status === Maintenance::STATUS_IN_PROGRESS) {
                    $vehicle->update(['status' => Vehicle::STATUS_MAINTENANCE]);
                } else {
                    $vehicle->update(['status' => Vehicle::STATUS_RUSAK]);
                }
            }
        });

        return redirect()
            ->route('mekanik.repairs')
            ->with('success', 'Log perbaikan armada berhasil disimpan dan stok database otomatis tersinkronisasi.');
    }

    /**
     * UPDATE LOG PERBAIKAN
     */
    public function updateRepair(Request $request, Maintenance $maintenance)
    {
        $validated = $request->validate([
            'tanggal_maintenance' => 'required|date',
            'vehicle_id'          => 'nullable|exists:vehicles,id',
            'nama_driver'         => 'nullable|string|max:100',
            'kategori_kendala'    => 'nullable|string|max:60',
            'part_name'           => 'nullable|string|max:150',
            'deskripsi_masalah'   => 'required|string',
            'solusi'              => 'nullable|string',
            'status_maintenance'  => 'nullable|in:pending,in_progress,completed',
        ]);

        DB::transaction(function () use ($maintenance, $validated) {
            $oldStatus = $maintenance->status_maintenance;
            $newStatus = $validated['status_maintenance'] ?? $maintenance->status_maintenance;

            $maintenance->update([
                'tanggal_maintenance' => $validated['tanggal_maintenance'],
                'vehicle_id'          => $validated['vehicle_id'] ?? $maintenance->vehicle_id,
                'nama_driver'         => $validated['nama_driver'] ?? $maintenance->nama_driver,
                'kategori_kendala'    => $validated['kategori_kendala'] ?? $maintenance->kategori_kendala,
                'deskripsi_masalah'   => $validated['deskripsi_masalah'],
                'solusi'              => $validated['solusi'],
                'status_maintenance'  => $newStatus,
            ]);

            // Jika sebelumnya pending dan sekarang dikerjakan/selesai, potong stok sparepart
            if ($oldStatus === Maintenance::STATUS_PENDING && $newStatus !== Maintenance::STATUS_PENDING) {
                foreach ($maintenance->details as $detail) {
                    if ($detail->sparepart && $detail->sparepart->stok >= $detail->qty) {
                        $detail->sparepart->decrement('stok', $detail->qty);
                    }
                }
            }

            if (! empty($validated['part_name'])) {
                $firstDetail = $maintenance->details()->first();
                if ($firstDetail) {
                    $firstDetail->update(['nama_sparepart' => $validated['part_name']]);
                } else {
                    $sp = Sparepart::where('nama_sparepart', 'like', '%' . $validated['part_name'] . '%')->first()
                        ?? Sparepart::first();
                    if ($sp) {
                        MaintenanceDetail::create([
                            'maintenance_id' => $maintenance->id,
                            'sparepart_id'   => $sp->id,
                            'nama_sparepart' => $validated['part_name'],
                            'qty'            => 1,
                        ]);
                    }
                }
            }

            $vehicle = Vehicle::find($maintenance->vehicle_id);
            if ($vehicle) {
                if ($newStatus === Maintenance::STATUS_COMPLETED) {
                    $vehicle->update(['status' => Vehicle::STATUS_SIAP_JALAN]);
                } elseif ($newStatus === Maintenance::STATUS_IN_PROGRESS) {
                    $vehicle->update(['status' => Vehicle::STATUS_MAINTENANCE]);
                } else {
                    $vehicle->update(['status' => Vehicle::STATUS_RUSAK]);
                }
            }
        });

        return back()->with('success', 'Data log perbaikan dan status armada berhasil diperbarui.');
    }

    /**
     * HAPUS LOG PERBAIKAN
     */
    public function destroyRepair(Maintenance $maintenance)
    {
        $maintenance->details()->delete();
        $maintenance->delete();

        return back()->with('success', 'Log perbaikan berhasil dihapus dari database.');
    }

    /**
     * 4. MASTER DATA: INVENTARIS & TOOLKIT
     */
    public function masterData(Request $request)
    {
        $tab    = $request->query('tab', 'all'); // all | toolkit | support
        $search = $request->query('q');

        $items = Sparepart::query()
            ->when($tab === 'toolkit', fn ($q) => $q->where('tipe_item', 'toolkit'))
            ->when($tab === 'support', fn ($q) => $q->where('tipe_item', 'support'))
            ->when($search, function ($q, $s) {
                $q->where(function ($sub) use ($s) {
                    $sub->where('nama_sparepart', 'like', "%{$s}%")
                        ->orWhere('kode_sparepart', 'like', "%{$s}%")
                        ->orWhere('keterangan', 'like', "%{$s}%")
                        ->orWhere('kategori', 'like', "%{$s}%");
                });
            })
            ->orderBy('kode_sparepart')
            ->get();

        $totalCount = Sparepart::count();
        $vendors    = Vendor::where('is_active', true)->orderBy('nama_vendor')->get();

        return view('mekanik.master-data', compact('items', 'tab', 'search', 'totalCount', 'vendors'));
    }

    /**
     * TAMBAH ITEM MASTER DATA BARU (+ Log baru)
     */
    public function storeMasterItem(Request $request)
    {
        $validated = $request->validate([
            'kode_sparepart' => 'required|string|max:50|unique:spareparts,kode_sparepart',
            'nama_sparepart' => 'required|string|max:150',
            'tipe_item'      => 'required|in:sparepart,toolkit,support',
            'kategori_abc'   => 'nullable|in:A,B,C',
            'satuan'         => 'required|string|max:30',
            'stok'           => 'required|integer|min:0',
            'minimal_stok'   => 'nullable|integer|min:0',
            'frekuensi'      => 'nullable|string|max:30',
            'lokasi'         => 'nullable|string|max:100',
            'keterangan'     => 'nullable|string',
        ]);

        $kategoriLabel = match ($validated['tipe_item']) {
            'toolkit' => 'Tool Kit',
            'support' => 'Support',
            default   => 'Spare Part',
        };

        Sparepart::create([
            'kode_sparepart' => strtoupper($validated['kode_sparepart']),
            'nama_sparepart' => $validated['nama_sparepart'],
            'kategori'       => $kategoriLabel,
            'kategori_abc'   => $validated['kategori_abc'] ?? 'B',
            'tipe_item'      => $validated['tipe_item'],
            'frekuensi'      => $validated['frekuensi'] ?? 'Rutin',
            'stok'           => (int) $validated['stok'],
            'minimal_stok'   => (int) ($validated['minimal_stok'] ?? 3),
            'satuan'         => $validated['satuan'],
            'harga'          => 0,
            'keterangan'     => $validated['keterangan'],
            'lokasi'         => $validated['lokasi'] ?: ($validated['tipe_item'] === 'toolkit' ? 'Workshop Utama' : 'Warehouse Alpha'),
        ]);

        return back()->with('success', 'Item berhasil ditambahkan ke Master Data.');
    }

    /**
     * UPDATE ITEM MASTER DATA / INVENTORY
     */
    public function updateMasterItem(Request $request, Sparepart $sparepart)
    {
        $validated = $request->validate([
            'kode_sparepart' => 'required|string|max:50|unique:spareparts,kode_sparepart,' . $sparepart->id,
            'nama_sparepart' => 'required|string|max:150',
            'tipe_item'      => 'required|in:sparepart,toolkit,support',
            'kategori_abc'   => 'nullable|in:A,B,C',
            'satuan'         => 'required|string|max:30',
            'stok'           => 'required|integer|min:0',
            'minimal_stok'   => 'nullable|integer|min:0',
            'frekuensi'      => 'nullable|string|max:30',
            'lokasi'         => 'nullable|string|max:100',
            'keterangan'     => 'nullable|string',
        ]);

        $kategoriLabel = match ($validated['tipe_item']) {
            'toolkit' => 'Tool Kit',
            'support' => 'Support',
            default   => 'Spare Part',
        };

        $sparepart->update([
            'kode_sparepart' => strtoupper($validated['kode_sparepart']),
            'nama_sparepart' => $validated['nama_sparepart'],
            'kategori'       => $kategoriLabel,
            'kategori_abc'   => $validated['kategori_abc'] ?? $sparepart->kategori_abc,
            'tipe_item'      => $validated['tipe_item'],
            'frekuensi'      => $validated['frekuensi'] ?? $sparepart->frekuensi,
            'stok'           => (int) $validated['stok'],
            'minimal_stok'   => (int) ($validated['minimal_stok'] ?? $sparepart->minimal_stok),
            'satuan'         => $validated['satuan'],
            'lokasi'         => $validated['lokasi'] ?? $sparepart->lokasi,
            'keterangan'     => $validated['keterangan'],
        ]);

        return back()->with('success', 'Data item berhasil diperbarui di database.');
    }

    /**
     * HAPUS ITEM MASTER DATA / INVENTORY
     */
    public function destroyMasterItem(Sparepart $sparepart)
    {
        $sparepart->delete();

        return back()->with('success', 'Item berhasil dihapus dari katalog database.');
    }

    /**
     * 5. PERSETUJUAN / HISTORY PEMBELANJAAN
     */
    public function approvals(Request $request)
    {
        $search = $request->query('q');
        $status = $request->query('status');

        $requests = SparepartRequest::with(['sparepart', 'requester', 'reviewer', 'maintenance.vehicle'])
            ->when($search, function ($q, $s) {
                $q->where(function ($sub) use ($s) {
                    $sub->where('nama_sparepart', 'like', "%{$s}%")
                        ->orWhere('vendor_name', 'like', "%{$s}%")
                        ->orWhere('alasan', 'like', "%{$s}%");
                });
            })
            ->when($status && in_array($status, ['pending', 'approved', 'rejected']), fn ($q) => $q->where('status', $status))
            ->latest('created_at')
            ->latest('id')
            ->get();

        $totalInDb = SparepartRequest::count();

        return view('mekanik.approvals', compact('requests', 'totalInDb', 'search', 'status'));
    }

    /**
     * DOWNLOAD CSV HISTORY PEMBELANJAAN
     */
    public function exportApprovals()
    {
        $requests = SparepartRequest::with('sparepart')->latest()->get();

        $filename = 'history-pembelanjaan-suku-cadang-' . now()->format('Y-m-d') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($requests) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF)); // UTF-8 BOM
            fputcsv($out, ['ID PERMINTAAN', 'NAMA SUKU CADANG', 'QTY', 'PRIORITAS', 'KATEGORI', 'NAMA VENDOR', 'TANGGAL', 'STATUS']);

            foreach ($requests as $req) {
                $reqCode  = '#REQ-' . $req->created_at->format('Y') . '-' . str_pad($req->id, 3, '0', STR_PAD_LEFT);
                $kategori = $req->sparepart->kategori ?? 'Suku Cadang';
                $vendor   = $req->vendor_name ?: 'PT. Nusantara Perkasa';
                $statusLabel = match ($req->status) {
                    'approved' => 'Disetujui',
                    'rejected' => 'Ditolak',
                    default    => 'Pending',
                };

                fputcsv($out, [
                    $reqCode,
                    $req->nama_sparepart,
                    $req->qty,
                    strtoupper($req->priority_level ?? 'NORMAL'),
                    $kategori,
                    $vendor,
                    $req->created_at->format('d M Y'),
                    $statusLabel,
                ]);
            }

            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }
}

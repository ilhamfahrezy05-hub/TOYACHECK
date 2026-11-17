<?php

namespace App\Http\Controllers;

use App\Models\Checklist;
use App\Models\Maintenance;
use App\Models\Purchase;
use App\Models\Sparepart;
use App\Models\SparepartRequest;
use App\Models\Vehicle;
use App\Models\Vendor;
use Illuminate\Http\Request;

/**
 * Controller Dashboard Utama Web Desktop Terpadu (Admin Distribusi & Mekanik).
 * Seluruh data terhubung dan tersinkronisasi penuh dengan database.
 */
class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Role Driver & HSE selalu menggunakan UI Mobile khusus (/driver) sesuai alur aplikasi
        if (auth()->check() && (auth()->user()->hasRole('driver') || auth()->user()->hasRole('hse'))) {
            return redirect()->route('driver.index');
        }

        // 1. Sapaan dinamis berdasarkan jam lokal
        $hour = (int) now()->format('H');
        if ($hour >= 5 && $hour < 11) {
            $greetingTime = 'Semangat Pagi!';
        } elseif ($hour >= 11 && $hour < 15) {
            $greetingTime = 'Semangat Siang!';
        } elseif ($hour >= 15 && $hour < 18) {
            $greetingTime = 'Semangat Sore!';
        } else {
            $greetingTime = 'Semangat Malam!';
        }

        // 2. Empat Kartu Statistik Utama (Total Checklist, Armada Sehat, Tidak Layak Jalan, Sedang Diperbaiki)
        $stats = [
            'total_checklist'   => Checklist::count(),
            'armada_sehat'      => Vehicle::where('status', Vehicle::STATUS_SIAP_JALAN)->count(),
            'tidak_layak_jalan' => Vehicle::where('status', Vehicle::STATUS_RUSAK)->count(),
            'sedang_diperbaiki' => Vehicle::where('status', Vehicle::STATUS_MAINTENANCE)->count(),
        ];

        // 3. Widget Kendala Checklist Fisik (Daftar armada dengan temuan fisik)
        $kendalaFisik = Checklist::with(['vehicle', 'items' => fn ($q) => $q->where('kondisi', false)])
            ->where('checklist_type', 'fisik')
            ->where('status', 'kendala')
            ->latest('tanggal')
            ->take(5)
            ->get();

        // 4. Widget Kendala Checklist K3 (Daftar temuan K3 & kebersihan)
        $kendalaK3 = Checklist::with(['vehicle', 'items' => fn ($q) => $q->where('kondisi', false)])
            ->where('checklist_type', 'k3')
            ->where('status', 'kendala')
            ->latest('tanggal')
            ->take(5)
            ->get();

        // 5. Tabel Daftar Inspeksi Terbaru dengan Filter Tanggal
        $tanggalFilter = $request->query('tanggal');

        $recentInspections = Checklist::with(['vehicle', 'driver', 'items'])
            ->when($tanggalFilter, fn ($q) => $q->whereDate('tanggal', $tanggalFilter))
            ->latest('tanggal')
            ->latest('id')
            ->take(10)
            ->get();

        // 6. Data Persediaan Sparepart (ABC) & Stok Kritis dari Database
        $kat = $request->query('kat'); // null | A | B | C

        $persediaan = Sparepart::where('tipe_item', 'sparepart')
            ->when($kat && in_array($kat, ['A', 'B', 'C']), fn ($q) => $q->where('kategori_abc', $kat))
            ->orderByRaw('CASE WHEN stok <= minimal_stok THEN 0 ELSE 1 END')
            ->orderBy('nama_sparepart')
            ->take(6)
            ->get();

        $criticalCount = Sparepart::where('tipe_item', 'sparepart')
            ->whereColumn('stok', '<=', 'minimal_stok')
            ->count();

        // 7. Rekap Perbaikan Armada Terbaru dari Database
        $rekapArmada = Maintenance::with(['vehicle', 'mekanik', 'details'])
            ->latest('tanggal_maintenance')
            ->latest('id')
            ->take(6)
            ->get();

        // 8. Tren Perbaikan Teratas (Dihitung langsung dari agregasi tabel maintenances di database)
        $trends = app(MekanikController::class)->calculateRepairTrendsFromDb();

        // 9. Data Khusus Dashboard Pengadaan (Figma 1405:10 & 1265:2856)
        $kritisRequestsCount = SparepartRequest::whereIn('priority_level', ['critical', 'Urgent', 'Tinggi'])->count();
        $totalRequestsCount  = SparepartRequest::count();
        $normalRequestsCount = max(0, $totalRequestsCount - $kritisRequestsCount);
        $pendingRequestsCount = SparepartRequest::where('status', SparepartRequest::STATUS_PENDING)->count();

        $procStats = [
            'total_permintaan'      => $totalRequestsCount,
            'kritis'                => $kritisRequestsCount,
            'normal'                => $normalRequestsCount,
            'pending'               => $pendingRequestsCount,
            'stok_kritis'           => $criticalCount,
            'vendor_aktif'          => Vendor::where('is_active', true)->count(),
            'vendor_baru_bulan_ini' => Vendor::whereYear('created_at', now()->year)->whereMonth('created_at', now()->month)->count() ?: 3,
        ];

        $procNotifRequests = SparepartRequest::with(['sparepart', 'requester', 'maintenance.vehicle'])
            ->orderByRaw("CASE WHEN status = 'pending' THEN 0 ELSE 1 END")
            ->latest('created_at')
            ->take(3)
            ->get();

        $procRecentRequests = SparepartRequest::with('sparepart')
            ->latest('created_at')
            ->take(5)
            ->get();

        $procRecentPurchases = Purchase::with(['items', 'vendor'])
            ->latest('tanggal_pembelian')
            ->latest('id')
            ->take(4)
            ->get();

        $procVendors = Vendor::where('is_active', true)->take(3)->get();

        return view('dashboard', compact(
            'greetingTime',
            'stats',
            'kendalaFisik',
            'kendalaK3',
            'recentInspections',
            'tanggalFilter',
            'persediaan',
            'criticalCount',
            'rekapArmada',
            'trends',
            'kat',
            'procStats',
            'procNotifRequests',
            'procRecentRequests',
            'procRecentPurchases',
            'procVendors'
        ));
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Checklist;
use App\Models\ChecklistItem;
use App\Models\InspectionItem;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Controller Aplikasi Mobile Driver & HSE (Figma Mobile Match).
 * Menangani alur:
 * 1. Scan QR Code di truk -> masuk ke aplikasi mobile (/m/qr/{code})
 * 2. Login mobile (ID Karyawan / Username + Password)
 * 3. Pilih Kendaraan (/driver) -> Daftar armada aktif
 * 4. Detail Kendaraan (/driver/vehicles/{vehicle}) -> Driver: CEK FISIK | HSE: CEK K3 + Export PDF
 * 5. Form Checklist Mobile (/driver/vehicles/{vehicle}/checklist/{type})
 * 6. Riwayat Checklist (/driver/history) + Edit/Hapus Laporan K3 oleh HSE
 * 7. Edit Data K3 (/driver/k3-items) -> Khusus HSE (Tambah, Edit, Hapus Item Pemeriksaan K3)
 * 8. Informasi Akun & Notifikasi (/driver/profile, /driver/notifications)
 */
class DriverAppController extends Controller
{
    /**
     * Pastikan role Admin tidak dapat mengakses mode operasional Driver/HSE
     */
    protected function ensureNotAdmin()
    {
        if (Auth::check() && Auth::user()->hasRole('admin')) {
            return redirect()->route('dashboard')
                ->with('error', 'Akun Admin hanya bertugas memonitoring sistem dan tidak dapat mengakses mode operasional Mobile.');
        }

        return null;
    }

    /**
     * ENTRY POINT UNIVERSAL DARI SCAN QR CODE
     */
    public function qrEntry(Request $request, ?string $code = null)
    {
        $request->session()->forget('qr_vehicle_id');

        if (!Auth::check()) {
            return redirect()->route('mobile.login');
        }

        if ($redirect = $this->ensureNotAdmin()) return $redirect;

        return redirect()->route('driver.index');
    }

    /**
     * HALAMAN LOGIN RESPONSIF
     */
    public function showLogin(Request $request)
    {
        if (Auth::check()) {
            if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('mekanik')) {
                return redirect()->route('dashboard');
            }

            return redirect()->route('driver.index');
        }

        return view('auth.login');
    }

    /**
     * PROSES LOGIN RESPONSIF
     */
    public function authenticate(Request $request)
    {
        return app(AuthController::class)->authenticate($request);
    }

    /**
     * HALAMAN UTAMA MOBILE: "Pilih Kendaraan Anda" (Driver) / "Pilih Kendaraan" (HSE)
     */
    public function index(Request $request)
    {
        if ($redirect = $this->ensureNotAdmin()) return $redirect;

        $search = $request->query('q');

        $vehicles = Vehicle::where('status', '!=', Vehicle::STATUS_NONAKTIF)
            ->when($search, function ($query, $q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('plat_nomor', 'like', "%{$q}%")
                        ->orWhere('merk', 'like', "%{$q}%")
                        ->orWhere('jenis_kendaraan', 'like', "%{$q}%");
                });
            })
            ->withCount([
                'maintenances as servis_count',
                'checklists as kendala_count' => fn ($q) => $q->where('status', 'kendala'),
            ])
            ->orderBy('plat_nomor')
            ->get();

        return view('driver.index', compact(
            'vehicles',
            'search'
        ));
    }

    /**
     * HALAMAN DETAIL KENDARAAN
     */
    public function showVehicle(Vehicle $vehicle)
    {
        if ($redirect = $this->ensureNotAdmin()) return $redirect;

        $user = Auth::user();

        if ($user->hasRole('driver') || $user->hasRole('hse')) {
            $user->update([
                'assigned_vehicle_id' => $vehicle->id,
                'vehicle_assigned_at' => now(),
            ]);
        }

        $vehicle->loadCount([
            'maintenances as servis_count',
            'checklists as kendala_count' => fn ($q) => $q->where('status', 'kendala'),
        ]);

        // Riwayat Penggunaan Kendaraan
        $usageHistory = Checklist::with(['driver', 'user', 'items'])
            ->where('vehicle_id', $vehicle->id)
            ->latest('tanggal')
            ->latest('id')
            ->take(15)
            ->get();

        $today = now()->toDateString();
        $todayFisik = Checklist::where('vehicle_id', $vehicle->id)
            ->where('checklist_type', 'fisik')
            ->whereDate('tanggal', $today)
            ->latest('id')
            ->first();

        $todayK3 = Checklist::where('vehicle_id', $vehicle->id)
            ->where('checklist_type', 'k3')
            ->whereDate('tanggal', $today)
            ->latest('id')
            ->first();

        return view('driver.vehicle', compact(
            'vehicle',
            'usageHistory',
            'todayFisik',
            'todayK3'
        ));
    }

    /**
     * EXPORT PDF RIWAYAT PENGGUNAAN & K3 KENDARAAN (Tombol Ungu "Export pdf" di Figma 997:6317)
     */
    public function exportVehiclePdf(Vehicle $vehicle)
    {
        if ($redirect = $this->ensureNotAdmin()) return $redirect;

        $vehicle->loadCount([
            'maintenances as servis_count',
            'checklists as kendala_count' => fn ($q) => $q->where('status', 'kendala'),
        ]);

        $usageHistory = Checklist::with(['driver', 'user', 'items'])
            ->where('vehicle_id', $vehicle->id)
            ->latest('tanggal')
            ->latest('id')
            ->take(30)
            ->get();

        return view('driver.vehicle-pdf', compact('vehicle', 'usageHistory'));
    }

    /**
     * HALAMAN FORM CHECKLIST MOBILE (CEK FISIK UNTUK DRIVER / CEK K3 UNTUK HSE)
     */
    public function checklistForm(Vehicle $vehicle, string $type)
    {
        if ($redirect = $this->ensureNotAdmin()) return $redirect;

        if (!in_array($type, ['fisik', 'k3'], true)) {
            abort(404);
        }

        $user = Auth::user();

        // HSE khusus ditugaskan untuk pemeriksaan K3 & Kebersihan, sedangkan Driver tetap bisa Cek Fisik & Cek K3
        if ($user->hasRole('hse') && $type !== 'k3') {
            return redirect()->route('driver.checklist.form', [$vehicle->id, 'k3'])
                ->with('error', 'Role K3/HSE khusus ditugaskan untuk pemeriksaan K3 & Kebersihan.');
        }

        $user->update([
            'assigned_vehicle_id' => $vehicle->id,
            'vehicle_assigned_at' => now(),
        ]);

        $groups = InspectionItem::groups()
            ->where('category', $type)
            ->where('is_active', true)
            ->with(['children' => fn ($q) => $q->where('is_active', true)->orderBy('urutan')->orderBy('id')])
            ->orderBy('urutan')
            ->get();

        $items = collect();
        foreach ($groups as $group) {
            foreach ($group->children as $child) {
                $child->group_name = $group->nama;
                $items->push($child);
            }
        }

        $pageTitle = $type === 'k3'
            ? 'Kondisi kebersihan kendaraan'
            : 'Pemeriksaan fisik kendaraan';

        return view('driver.checklist', compact(
            'vehicle',
            'type',
            'groups',
            'items',
            'pageTitle'
        ));
    }

    /**
     * SIMPAN HASIL CHECKLIST DARI MOBILE DRIVER / HSE
     */
    public function storeChecklist(Request $request, Vehicle $vehicle, string $type)
    {
        if ($redirect = $this->ensureNotAdmin()) return $redirect;

        if (!in_array($type, ['fisik', 'k3'], true)) {
            abort(404);
        }

        $user = Auth::user();

        // Pastikan HSE hanya menyimpan laporan K3 (Driver tetap bisa Fisik & K3 sementara)
        if ($user->hasRole('hse') && $type !== 'k3') {
            abort(403, 'Role K3/HSE hanya dapat mengirim laporan K3.');
        }

        $validated = $request->validate([
            'catatan' => 'nullable|string|max:1000',
            'items'   => 'required|array|min:1',
            'items.*' => 'required|in:0,1',
            'foto'    => 'nullable|array',
            'foto.*'  => 'nullable|image|max:4096',
        ], [
            'items.required'   => 'Harap periksa minimal satu komponen kendaraan.',
            'items.*.required' => 'Semua komponen wajib dipilih keadaannya (Aman / Kendala).',
        ]);

        $hasProblem = in_array('0', $validated['items'], true) || in_array(0, $validated['items'], true);

        $checklist = DB::transaction(function () use ($validated, $request, $vehicle, $type, $hasProblem, $user) {
            $created = Checklist::create([
                'vehicle_id'     => $vehicle->id,
                'checklist_type' => $type,
                'driver_id'      => $user->id,
                'user_id'        => $user->id,
                'tanggal'        => now(),
                'status'         => $hasProblem ? 'kendala' : 'aman',
                'catatan'        => $validated['catatan'] ?? null,
            ]);

            foreach ($validated['items'] as $itemId => $kondisiVal) {
                $inspection = InspectionItem::find($itemId);
                if (!$inspection) {
                    continue;
                }

                $fotoPath = null;
                if ($request->hasFile("foto.{$itemId}")) {
                    $fotoPath = $request->file("foto.{$itemId}")->store('checklists', 'public');
                }

                ChecklistItem::create([
                    'checklist_id'       => $created->id,
                    'inspection_item_id' => $inspection->id,
                    'item'               => $inspection->nama,
                    'criteria'           => $inspection->criteria,
                    'kondisi'            => (string) $kondisiVal === '1',
                    'foto_dokumentasi'   => $fotoPath,
                ]);
            }

            if ($hasProblem) {
                $vehicle->update(['status' => Vehicle::STATUS_RUSAK]);
            }

            return $created;
        });

        $labelType = $type === 'k3' ? 'Cek K3 & Kebersihan' : 'Cek Fisik';
        $message = $hasProblem
            ? "Laporan {$labelType} berhasil dikirim! Ditemukan kendala pada unit {$vehicle->plat_nomor}, status unit diteruskan ke Admin & Mekanik."
            : "Laporan {$labelType} berhasil dikirim! Seluruh komponen unit {$vehicle->plat_nomor} dalam keadaan baik.";

        return redirect()
            ->route('driver.vehicle.show', $vehicle->id)
            ->with('success', $message)
            ->with('last_checklist_id', $checklist->id);
    }

    /**
     * HALAMAN RIWAYAT CHECKLIST MOBILE
     */
    public function history(Request $request)
    {
        if ($redirect = $this->ensureNotAdmin()) return $redirect;

        $user = Auth::user();
        $vehicleId = $request->query('vehicle_id');
        $type = $request->query('type');

        if ($user->hasRole('hse')) {
            $type = 'k3';
        }

        $checklists = Checklist::with(['vehicle', 'driver', 'items'])
            ->when(
                $user->hasRole('driver'),
                fn ($q) => $q->where('driver_id', $user->id)
            )
            ->when(
                $user->hasRole('hse'),
                fn ($q) => $q->where('checklist_type', 'k3')
            )
            ->when($vehicleId, fn ($q) => $q->where('vehicle_id', $vehicleId))
            ->when($type, fn ($q) => $q->where('checklist_type', $type))
            ->latest('tanggal')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $vehicles = Vehicle::orderBy('plat_nomor')->get();

        return view('driver.history', compact('checklists', 'vehicles', 'vehicleId', 'type'));
    }

    /**
     * HALAMAN DETAIL RIWAYAT CHECKLIST MOBILE
     */
    public function historyDetail(Checklist $checklist)
    {
        if ($redirect = $this->ensureNotAdmin()) return $redirect;

        $checklist->load(['vehicle', 'driver', 'items.inspectionItem']);

        return view('driver.history-detail', compact('checklist'));
    }

    /**
     * KHUSUS ROLE HSE: FORM EDIT / GANTI LAPORAN K3
     */
    public function editK3Report(Checklist $checklist)
    {
        if ($checklist->checklist_type !== 'k3') {
            return redirect()->route('driver.history.detail', $checklist->id)
                ->with('error', 'Hanya laporan K3 & Kebersihan yang dapat diubah oleh HSE.');
        }

        $checklist->load(['vehicle', 'items.inspectionItem']);

        return view('driver.checklist-edit', compact('checklist'));
    }

    /**
     * KHUSUS ROLE HSE: UPDATE / SIMPAN PERUBAHAN LAPORAN K3
     */
    public function updateK3Report(Request $request, Checklist $checklist)
    {
        if ($checklist->checklist_type !== 'k3') {
            abort(403, 'Hanya laporan K3 yang dapat diubah oleh HSE.');
        }

        $validated = $request->validate([
            'catatan' => 'nullable|string|max:1000',
            'items'   => 'required|array|min:1',
            'items.*' => 'required|in:0,1',
            'foto'    => 'nullable|array',
            'foto.*'  => 'nullable|image|max:4096',
        ]);

        $hasProblem = in_array('0', $validated['items'], true) || in_array(0, $validated['items'], true);

        DB::transaction(function () use ($checklist, $validated, $request, $hasProblem) {
            $checklist->update([
                'status'  => $hasProblem ? 'kendala' : 'aman',
                'catatan' => $validated['catatan'] ?? null,
                'user_id' => Auth::id(),
            ]);

            foreach ($validated['items'] as $checklistItemId => $kondisiVal) {
                $cItem = ChecklistItem::where('checklist_id', $checklist->id)
                    ->where('id', $checklistItemId)
                    ->first();

                if (! $cItem) {
                    continue;
                }

                $updateData = [
                    'kondisi' => (string) $kondisiVal === '1',
                ];

                if ($request->hasFile("foto.{$checklistItemId}")) {
                    $updateData['foto_dokumentasi'] = $request->file("foto.{$checklistItemId}")->store('checklists', 'public');
                }

                $cItem->update($updateData);
            }

            if ($checklist->vehicle) {
                if ($hasProblem) {
                    $checklist->vehicle->update(['status' => Vehicle::STATUS_RUSAK]);
                } else {
                    $checklist->vehicle->update(['status' => Vehicle::STATUS_SIAP_JALAN]);
                }
            }
        });

        return redirect()
            ->route('driver.history.detail', $checklist->id)
            ->with('success', 'Laporan K3 & Kebersihan berhasil diperbarui dan tersinkronisasi dengan database!');
    }

    /**
     * KHUSUS ROLE HSE: HAPUS LAPORAN K3
     */
    public function destroyK3Report(Checklist $checklist)
    {
        if ($checklist->checklist_type !== 'k3') {
            abort(403, 'Hanya laporan K3 yang dapat dihapus oleh HSE.');
        }

        $vehicleId = $checklist->vehicle_id;

        DB::transaction(function () use ($checklist) {
            $checklist->items()->delete();
            $checklist->delete();
        });

        return redirect()
            ->route('driver.vehicle.show', $vehicleId)
            ->with('success', 'Laporan pemeriksaan K3 berhasil dihapus dari database.');
    }

    /**
     * KHUSUS ROLE HSE: KELOLA ITEM PEMERIKSAAN K3 (Figma "Edit data checklist k3" 1190:5486)
     * HSE hanya dapat menambah, mengedit, dan menghapus item pemeriksaan khusus K3 saja.
     */
    public function k3Items()
    {
        $k3Groups = InspectionItem::groups()
            ->where('category', 'k3')
            ->orderBy('urutan')
            ->get();

        $k3Items = InspectionItem::whereNotNull('parent_id')
            ->where('category', 'k3')
            ->with('parent')
            ->orderBy('parent_id')
            ->orderBy('urutan')
            ->orderBy('id')
            ->get();

        return view('driver.k3-items', compact('k3Groups', 'k3Items'));
    }

    /**
     * KHUSUS ROLE HSE: TAMBAH ITEM PEMERIKSAAN K3 BARU (+ Data)
     */
    public function storeK3Item(Request $request)
    {
        $validated = $request->validate([
            'nama'      => 'required|string|max:150',
            'criteria'  => 'required|string|max:500',
            'parent_id' => 'nullable|exists:inspection_items,id',
        ]);

        $group = null;
        if (! empty($validated['parent_id'])) {
            $group = InspectionItem::whereNull('parent_id')
                ->where('category', 'k3')
                ->find($validated['parent_id']);
        }

        if (! $group) {
            $group = InspectionItem::firstOrCreate(
                ['parent_id' => null, 'nama' => 'Kebersihan Kendaraan', 'category' => 'k3'],
                ['urutan' => 2, 'is_active' => true]
            );
        }

        $nextOrder = (InspectionItem::where('parent_id', $group->id)->max('urutan') ?? 0) + 1;

        InspectionItem::create([
            'parent_id' => $group->id,
            'nama'      => $validated['nama'],
            'category'  => 'k3',
            'criteria'  => $validated['criteria'],
            'urutan'    => $nextOrder,
            'is_active' => true,
            'user_edit' => Auth::id(),
        ]);

        return back()->with('success', 'Item pemeriksaan K3 baru berhasil ditambahkan ke database.');
    }

    /**
     * KHUSUS ROLE HSE: EDIT ITEM PEMERIKSAAN K3
     */
    public function updateK3Item(Request $request, InspectionItem $inspectionItem)
    {
        if ($inspectionItem->category !== 'k3' || $inspectionItem->parent_id === null) {
            abort(403, 'HSE hanya dapat mengubah item pemeriksaan kategori K3.');
        }

        $validated = $request->validate([
            'nama'      => 'required|string|max:150',
            'criteria'  => 'required|string|max:500',
            'parent_id' => 'nullable|exists:inspection_items,id',
        ]);

        $parentId = $inspectionItem->parent_id;
        if (! empty($validated['parent_id'])) {
            $validGroup = InspectionItem::whereNull('parent_id')
                ->where('category', 'k3')
                ->find($validated['parent_id']);
            if ($validGroup) {
                $parentId = $validGroup->id;
            }
        }

        $inspectionItem->update([
            'parent_id' => $parentId,
            'nama'      => $validated['nama'],
            'criteria'  => $validated['criteria'],
            'user_edit' => Auth::id(),
        ]);

        return back()->with('success', 'Item pemeriksaan K3 berhasil diperbarui.');
    }

    /**
     * KHUSUS ROLE HSE: HAPUS ITEM PEMERIKSAAN K3
     */
    public function destroyK3Item(InspectionItem $inspectionItem)
    {
        if ($inspectionItem->category !== 'k3' || $inspectionItem->parent_id === null) {
            abort(403, 'HSE hanya dapat menghapus item pemeriksaan kategori K3.');
        }

        $inspectionItem->delete();

        return back()->with('success', 'Item pemeriksaan K3 berhasil dihapus dari database.');
    }

    /**
     * HALAMAN INFORMASI AKUN MOBILE
     */
    public function profile()
    {
        if ($redirect = $this->ensureNotAdmin()) return $redirect;

        $user = Auth::user();
        $totalChecklists = Checklist::where('driver_id', $user->id)->count();
        $thisMonthChecklists = Checklist::where('driver_id', $user->id)
            ->whereMonth('tanggal', now()->month)
            ->whereYear('tanggal', now()->year)
            ->count();

        $assignedVehicle = $user->assignedVehicle;

        return view('driver.profile', compact(
            'user',
            'totalChecklists',
            'thisMonthChecklists',
            'assignedVehicle'
        ));
    }

    /**
     * HALAMAN NOTIFIKASI & PANDUAN MANUAL MOBILE
     */
    public function notifications()
    {
        if ($redirect = $this->ensureNotAdmin()) return $redirect;

        $user = Auth::user();

        $recentAlerts = Checklist::with(['vehicle', 'items' => fn ($q) => $q->where('kondisi', false)])
            ->where(function ($q) use ($user) {
                if ($user->hasRole('driver')) {
                    $q->where('driver_id', $user->id)->where('checklist_type', 'fisik');
                } elseif ($user->hasRole('hse')) {
                    $q->where('checklist_type', 'k3');
                }
            })
            ->latest('tanggal')
            ->take(10)
            ->get();

        return view('driver.notifications', compact('recentAlerts'));
    }

    /**
     * HALAMAN CETAK SEMUA QR CODE ARMADA
     */
    public function printAllQr(Request $request)
    {
        $vehicleId = $request->query('vehicle_id');

        $vehicles = Vehicle::when($vehicleId, fn ($q) => $q->where('id', $vehicleId))
            ->orderBy('plat_nomor')
            ->get();

        return view('vehicles.qr-print', compact('vehicles'));
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Checklist;
use App\Models\Maintenance;
use App\Models\MaintenanceDetail;
use App\Models\Sparepart;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Controller Log & Catatan Perbaikan Armada (SRS: Modul Maintenance).
 * Mengatur alur: Unit Rusak -> Perbaikan Mekanik -> Penggunaan Sparepart -> Selesai (Unit Siap Jalan).
 */
class MaintenanceController extends Controller
{
    /**
     * TAMPILKAN DAFTAR CATATAN PERBAIKAN
     */
    public function index(Request $request)
    {
        if (auth()->check() && auth()->user()->hasRole('mekanik')) {
            return redirect()->route('mekanik.repairs');
        }

        $status = $request->query('status');

        $maintenances = Maintenance::with([
                'vehicle',
                'mekanik',
                'details.sparepart',
                'sparepartRequests.requester',
                'sparepartRequests.sparepart',
            ])
            ->when($status, fn ($q) => $q->where('status_maintenance', $status))
            ->latest('tanggal_maintenance')
            ->latest('id')
            ->paginate(15);

        $stats = [
            'total'       => Maintenance::count(),
            'pending'     => Maintenance::where('status_maintenance', Maintenance::STATUS_PENDING)->count(),
            'in_progress' => Maintenance::where('status_maintenance', Maintenance::STATUS_IN_PROGRESS)->count(),
            'completed'   => Maintenance::where('status_maintenance', Maintenance::STATUS_COMPLETED)->count(),
        ];

        // Daftar armada untuk form input log perbaikan
        $vehicles = Vehicle::where('status', '!=', Vehicle::STATUS_NONAKTIF)    
            ->get();

        // Daftar mekanik (jika belum ada user bertole mekanik, ambil semua user aktif)
        $mekaniks = User::role('mekanik')->get();
        if ($mekaniks->isEmpty()) {
            $mekaniks = User::all();
        }

        // Katalog sparepart bengkel & vendor aktif untuk pengajuan ke pengadaan
        $spareparts = Sparepart::orderBy('nama_sparepart')->get();
        $vendors = Vendor::where('is_active', true)->orderBy('nama_vendor')->get();

        return view('maintenances.index', compact(
            'maintenances',
            'stats',
            'vehicles',
            'mekaniks',
            'spareparts',
            'vendors',
            'status'
        ));
    }

    /**
     * EXPORT CATATAN PERBAIKAN KE PDF (TAMPILAN CETAK / SIMPAN PDF)
     */
    public function exportPdf(Request $request)
    {
        $status = $request->query('status');
        $search = $request->query('q');

        $maintenances = Maintenance::with([
                'vehicle',
                'mekanik',
                'details.sparepart',
                'checklist.driver',
            ])
            ->when($status, fn ($q) => $q->where('status_maintenance', $status))
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->whereHas('vehicle', fn ($v) => $v->where('plat_nomor', 'like', "%{$search}%")->orWhere('merk', 'like', "%{$search}%")->orWhere('jenis_kendaraan', 'like', "%{$search}%"))
                        ->orWhereHas('mekanik', fn ($m) => $m->where('name', 'like', "%{$search}%"))
                        ->orWhere('deskripsi_masalah', 'like', "%{$search}%")
                        ->orWhere('kategori_kendala', 'like', "%{$search}%");
                });
            })
            ->latest('tanggal_maintenance')
            ->latest('id')
            ->get();

        $stats = [
            'total'       => Maintenance::count(),
            'pending'     => Maintenance::where('status_maintenance', Maintenance::STATUS_PENDING)->count(),
            'in_progress' => Maintenance::where('status_maintenance', Maintenance::STATUS_IN_PROGRESS)->count(),
            'completed'   => Maintenance::where('status_maintenance', Maintenance::STATUS_COMPLETED)->count(),
        ];

        return view('maintenances.pdf', compact('maintenances', 'stats', 'status', 'search'));
    }

    /**
     * SIMPAN LOG PERBAIKAN BARU
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'vehicle_id'          => 'required|exists:vehicles,id',
            'mekanik_id'          => 'required|exists:users,id',
            'checklist_id'        => 'nullable|exists:checklists,id',
            'tanggal_maintenance'=> 'required|date',
            'status_maintenance'  => ['required', Rule::in([
                Maintenance::STATUS_PENDING,
                Maintenance::STATUS_IN_PROGRESS,
                Maintenance::STATUS_COMPLETED,
            ])],
            'deskripsi_masalah'   => 'required|string',
            'solusi'              => 'nullable|string',
            'catatan'             => 'nullable|string',
            'spareparts'          => 'nullable|array',
            'spareparts.*.id'     => 'required_with:spareparts|exists:spareparts,id',
            'spareparts.*.qty'    => 'required_with:spareparts|integer|min:1',
        ]);

        DB::transaction(function () use ($validated, $request) {

            $maintenance = Maintenance::create([
                'vehicle_id'           => $validated['vehicle_id'],
                'mekanik_id'           => $validated['mekanik_id'],
                'checklist_id'         => $validated['checklist_id'] ?? null,
                'tanggal_maintenance' => $validated['tanggal_maintenance'],
                'status_maintenance'   => $validated['status_maintenance'],
                'deskripsi_masalah'    => $validated['deskripsi_masalah'],
                'solusi'               => $validated['solusi'] ?? null,
                'catatan'              => $validated['catatan'] ?? null,
            ]);

            // Catat penggunaan sparepart dan potong stok
            if (!empty($request->spareparts)) {
                foreach ($request->spareparts as $item) {
                    if (empty($item['id']) || empty($item['qty'])) {
                        continue;
                    }

                    $sparepart = Sparepart::find($item['id']);
                    if (!$sparepart) continue;

                    MaintenanceDetail::create([
                        'maintenance_id' => $maintenance->id,
                        'sparepart_id'   => $sparepart->id,
                        'nama_sparepart' => $sparepart->nama_sparepart,
                        'qty'            => (int) $item['qty'],
                    ]);

                    // Kurangi stok jika perbaikan sedang atau selesai dikerjakan
                    if ($maintenance->status_maintenance !== Maintenance::STATUS_PENDING) {
                        $sparepart->decrement('stok', (int) $item['qty']);
                    }
                }
            }

            // Sinkronisasi status kendaraan otomatis (SRS poin 3 & 4)
            $vehicle = Vehicle::find($validated['vehicle_id']);
            if ($vehicle) {
                if ($maintenance->status_maintenance === Maintenance::STATUS_COMPLETED) {
                    $vehicle->update(['status' => Vehicle::STATUS_SIAP_JALAN]);
                } elseif ($maintenance->status_maintenance === Maintenance::STATUS_IN_PROGRESS) {
                    $vehicle->update(['status' => Vehicle::STATUS_MAINTENANCE]);
                } else {
                    $vehicle->update(['status' => Vehicle::STATUS_RUSAK]);
                }
            }
        });

        return back()->with('success', 'Catatan perbaikan berhasil disimpan.');
    }

    /**
     * DETAIL CATATAN PERBAIKAN
     */
    public function show($id)
    {
        $maintenance = Maintenance::with([
                'vehicle',
                'mekanik',
                'checklist',
                'details.sparepart',
                'sparepartRequests.requester',
                'sparepartRequests.sparepart',
            ])
            ->findOrFail($id);

        $spareparts = Sparepart::orderBy('nama_sparepart')->get();
        $vendors = Vendor::where('is_active', true)->orderBy('nama_vendor')->get();

        return view('maintenances.show', compact('maintenance', 'spareparts', 'vendors'));
    }

    /**
     * UPDATE STATUS, DETAIL KENDALA & TINDAKAN PERBAIKAN
     */
    public function update(Request $request, $id)
    {
        $maintenance = Maintenance::findOrFail($id);

        $validated = $request->validate([
            'status_maintenance' => ['required', Rule::in([
                Maintenance::STATUS_PENDING,
                Maintenance::STATUS_IN_PROGRESS,
                Maintenance::STATUS_COMPLETED,
            ])],
            'deskripsi_masalah'  => 'nullable|string',
            'kategori_kendala'   => 'nullable|string|max:120',
            'nama_driver'        => 'nullable|string|max:120',
            'estimasi_downtime'  => 'nullable|string|max:120',
            'solusi'             => 'nullable|string',
            'catatan'            => 'nullable|string',
        ]);

        DB::transaction(function () use ($maintenance, $validated) {
            $oldStatus = $maintenance->status_maintenance;
            $newStatus = $validated['status_maintenance'];

            $maintenance->update([
                'status_maintenance' => $newStatus,
                'deskripsi_masalah'  => array_key_exists('deskripsi_masalah', $validated) && filled($validated['deskripsi_masalah'])
                    ? $validated['deskripsi_masalah']
                    : $maintenance->deskripsi_masalah,
                'kategori_kendala'   => $validated['kategori_kendala'] ?? $maintenance->kategori_kendala,
                'nama_driver'        => $validated['nama_driver'] ?? $maintenance->nama_driver,
                'estimasi_downtime'  => $validated['estimasi_downtime'] ?? $maintenance->estimasi_downtime,
                'solusi'             => $validated['solusi'] ?? $maintenance->solusi,
                'catatan'            => $validated['catatan'] ?? $maintenance->catatan,
            ]);

            // Jika sebelumnya pending dan sekarang in_progress / completed, potong stok sparepart yang tercatat
            if ($oldStatus === Maintenance::STATUS_PENDING && $newStatus !== Maintenance::STATUS_PENDING) {
                foreach ($maintenance->details as $detail) {
                    if ($detail->sparepart) {
                        $detail->sparepart->decrement('stok', $detail->qty);
                    }
                }
            }

            // Update status armada
            $vehicle = $maintenance->vehicle;
            if ($vehicle) {
                if ($newStatus === Maintenance::STATUS_COMPLETED) {
                    $vehicle->update(['status' => Vehicle::STATUS_SIAP_JALAN]);
                } elseif ($newStatus === Maintenance::STATUS_IN_PROGRESS) {
                    $vehicle->update(['status' => Vehicle::STATUS_MAINTENANCE]);
                }
            }
        });

        return back()->with('success', 'Catatan perbaikan berhasil diperbarui.');
    }

    /**
     * HAPUS LOG PERBAIKAN (Hanya Admin / SMD)
     */
    public function destroy($id)
    {
        $maintenance = Maintenance::findOrFail($id);
        $maintenance->delete();

        return back()->with('success', 'Catatan perbaikan berhasil dihapus.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VehicleController extends Controller
{
    // TAMPILKAN DAFTAR ARMADA
    public function index()
    {
        $vehicles = Vehicle::query()
            ->orderBy('plat_nomor', 'asc')
            ->paginate(15);

        // Summary counts untuk stat cards
        $stats = [
            'total'       => Vehicle::count(),
            'siap_jalan'  => Vehicle::where('status', Vehicle::STATUS_SIAP_JALAN)->count(),
            'maintenance' => Vehicle::where('status', Vehicle::STATUS_MAINTENANCE)->count(),
            'rusak'       => Vehicle::where('status', Vehicle::STATUS_RUSAK)->count(),
            'nonaktif'    => Vehicle::where('status', Vehicle::STATUS_NONAKTIF)->count(),
        ];

        return view('vehicles.index', compact('vehicles', 'stats'));
    }

    // SIMPAN ARMADA BARU
    public function store(Request $request)
    {
        $validated = $request->validate([
            'plat_nomor'      => 'required|string|max:15|unique:vehicles,plat_nomor',
            'merk'            => 'required|string|max:50',
            'jenis_kendaraan' => 'required|string|max:50',
            'tahun'           => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
            'dimensi_box'     => 'nullable|string|max:100',
            'tahun_stnk'      => 'nullable|string|max:30',
            'tahun_kir'       => 'nullable|string|max:30',
            'status'          => 'required|in:siap_jalan,maintenance,rusak,nonaktif',
        ]);

        Vehicle::create($validated);

        return back()->with('success', 'Armada berhasil ditambahkan.');
    }

    // UPDATE ARMADA
    public function update(Request $request, $id)
    {
        $vehicle = Vehicle::findOrFail($id);

        $validated = $request->validate([
            'plat_nomor'      => ['required', 'string', 'max:15', Rule::unique('vehicles', 'plat_nomor')->ignore($vehicle->id)],
            'merk'            => 'required|string|max:50',
            'jenis_kendaraan' => 'required|string|max:50',
            'tahun'           => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
            'dimensi_box'     => 'nullable|string|max:100',
            'tahun_stnk'      => 'nullable|string|max:30',
            'tahun_kir'       => 'nullable|string|max:30',
            'status'          => 'required|in:siap_jalan,maintenance,rusak,nonaktif',
        ]);

        $vehicle->update($validated);

        return back()->with('success', 'Data armada berhasil diperbarui.');
    }

    // HAPUS ARMADA
    public function destroy($id)
    {
        $vehicle = Vehicle::findOrFail($id);

        \Illuminate\Support\Facades\DB::transaction(function () use ($vehicle) {
            // 1. Lepas penugasan armada dari user/driver
            \App\Models\User::where('assigned_vehicle_id', $vehicle->id)->update([
                'assigned_vehicle_id' => null,
                'vehicle_assigned_at' => null,
            ]);

            // 2. Hapus relasi checklists berserta item checklistnya
            $checklists = \App\Models\Checklist::where('vehicle_id', $vehicle->id)->get();
            foreach ($checklists as $chk) {
                $chk->items()->delete();
                $chk->delete();
            }

            // 3. Hapus relasi maintenances beserta details pemakaian sparepartnya
            $maintenances = \App\Models\Maintenance::where('vehicle_id', $vehicle->id)->get();
            foreach ($maintenances as $m) {
                // Lepaskan referensi maintenance_id pada sparepart_requests agar riwayat permintaan tetap ada
                \App\Models\SparepartRequest::where('maintenance_id', $m->id)->update(['maintenance_id' => null]);
                $m->details()->delete();
                $m->delete();
            }

            // 4. Hapus data armada utama
            $vehicle->delete();
        });

        return back()->with('success', 'Armada beserta seluruh riwayat terkait berhasil dihapus.');
    }
}

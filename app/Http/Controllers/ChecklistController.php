<?php

namespace App\Http\Controllers;

use App\Models\Checklist;
use App\Models\ChecklistItem;
use App\Models\InspectionItem;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

/**
 * Controller Checklist Inspeksi Harian Armada (SRS: Modul Checklist).
 * - Driver: Checklist Fisik Kendaraan sebelum jalan.
 * - HSE: Checklist K3 & Kebersihan Kendaraan.
 * - SMD / Admin & Mekanik: Monitoring seluruh checklist & temuan kendala.
 */
class ChecklistController extends Controller
{
    /**
     * TAMPILKAN DAFTAR CHECKLIST
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Tentukan tipe checklist yang diizinkan untuk diisi oleh user saat ini
        if ($user->hasRole('driver')) {
            $allowedTypes = ['fisik'];
        } elseif ($user->hasRole('hse')) {
            $allowedTypes = ['k3'];
        } else {
            // Admin hanya bertugas memonitor hasil checklist, tidak menginput checklist
            $allowedTypes = [];
        }

        $typeFilter   = $request->query('type');
        $statusFilter = $request->query('status');

        $checklists = Checklist::with([
                'vehicle',
                'driver',
                'user',
                'items',
            ])
            ->when($typeFilter, fn ($q) => $q->where('checklist_type', $typeFilter))
            ->when($statusFilter, fn ($q) => $q->where('status', $statusFilter))
            ->latest('tanggal')
            ->latest('id')
            ->paginate(15);

        $today = now()->toDateString();

        $stats = [
            'total'    => Checklist::count(),
            'hari_ini' => Checklist::whereDate('tanggal', $today)->count(),
            'aman'     => Checklist::where('status', 'aman')->count(),
            'kendala'  => Checklist::where('status', 'kendala')->count(),
        ];

        // Daftar unit armada aktif yang dapat diperiksa
        $vehicles = Vehicle::where('status', '!=', Vehicle::STATUS_NONAKTIF)
            ->orderBy('kode_armada')
            ->get();

        // Parameter pemeriksaan aktif per kategori (Fisik dan K3)
        $inspectionGroups = [
            'fisik' => InspectionItem::groups()
                ->where('category', 'fisik')
                ->where('is_active', true)
                ->with(['children' => fn ($q) => $q->where('is_active', true)->orderBy('urutan')->orderBy('id')])
                ->orderBy('urutan')
                ->get(),
            'k3'    => InspectionItem::groups()
                ->where('category', 'k3')
                ->where('is_active', true)
                ->with(['children' => fn ($q) => $q->where('is_active', true)->orderBy('urutan')->orderBy('id')])
                ->orderBy('urutan')
                ->get(),
        ];

        return view('checklists.index', compact(
            'checklists',
            'stats',
            'vehicles',
            'inspectionGroups',
            'allowedTypes',
            'typeFilter',
            'statusFilter'
        ));
    }

    /**
     * SIMPAN CHECKLIST BARU
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'vehicle_id'     => 'required|exists:vehicles,id',
            'checklist_type' => ['required', Rule::in(['fisik', 'k3'])],
            'catatan'        => 'nullable|string',
            'items'          => 'required|array',
            'items.*'        => 'required|in:0,1',
            'foto'           => 'nullable|array',
            'foto.*'         => 'nullable|image|max:3072',
        ]);

        // Cek apakah ada item yang bernilai '0' (bermasalah)
        $hasProblem = in_array('0', $validated['items'], true) || in_array(0, $validated['items'], true);

        DB::transaction(function () use ($validated, $request, $hasProblem) {
            $user = Auth::user();

            $checklist = Checklist::create([
                'vehicle_id'     => $validated['vehicle_id'],
                'checklist_type' => $validated['checklist_type'],
                'driver_id'      => $user->id,
                'user_id'        => $user->id,
                'tanggal'        => now(),
                'status'         => $hasProblem ? 'kendala' : 'aman',
                'catatan'        => $validated['catatan'] ?? null,
            ]);

            // Simpan setiap item pemeriksaan
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
                    'checklist_id'       => $checklist->id,
                    'inspection_item_id' => $inspection->id,
                    'item'               => $inspection->nama,
                    'criteria'           => $inspection->criteria,
                    'kondisi'            => (string) $kondisiVal === '1',
                    'foto_dokumentasi'   => $fotoPath,
                ]);
            }

            // Jika ada temuan kendala/kerusakan, otomatis ubah status armada menjadi 'rusak' (SRS alur bisnis)
            if ($hasProblem) {
                Vehicle::where('id', $validated['vehicle_id'])
                    ->update(['status' => Vehicle::STATUS_RUSAK]);
            }
        });

        $msg = $hasProblem
            ? 'Checklist berhasil disimpan. Unit armada ditandai berstatus "Rusak" dan menunggu penanganan mekanik.'
            : 'Checklist berhasil disimpan. Semua item dalam kondisi aman.';

        return back()->with('success', $msg);
    }

    /**
     * DETAIL CHECKLIST
     */
    public function show($id)
    {
        $checklist = Checklist::with([
                'vehicle',
                'driver',
                'user',
                'items.inspectionItem',
            ])
            ->findOrFail($id);

        return view('checklists.show', compact('checklist'));
    }

    /**
     * FORM EDIT HASIL CHECKLIST (FISIK & K3) OLEH ADMIN DISTRIBUSI
     */
    public function edit($id)
    {
        $checklist = Checklist::with([
                'vehicle',
                'driver',
                'user',
                'items.inspectionItem',
            ])
            ->findOrFail($id);

        return view('checklists.edit', compact('checklist'));
    }

    /**
     * SIMPAN PERUBAHAN HASIL CHECKLIST (FISIK & K3)
     */
    public function update(Request $request, $id)
    {
        $checklist = Checklist::with(['vehicle', 'items'])->findOrFail($id);

        $validated = $request->validate([
            'catatan' => 'nullable|string|max:1000',
            'tanggal' => 'nullable|date',
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
                'tanggal' => ! empty($validated['tanggal']) ? $validated['tanggal'] : $checklist->tanggal,
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
                } elseif ($checklist->vehicle->status === Vehicle::STATUS_RUSAK) {
                    $checklist->vehicle->update(['status' => Vehicle::STATUS_SIAP_JALAN]);
                }
            }
        });

        $jenisLabel = $checklist->checklist_type === 'k3' ? 'K3 & Kebersihan' : 'Fisik Kendaraan';

        return redirect()
            ->route('checklists.show', $checklist->id)
            ->with('success', "Hasil checklist {$jenisLabel} unit {$checklist->vehicle->plat_nomor} berhasil diperbarui.");
    }
}
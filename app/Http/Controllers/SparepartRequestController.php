<?php

namespace App\Http\Controllers;

use App\Models\Maintenance;
use App\Models\Sparepart;
use App\Models\SparepartRequest;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Permintaan suku cadang: Mekanik / Admin mengajukan, Admin Distribusi menyetujui atau menolak.
 */
class SparepartRequestController extends Controller
{
    public function index(Request $request)
    {
        if (auth()->check() && auth()->user()->hasRole('mekanik')) {
            return redirect()->route('mekanik.approvals');
        }
        if (auth()->check() && auth()->user()->hasRole('pengadaan')) {
            return redirect()->route('pengadaan.requests', $request->query());
        }

        $status = $request->query('status');
        $user   = auth()->user();

        $requests = SparepartRequest::with(['sparepart', 'maintenance.vehicle', 'requester', 'reviewer'])
            // Selain Admin & Pengadaan hanya melihat permintaannya sendiri
            ->when(! $user->hasAnyRole(['admin', 'pengadaan']), fn ($q) => $q->where('requested_by', $user->id))
            ->when($status, fn ($q, $v) => $q->where('status', $v))
            ->latest()
            ->paginate($user->perPage())
            ->withQueryString();

        $stats = [
            'total'    => SparepartRequest::count(),
            'pending'  => SparepartRequest::where('status', SparepartRequest::STATUS_PENDING)->count(),
            'approved' => SparepartRequest::where('status', SparepartRequest::STATUS_APPROVED)->count(),
            'rejected' => SparepartRequest::where('status', SparepartRequest::STATUS_REJECTED)->count(),
        ];

        $spareparts   = Sparepart::orderBy('nama_sparepart')->get();
        $vendors      = Vendor::where('is_active', true)->orderBy('nama_vendor')->get();
        $maintenances = Maintenance::with('vehicle')
            ->where('status_maintenance', '!=', Maintenance::STATUS_COMPLETED)
            ->latest('tanggal_maintenance')
            ->get();

        return view('sparepart-requests.index', compact(
            'requests', 'stats', 'spareparts', 'vendors', 'maintenances', 'status'
        ));
    }

    public function store(Request $request)
    {
        $validated = $this->validatedPayload($request);

        SparepartRequest::create($validated + [
            'requested_by' => auth()->id(),
            'status'       => SparepartRequest::STATUS_PENDING,
        ]);

        return back()->with('success', 'Pengajuan suku cadang berhasil dikirim ke Pengadaan.');
    }

    /**
     * Admin mengedit permintaan suku cadang dari mekanik atau pengajuan mandiri.
     */
    public function update(Request $request, SparepartRequest $sparepartRequest)
    {
        $validated = $this->validatedPayload($request);

        $sparepartRequest->update($validated);

        return back()->with('success', "Permintaan {$sparepartRequest->nama_sparepart} berhasil diperbarui.");
    }

    /**
     * Validasi bersama dan resolusi nama suku cadang dari katalog untuk store & update.
     */
    private function validatedPayload(Request $request): array
    {
        $validated = $request->validate([
            'sparepart_id'           => 'nullable|exists:spareparts,id',
            'nama_sparepart'         => 'required_without:sparepart_id|nullable|string|max:150',
            'qty'                    => 'required|integer|min:1',
            'maintenance_id'         => 'nullable|exists:maintenances,id',
            'priority_level'         => ['nullable', Rule::in(['Rendah', 'Normal', 'Tinggi', 'Urgent'])],
            'expected_delivery_date' => 'nullable|date',
            'vendor_name'            => 'nullable|string|max:150',
            'alasan'                 => 'nullable|string',
            'catatan_admin'          => 'nullable|string',
        ]);

        if (! empty($validated['sparepart_id'])) {
            $validated['nama_sparepart'] = Sparepart::find($validated['sparepart_id'])->nama_sparepart;
        }

        if (empty($validated['priority_level'])) {
            $validated['priority_level'] = 'Normal';
        }

        return $validated;
    }

    public function approve(Request $request, SparepartRequest $sparepartRequest)
    {
        return $this->review($request, $sparepartRequest, SparepartRequest::STATUS_APPROVED);
    }

    public function reject(Request $request, SparepartRequest $sparepartRequest)
    {
        $request->validate([
            'catatan_admin' => 'required|string',
        ], [
            'catatan_admin.required' => 'Alasan penolakan wajib diisi.',
        ]);

        return $this->review($request, $sparepartRequest, SparepartRequest::STATUS_REJECTED);
    }

    public function destroy(SparepartRequest $sparepartRequest)
    {
        if (! $sparepartRequest->isPending()) {
            return back()->with('error', 'Hanya permintaan berstatus pending yang dapat dihapus.');
        }

        $sparepartRequest->delete();

        return back()->with('success', 'Permintaan berhasil dihapus.');
    }

    private function review(Request $request, SparepartRequest $sparepartRequest, string $status)
    {
        if (! $sparepartRequest->isPending()) {
            return back()->with('error', 'Permintaan ini sudah diproses sebelumnya.');
        }

        $sparepartRequest->update([
            'status'        => $status,
            'catatan_admin' => $request->input('catatan_admin'),
            'reviewed_by'   => auth()->id(),
            'reviewed_at'   => now(),
        ]);

        // Tambah stok otomatis ke inventory apabila permintaan disetujui dan terhubung ke katalog
        if ($status === SparepartRequest::STATUS_APPROVED && $sparepartRequest->sparepart) {
            $sparepartRequest->sparepart->increment('stok', $sparepartRequest->qty);
        }

        $label = $status === SparepartRequest::STATUS_APPROVED ? 'disetujui' : 'ditolak';

        return back()->with('success', "Permintaan {$sparepartRequest->nama_sparepart} berhasil {$label}.");
    }
}

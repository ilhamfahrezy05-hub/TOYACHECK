<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Sparepart;
use App\Models\SparepartRequest;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PengadaanController extends Controller
{
    /**
     * 1. DASHBOARD PENGADAAN (Redirect ke /dashboard terpadu)
     */
    public function dashboard(Request $request)
    {
        return redirect()->route('dashboard', $request->query());
    }

    /**
     * 2. PERMINTAAN PENGADAAN — Master-Detail View (Figma 918:24651 & 1113:1272)
     */
    public function requests(Request $request)
    {
        $search     = $request->query('q');
        $status     = $request->query('status');
        $selectedId = $request->query('selected');

        $requests = SparepartRequest::with(['sparepart', 'requester', 'reviewer', 'maintenance.vehicle'])
            ->when($search, function ($q, $s) {
                $q->where(function ($sub) use ($s) {
                    $sub->where('nama_sparepart', 'like', "%{$s}%")
                        ->orWhere('vendor_name', 'like', "%{$s}%")
                        ->orWhere('alasan', 'like', "%{$s}%");
                });
            })
            ->when($status && in_array($status, ['pending', 'approved', 'rejected']), fn ($q) => $q->where('status', $status))
            ->orderByRaw("CASE WHEN status = 'pending' THEN 0 WHEN status = 'approved' THEN 1 ELSE 2 END")
            ->latest('created_at')
            ->latest('id')
            ->get();

        $selectedRequest = null;
        if ($selectedId) {
            $selectedRequest = $requests->firstWhere('id', (int) $selectedId)
                ?? SparepartRequest::with(['sparepart', 'requester', 'reviewer', 'maintenance.vehicle'])->find($selectedId);
        }
        if (! $selectedRequest && $requests->isNotEmpty()) {
            $selectedRequest = $requests->first();
        }

        return view('pengadaan.requests', compact(
            'requests',
            'selectedRequest',
            'search',
            'status'
        ));
    }

    /**
     * 2b. DETAIL PENUH PERMINTAAN & TAHAPAN PENGADAAN (Figma 918:26597)
     */
    public function showRequest(SparepartRequest $sparepartRequest)
    {
        $sparepartRequest->load(['sparepart', 'requester', 'reviewer', 'maintenance.vehicle']);

        $linkedPurchase = Purchase::where('sparepart_request_id', $sparepartRequest->id)->latest()->first();

        return view('pengadaan.request-show', compact('sparepartRequest', 'linkedPurchase'));
    }

    /**
     * SETUJUI PERMINTAAN (✓ Diterima)
     */
    public function approveRequest(Request $request, SparepartRequest $sparepartRequest)
    {
        if (! $sparepartRequest->isPending()) {
            return back()->with('error', 'Permintaan ini sudah diproses sebelumnya.');
        }

        $sparepartRequest->update([
            'status'        => SparepartRequest::STATUS_APPROVED,
            'catatan_admin' => $request->input('catatan_admin'),
            'reviewed_by'   => auth()->id(),
            'reviewed_at'   => now(),
        ]);

        if ($sparepartRequest->sparepart) {
            $sparepartRequest->sparepart->increment('stok', $sparepartRequest->qty);
        }

        return back()->with('success', "Permintaan #{$sparepartRequest->id} ({$sparepartRequest->nama_sparepart}) telah diterima/disetujui.");
    }

    /**
     * TOLAK PERMINTAAN (⊘ Ditolak)
     */
    public function rejectRequest(Request $request, SparepartRequest $sparepartRequest)
    {
        if (! $sparepartRequest->isPending()) {
            return back()->with('error', 'Permintaan ini sudah diproses sebelumnya.');
        }

        $sparepartRequest->update([
            'status'        => SparepartRequest::STATUS_REJECTED,
            'catatan_admin' => $request->input('catatan_admin') ?: 'Ditolak oleh bagian Pengadaan.',
            'reviewed_by'   => auth()->id(),
            'reviewed_at'   => now(),
        ]);

        return back()->with('success', "Permintaan #{$sparepartRequest->id} ({$sparepartRequest->nama_sparepart}) telah ditolak.");
    }

    /**
     * HAPUS PERMINTAAN (Modal Hapus Figma 1113:230)
     */
    public function destroyRequest(SparepartRequest $sparepartRequest)
    {
        $nama = $sparepartRequest->nama_sparepart;
        $sparepartRequest->delete();

        return redirect()
            ->route('pengadaan.requests')
            ->with('success', "Data permintaan {$nama} berhasil dihapus.");
    }

    /**
     * 3. LOKASI VENDOR & MAP LOGISTIK (Figma 918:36897)
     */
    public function vendors(Request $request)
    {
        $search = $request->query('q');

        $vendors = Vendor::query()
            ->when($search, function ($q, $s) {
                $q->where(function ($sub) use ($s) {
                    $sub->where('nama_vendor', 'like', "%{$s}%")
                        ->orWhere('keahlian', 'like', "%{$s}%")
                        ->orWhere('alamat', 'like', "%{$s}%")
                        ->orWhere('kota', 'like', "%{$s}%")
                        ->orWhere('email', 'like', "%{$s}%");
                });
            })
            ->orderBy('nama_vendor')
            ->get();

        $totalVendors  = Vendor::count();
        $totalKriteria = Vendor::whereNotNull('keahlian')->where('keahlian', '!=', '')->distinct('keahlian')->count('keahlian');
        if ($totalKriteria === 0) {
            $totalKriteria = 1;
        }

        return view('pengadaan.vendors', compact(
            'vendors',
            'totalVendors',
            'totalKriteria',
            'search'
        ));
    }

    /**
     * 3b. FORM PENDAFTARAN VENDOR BARU (Figma 1265:5181 & 1270:5412)
     */
    public function createVendor()
    {
        $spesialisOptions = [
            'Tires',
            'Engines',
            'Diesel Parts',
            'Body Work',
            'Brakes',
            'Electronics',
            'Filters',
            'Brakes & Suspension',
            'Oli & Pelumas',
            'Suku Cadang Umum',
        ];

        return view('pengadaan.vendors-create', compact('spesialisOptions'));
    }

    /**
     * SIMPAN VENDOR BARU
     */
    public function storeVendor(Request $request)
    {
        $validated = $request->validate([
            'nama_vendor'   => 'required|string|max:150',
            'keahlian'      => 'nullable|string|max:120',
            'kontak_person' => 'nullable|string|max:120',
            'telepon'       => 'nullable|string|max:30',
            'email'         => 'nullable|email|max:150',
            'alamat'        => 'required|string',
            'kota'          => 'nullable|string|max:100',
            'provinsi'      => 'nullable|string|max:100',
            'latitude'      => 'nullable|string|max:40',
            'longitude'     => 'nullable|string|max:40',
        ]);

        Vendor::create($validated + [
            'is_active' => true,
        ]);

        return redirect()
            ->route('pengadaan.vendors')
            ->with('success', 'Lokasi dan data vendor baru berhasil disimpan.');
    }

    /**
     * UPDATE VENDOR
     */
    public function updateVendor(Request $request, Vendor $vendor)
    {
        $validated = $request->validate([
            'nama_vendor'   => 'required|string|max:150',
            'keahlian'      => 'nullable|string|max:120',
            'kontak_person' => 'nullable|string|max:120',
            'telepon'       => 'nullable|string|max:30',
            'email'         => 'nullable|email|max:150',
            'alamat'        => 'required|string',
            'kota'          => 'nullable|string|max:100',
            'provinsi'      => 'nullable|string|max:100',
            'latitude'      => 'nullable|string|max:40',
            'longitude'     => 'nullable|string|max:40',
        ]);

        $vendor->update($validated);

        return redirect()
            ->route('pengadaan.vendors')
            ->with('success', 'Data vendor berhasil diperbarui.');
    }

    /**
     * HAPUS VENDOR
     */
    public function destroyVendor(Vendor $vendor)
    {
        $nama = $vendor->nama_vendor;
        $vendor->delete();

        return redirect()
            ->route('pengadaan.vendors')
            ->with('success', "Vendor {$nama} berhasil dihapus.");
    }

    /**
     * 4. LAPORAN / RIWAYAT BELANJA (Figma 918:34465, 918:36198, 918:35447)
     */
    public function reports(Request $request)
    {
        $search    = $request->query('q');
        $startDate = $request->query('start_date');
        $endDate   = $request->query('end_date');

        $purchases = Purchase::with(['vendor', 'items.sparepart', 'user'])
            ->when($search, function ($q, $s) {
                $q->where(function ($sub) use ($s) {
                    $sub->where('kode_pembelian', 'like', "%{$s}%")
                        ->orWhere('vendor_name', 'like', "%{$s}%")
                        ->orWhereHas('items', fn ($iq) => $iq->where('nama_sparepart', 'like', "%{$s}%")->orWhere('kode_barang', 'like', "%{$s}%"));
                });
            })
            ->when($startDate, fn ($q) => $q->whereDate('tanggal_pembelian', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->whereDate('tanggal_pembelian', '<=', $endDate))
            ->latest('tanggal_pembelian')
            ->latest('id')
            ->get();

        // 3 Summary KPI Cards di bawah tabel (Figma 918:34465)
        $totalItemsDibeli   = PurchaseItem::sum('qty');
        $pengeluaranBulanIni = Purchase::whereYear('tanggal_pembelian', now()->year)
            ->whereMonth('tanggal_pembelian', now()->month)
            ->sum('total_harga');
        if ($pengeluaranBulanIni == 0) {
            $pengeluaranBulanIni = Purchase::sum('total_harga');
        }
        $vendorAktifCount = Vendor::where('is_active', true)->count();

        return view('pengadaan.reports', compact(
            'purchases',
            'totalItemsDibeli',
            'pengeluaranBulanIni',
            'vendorAktifCount',
            'search',
            'startDate',
            'endDate'
        ));
    }

    /**
     * 4b. FORM DETAIL TRANSAKSI BARU / ADD HISTORY (Figma 1262:163 & 1262:779)
     */
    public function createReport(Request $request)
    {
        $vendors    = Vendor::where('is_active', true)->orderBy('nama_vendor')->get();
        $spareparts = Sparepart::orderBy('nama_sparepart')->get();

        // Jika datang dari permintaan tertentu (?request_id=...)
        $prefillRequest = null;
        if ($request->filled('request_id')) {
            $prefillRequest = SparepartRequest::with('sparepart')->find($request->query('request_id'));
        }

        return view('pengadaan.reports-create', compact(
            'vendors',
            'spareparts',
            'prefillRequest'
        ));
    }

    /**
     * SIMPAN TRANSAKSI PEMBELIAN BARU & UPLOAD BUKTI TRANSFER
     */
    public function storeReport(Request $request)
    {
        $validated = $request->validate([
            'vendor_id'              => 'nullable|exists:vendors,id',
            'vendor_name'            => 'nullable|string|max:150',
            'sparepart_request_id'   => 'nullable|exists:sparepart_requests,id',
            'total_harga'            => 'required|numeric|min:0',
            'tanggal_pembelian'      => 'required|date',
            'waktu_pembelian'        => 'nullable|string|max:20',
            'bukti_pembayaran'       => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:5120',
            'catatan'                => 'nullable|string',
            'items'                  => 'required|array|min:1',
            'items.*.nama_sparepart' => 'required|string|max:150',
            'items.*.kode_barang'    => 'nullable|string|max:60',
            'items.*.qty'            => 'required|integer|min:1',
        ]);

        DB::transaction(function () use ($validated, $request) {
            $vendor = ! empty($validated['vendor_id']) ? Vendor::find($validated['vendor_id']) : null;
            $vendorName = $vendor?->nama_vendor
                ?: ($validated['vendor_name'] ?: 'Mitra Otomotif Sejahtera');

            $buktiPath = null;
            if ($request->hasFile('bukti_pembayaran')) {
                $buktiPath = $request->file('bukti_pembayaran')->store('purchases', 'public');
            }

            $nextNum = (Purchase::max('id') ?? 0) + 88289;
            $kode    = '#PR-' . $nextNum;
            while (Purchase::where('kode_pembelian', $kode)->exists()) {
                $nextNum++;
                $kode = '#PR-' . $nextNum;
            }

            $purchase = Purchase::create([
                'kode_pembelian'       => $kode,
                'user_id'              => auth()->id(),
                'vendor_id'            => $vendor?->id,
                'vendor_name'          => $vendorName,
                'sparepart_request_id' => $validated['sparepart_request_id'] ?? null,
                'tanggal_pembelian'    => $validated['tanggal_pembelian'],
                'waktu_pembelian'      => $validated['waktu_pembelian'] ?: now()->format('H:i'),
                'total_harga'          => $validated['total_harga'],
                'bukti_pembayaran'     => $buktiPath,
                'catatan'              => $validated['catatan'] ?? null,
                'status_pembelian'     => 'selesai',
            ]);

            $totalQty = collect($validated['items'])->sum('qty');
            $unitPriceEstimate = $totalQty > 0 ? round($validated['total_harga'] / $totalQty, 2) : 0;

            foreach ($validated['items'] as $row) {
                $sp = Sparepart::where('nama_sparepart', $row['nama_sparepart'])
                    ->orWhere(function ($q) use ($row) {
                        if (! empty($row['kode_barang'])) {
                            $q->where('kode_sparepart', $row['kode_barang']);
                        }
                    })
                    ->first();

                $sku = $row['kode_barang'] ?: ($sp?->kode_sparepart ?: 'SKU-' . strtoupper(substr(md5($row['nama_sparepart']), 0, 6)));

                PurchaseItem::create([
                    'purchase_id'    => $purchase->id,
                    'sparepart_id'   => $sp?->id,
                    'nama_sparepart' => $row['nama_sparepart'],
                    'kode_barang'    => $sku,
                    'qty'            => (int) $row['qty'],
                    'harga_satuan'   => $unitPriceEstimate,
                ]);

                // Tambah stok otomatis ke katalog spareparts jika cocok
                if ($sp) {
                    $sp->increment('stok', (int) $row['qty']);
                }
            }

            // Jika transaksi ini berasal dari permintaan yang masih pending, otomatis setujui
            if (! empty($validated['sparepart_request_id'])) {
                $req = SparepartRequest::find($validated['sparepart_request_id']);
                if ($req && $req->isPending()) {
                    $req->update([
                        'status'      => SparepartRequest::STATUS_APPROVED,
                        'reviewed_by' => auth()->id(),
                        'reviewed_at' => now(),
                    ]);
                }
            }
        });

        return redirect()
            ->route('pengadaan.reports')
            ->with('success', 'Detail transaksi pembelian berhasil disimpan dan stok suku cadang telah diperbarui.');
    }

    /**
     * HAPUS RIWAYAT PEMBELIAN (Modal Hapus Figma 918:36198)
     */
    public function destroyReport(Purchase $purchase)
    {
        if ($purchase->bukti_pembayaran && Storage::disk('public')->exists($purchase->bukti_pembayaran)) {
            Storage::disk('public')->delete($purchase->bukti_pembayaran);
        }

        $kode = $purchase->kode_pembelian;
        $purchase->items()->delete();
        $purchase->delete();

        return redirect()
            ->route('pengadaan.reports')
            ->with('success', "Riwayat transaksi {$kode} berhasil dihapus.");
    }

    /**
     * EXPORT PDF RIWAYAT BELANJA (Tombol DOWNLOAD PDF di Laporan)
     */
    public function exportReportsPdf(Request $request)
    {
        $search    = $request->query('q');
        $startDate = $request->query('start_date');
        $endDate   = $request->query('end_date');

        $purchases = Purchase::with(['vendor', 'items.sparepart', 'user'])
            ->when($search, function ($q, $s) {
                $q->where(function ($sub) use ($s) {
                    $sub->where('kode_pembelian', 'like', "%{$s}%")
                        ->orWhere('vendor_name', 'like', "%{$s}%")
                        ->orWhereHas('items', fn ($iq) => $iq->where('nama_sparepart', 'like', "%{$s}%"));
                });
            })
            ->when($startDate, fn ($q) => $q->whereDate('tanggal_pembelian', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->whereDate('tanggal_pembelian', '<=', $endDate))
            ->latest('tanggal_pembelian')
            ->latest('id')
            ->get();

        $totalNominal = $purchases->sum('total_harga');

        return view('pengadaan.reports-pdf', compact('purchases', 'totalNominal', 'startDate', 'endDate', 'search'));
    }

    /**
     * 5. PERSETUJUAN — Tabel Riwayat Permintaan Disetujui (Figma 1113:2312 & 1265:4783)
     */
    public function approvals(Request $request)
    {
        $search    = $request->query('q');
        $startDate = $request->query('start_date');
        $endDate   = $request->query('end_date');

        $approvedRequests = SparepartRequest::with(['sparepart', 'requester', 'reviewer'])
            ->where('status', SparepartRequest::STATUS_APPROVED)
            ->when($search, function ($q, $s) {
                $q->where(function ($sub) use ($s) {
                    $sub->where('nama_sparepart', 'like', "%{$s}%")
                        ->orWhere('vendor_name', 'like', "%{$s}%")
                        ->orWhere('alasan', 'like', "%{$s}%");
                });
            })
            ->when($startDate, fn ($q) => $q->whereDate('created_at', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->whereDate('created_at', '<=', $endDate))
            ->latest('reviewed_at')
            ->latest('created_at')
            ->get();

        $totalApproved = SparepartRequest::where('status', SparepartRequest::STATUS_APPROVED)->count();

        return view('pengadaan.approvals', compact(
            'approvedRequests',
            'totalApproved',
            'search',
            'startDate',
            'endDate'
        ));
    }

    /**
     * EXPORT PDF PERSETUJUAN (Tombol DOWNLOAD PDF di Persetujuan)
     */
    public function exportApprovalsPdf(Request $request)
    {
        $search    = $request->query('q');
        $startDate = $request->query('start_date');
        $endDate   = $request->query('end_date');

        $approvedRequests = SparepartRequest::with(['sparepart', 'requester', 'reviewer'])
            ->where('status', SparepartRequest::STATUS_APPROVED)
            ->when($search, function ($q, $s) {
                $q->where(function ($sub) use ($s) {
                    $sub->where('nama_sparepart', 'like', "%{$s}%")
                        ->orWhere('vendor_name', 'like', "%{$s}%");
                });
            })
            ->when($startDate, fn ($q) => $q->whereDate('created_at', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->whereDate('created_at', '<=', $endDate))
            ->latest('reviewed_at')
            ->latest('created_at')
            ->get();

        return view('pengadaan.approvals-pdf', compact('approvedRequests', 'startDate', 'endDate', 'search'));
    }
}

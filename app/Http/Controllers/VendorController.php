<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use Illuminate\Http\Request;

/**
 * Direktori vendor / supplier suku cadang.
 */
class VendorController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('q');
        $status = $request->query('status');

        $vendors = Vendor::query()
            ->when($search, function ($q, $v) {
                $q->where(function ($w) use ($v) {
                    $w->where('nama_vendor', 'like', "%{$v}%")
                      ->orWhere('kontak_person', 'like', "%{$v}%")
                      ->orWhere('keahlian', 'like', "%{$v}%");
                });
            })
            ->when($status !== null && $status !== '', fn ($q) => $q->where('is_active', $status === 'aktif'))
            ->orderBy('nama_vendor')
            ->paginate(auth()->user()->perPage())
            ->withQueryString();

        $stats = [
            'total'    => Vendor::count(),
            'aktif'    => Vendor::where('is_active', true)->count(),
            'nonaktif' => Vendor::where('is_active', false)->count(),
        ];

        return view('vendors.index', compact('vendors', 'stats', 'search', 'status'));
    }

    public function store(Request $request)
    {
        Vendor::create($this->validated($request));

        return back()->with('success', 'Vendor berhasil ditambahkan.');
    }

    public function update(Request $request, Vendor $vendor)
    {
        $vendor->update($this->validated($request));

        return back()->with('success', 'Data vendor berhasil diperbarui.');
    }

    public function destroy(Vendor $vendor)
    {
        $vendor->delete();

        return back()->with('success', 'Vendor berhasil dihapus.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'nama_vendor'   => 'required|string|max:150',
            'kontak_person' => 'nullable|string|max:100',
            'telepon'       => 'nullable|string|max:30',
            'email'         => 'nullable|email|max:150',
            'alamat'        => 'nullable|string',
            'keahlian'      => 'nullable|string|max:150',
            'is_active'     => 'required|in:0,1',
        ]);

        $data['is_active'] = (bool) $data['is_active'];

        return $data;
    }
}

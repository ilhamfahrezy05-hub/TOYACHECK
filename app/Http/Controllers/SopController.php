<?php

namespace App\Http\Controllers;

use App\Models\Sop;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SopController extends Controller
{
    /**
     * TAMPILKAN HALAMAN STANDAR OPERASIONAL PROSEDUR (ADMIN ONLY)
     */
    public function index(Request $request)
    {
        $roleFilter = $request->query('role');
        $search     = trim((string) $request->query('q', ''));

        $roles = Sop::ROLES;
        $roleMeta = Sop::ROLE_META;

        // Hitung jumlah SOP tiap role (tanpa filter) untuk badge tab
        $countsByRole = Sop::selectRaw('role_target, COUNT(*) as total')
            ->groupBy('role_target')
            ->pluck('total', 'role_target');

        $totalAll = $countsByRole->sum();

        $sopsQuery = Sop::ordered()
            ->when($roleFilter && array_key_exists($roleFilter, $roles), fn ($q) => $q->where('role_target', $roleFilter))
            ->when($search !== '', fn ($q) => $q->where('isi_prosedur', 'like', "%{$search}%"));

        $groupedSops = $sopsQuery->get()->groupBy('role_target');

        // Tentukan urutan role yang akan dirender pada halaman
        $visibleRoles = ($roleFilter && array_key_exists($roleFilter, $roles))
            ? [$roleFilter => $roles[$roleFilter]]
            : $roles;

        return view('sop.index', compact(
            'roles',
            'roleMeta',
            'visibleRoles',
            'groupedSops',
            'countsByRole',
            'totalAll',
            'roleFilter',
            'search'
        ));
    }

    /**
     * SIMPAN BUTIR SOP BARU
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'role_target'  => ['required', Rule::in(array_keys(Sop::ROLES))],
            'urutan'       => 'nullable|integer|min:1|max:999',
            'isi_prosedur' => 'required|string|max:2000',
        ], [
            'role_target.required'  => 'Role wajib dipilih.',
            'role_target.in'        => 'Role tidak valid.',
            'isi_prosedur.required' => 'Isi prosedur SOP tidak boleh kosong.',
        ]);

        if (empty($validated['urutan'])) {
            $maxUrutan = Sop::where('role_target', $validated['role_target'])->max('urutan') ?? 0;
            $validated['urutan'] = $maxUrutan + 1;
        }

        Sop::create($validated);

        return back()->with('success', 'Butir SOP berhasil ditambahkan.');
    }

    /**
     * PERBARUI BUTIR SOP
     */
    public function update(Request $request, Sop $sop)
    {
        $validated = $request->validate([
            'role_target'  => ['required', Rule::in(array_keys(Sop::ROLES))],
            'urutan'       => 'required|integer|min:1|max:999',
            'isi_prosedur' => 'required|string|max:2000',
        ], [
            'role_target.required'  => 'Role wajib dipilih.',
            'role_target.in'        => 'Role tidak valid.',
            'urutan.required'       => 'Nomor urutan wajib diisi.',
            'isi_prosedur.required' => 'Isi prosedur SOP tidak boleh kosong.',
        ]);

        $sop->update($validated);

        return back()->with('success', 'Butir SOP berhasil diperbarui.');
    }

    /**
     * HAPUS BUTIR SOP
     */
    public function destroy(Sop $sop)
    {
        $sop->delete();

        return back()->with('success', 'Butir SOP berhasil dihapus.');
    }

    /**
     * HALAMAN PREVIEW & CETAK PDF STANDAR OPERASIONAL PROSEDUR
     */
    public function exportPdf(Request $request)
    {
        $roleFilter = $request->query('role');
        $roles = Sop::ROLES;

        $sops = Sop::ordered()
            ->when($roleFilter && array_key_exists($roleFilter, $roles), fn ($q) => $q->where('role_target', $roleFilter))
            ->get()
            ->groupBy('role_target');

        $visibleRoles = ($roleFilter && array_key_exists($roleFilter, $roles))
            ? [$roleFilter => $roles[$roleFilter]]
            : $roles;

        return view('sop.pdf', compact('visibleRoles', 'sops', 'roleFilter'));
    }
}

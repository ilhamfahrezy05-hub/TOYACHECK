<?php

namespace App\Http\Controllers;

use App\Models\InspectionItem;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;


/**
 * Controller Manajemen Parameter & SOP Checklist Inspeksi Armada (SRS: Master Item Pemeriksaan).
 * Mengatur hierarki parameter inspeksi (Grup & Item Titik Periksa) untuk kategori Fisik dan K3.
 */
class InspectionItemController extends Controller
{
    /**
     * TAMPILKAN DAFTAR ITEM PEMERIKSAAN (PER KATEGORI)
     */
    public function index(Request $request)
    {
        $category = $request->query('category', 'fisik');
        if (!in_array($category, ['fisik', 'k3'], true)) {
            $category = 'fisik';
        }

        // Ambil grup beserta relasi children dan user audit editor
        $groups = InspectionItem::groups()
            ->where('category', $category)
            ->with(['children.editor'])
            ->withCount('children')
            ->orderBy('urutan')
            ->orderBy('id')
            ->get();

        $stats = [
            'grup'     => InspectionItem::groups()->where('category', $category)->count(),
            'item'     => InspectionItem::whereNotNull('parent_id')->where('category', $category)->count(),
            'aktif'    => InspectionItem::whereNotNull('parent_id')->where('category', $category)->where('is_active', true)->count(),
            'nonaktif' => InspectionItem::whereNotNull('parent_id')->where('category', $category)->where('is_active', false)->count(),
        ];

        return view('inspection-items.index', compact('groups', 'stats', 'category'));
    }

    /**
     * SIMPAN GRUP / ITEM PEMERIKSAAN BARU
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category'  => ['required', Rule::in(['fisik', 'k3'])],
            'parent_id' => 'nullable|exists:inspection_items,id',
            'nama'      => 'required|string|max:150',
            'criteria'  => 'nullable|string',
            'urutan'    => 'nullable|integer|min:0',
        ]);

        // Tentukan urutan otomatis jika tidak diisi
        $urutan = $validated['urutan'] ?? null;
        if ($urutan === null) {
            $maxUrutan = InspectionItem::where('category', $validated['category'])
                ->where('parent_id', $validated['parent_id'] ?? null)
                ->max('urutan');
            $urutan = ($maxUrutan ?? 0) + 1;
        }

        InspectionItem::create([
            'category'  => $validated['category'],
            'parent_id' => $validated['parent_id'] ?? null,
            'nama'      => $validated['nama'],
            'criteria'  => $validated['criteria'] ?? null,
            'urutan'    => $urutan,
            'is_active' => true,
            'user_edit' => Auth::id(),
        ]);

        return back()->with('success', 'Parameter pemeriksaan berhasil ditambahkan.');
    }

    /**
     * UPDATE NAMA / KRITERIA / STATUS ITEM PEMERIKSAAN
     */
    public function update(Request $request, $id)
    {
        $item = InspectionItem::findOrFail($id);

        $validated = $request->validate([
            'nama'      => 'required|string|max:150',
            'criteria'  => 'nullable|string',
            'urutan'    => 'required|integer|min:0',
            'is_active' => 'required|in:0,1',
        ]);

        $item->update([
            'nama'      => $validated['nama'],
            'criteria'  => $item->isGroup() ? null : ($validated['criteria'] ?? null),
            'urutan'    => (int) $validated['urutan'],
            'is_active' => (bool) $validated['is_active'],
            'user_edit' => Auth::id(),
        ]);

        return back()->with('success', 'Data parameter pemeriksaan berhasil diperbarui.');
    }

    /**
     * TOGGLE STATUS AKTIF / NONAKTIF
     */
    public function toggle($id)
    {
        $item = InspectionItem::findOrFail($id);

        $item->update([
            'is_active' => !$item->is_active,
            'user_edit' => Auth::id(),
        ]);

        $statusStr = $item->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "Item {$item->nama} berhasil {$statusStr}.");
    }

    /**
     * HAPUS PARAMETER PEMERIKSAAN
     */
    public function destroy($id)
    {
        $item = InspectionItem::findOrFail($id);

        // Jika grup dan masih memiliki sub-item
        if ($item->isGroup() && $item->children()->count() > 0) {
            return back()->with('error', 'Grup ini tidak dapat dihapus karena masih memiliki titik pemeriksaan di dalamnya.');
        }

        // Jika sudah pernah dipakai pada riwayat inspeksi
        if ($item->checklistItems()->exists()) {
            return back()->with('error', 'Parameter ini telah memiliki riwayat checklist dan tidak boleh dihapus. Anda dapat menonaktifkannya.');
        }

        $item->delete();

        return back()->with('success', 'Parameter pemeriksaan berhasil dihapus.');
    }
}

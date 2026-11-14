@extends('layouts.app')

@section('title', 'Item Pemeriksaan')
@section('page-title', 'Item Pemeriksaan')
@section('page-subtitle', 'Kelola parameter & SOP checklist inspeksi armada')

{{-- ===================== PAGE ACTIONS ===================== --}}
@section('page-actions')
    <div class="d-flex gap-8">
        <button class="btn btn-secondary" onclick="openModal('modalCreateGroup')">
            <i class="fa-solid fa-folder-plus"></i>
            Tambah Grup
        </button>
        <button class="btn btn-primary" onclick="openModal('modalCreateItem')">
            <i class="fa-solid fa-plus"></i>
            Tambah Item
        </button>
    </div>
@endsection

{{-- ===================== STYLES ===================== --}}
@push('styles')
<style>
    .category-tabs {
        display: flex;
        gap: 8px;
        margin-bottom: 20px;
        border-bottom: 2px solid var(--border);
        padding-bottom: 2px;
    }
    .cat-tab {
        padding: 10px 18px;
        font-size: 13px;
        font-weight: 600;
        color: var(--text-secondary);
        border-bottom: 3px solid transparent;
        margin-bottom: -4px;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all .15s;
    }
    .cat-tab:hover {
        color: var(--primary);
    }
    .cat-tab.active {
        color: var(--primary);
        border-bottom-color: var(--primary);
    }
    .group-card {
        border: 1px solid var(--border);
        border-radius: var(--radius-md, 10px);
        margin-bottom: 18px;
        background: #fff;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0,0,0,0.02);
    }
    .group-header {
        padding: 14px 20px;
        background: #F8FAFC;
        border-bottom: 1px solid var(--border);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }
    .group-title {
        font-size: 14px;
        font-weight: 700;
        color: var(--text-primary);
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .item-table {
        width: 100%;
        border-collapse: collapse;
    }
    .item-table th {
        padding: 10px 20px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        color: var(--text-muted);
        background: #FAFAFA;
        border-bottom: 1px solid var(--border);
    }
    .item-table td {
        padding: 12px 20px;
        font-size: 13px;
        border-bottom: 1px solid #F1F5F9;
        vertical-align: middle;
    }
    .item-table tr:last-child td {
        border-bottom: none;
    }
    .item-table tr.row-inactive td {
        opacity: 0.6;
        background: #FAFAFA;
    }
</style>
@endpush

{{-- ===================== CONTENT ===================== --}}
@section('content')

{{-- ── TAB PILIHAN KATEGORI (FISIK / K3) ── --}}
<div class="category-tabs">
    <a href="{{ route('inspection-items.index', ['category' => 'fisik']) }}"
       class="cat-tab {{ $category === 'fisik' ? 'active' : '' }}">
        <i class="fa-solid fa-truck-ramp-box"></i>
        Parameter Fisik Kendaraan (Driver)
    </a>
    <a href="{{ route('inspection-items.index', ['category' => 'k3']) }}"
       class="cat-tab {{ $category === 'k3' ? 'active' : '' }}">
        <i class="fa-solid fa-shield-halved"></i>
        Parameter K3 & Kebersihan (HSE)
    </a>
</div>

{{-- ── STAT CARDS ── --}}
<div class="stats-grid" style="grid-template-columns: repeat(4, 1fr); margin-bottom: 24px;">

    <div class="stat-card">
        <div class="stat-icon blue"><i class="fa-solid fa-folder"></i></div>
        <div class="stat-info">
            <div class="stat-label">Total Grup</div>
            <div class="stat-value">{{ $stats['grup'] }}</div>
            <div class="stat-change">Kategori komponen</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon violet"><i class="fa-solid fa-list-check"></i></div>
        <div class="stat-info">
            <div class="stat-label">Total Parameter</div>
            <div class="stat-value">{{ $stats['item'] }}</div>
            <div class="stat-change">Titik pemeriksaan</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon green"><i class="fa-solid fa-circle-check"></i></div>
        <div class="stat-info">
            <div class="stat-label">Aktif</div>
            <div class="stat-value">{{ $stats['aktif'] }}</div>
            <div class="stat-change">Muncul di form checklist</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon red"><i class="fa-solid fa-circle-pause"></i></div>
        <div class="stat-info">
            <div class="stat-label">Nonaktif</div>
            <div class="stat-value">{{ $stats['nonaktif'] }}</div>
            <div class="stat-change">Disembunyikan (riwayat aman)</div>
        </div>
    </div>

</div>

{{-- ── DAFTAR GRUP & ITEM ── --}}
@forelse($groups as $group)
<div class="group-card">
    <div class="group-header">
        <div class="group-title">
            <div style="width:30px;height:30px;border-radius:6px;background:var(--primary-subtle);color:var(--primary);display:flex;align-items:center;justify-content:center;font-size:13px">
                <i class="fa-solid fa-folder-open"></i>
            </div>
            <span>{{ $group->nama }}</span>
            <span class="badge badge-neutral" style="font-size:11px;font-weight:600">
                {{ $group->children_count }} item
            </span>
            @if(!$group->is_active)
                <span class="badge badge-danger" style="font-size:10px">Grup Nonaktif</span>
            @endif
        </div>

        <div class="d-flex gap-8 align-center">
            {{-- Tambah Item ke Grup Ini --}}
            <button class="btn btn-secondary btn-sm"
                    onclick="openCreateItemForGroup({{ $group->id }}, '{{ addslashes($group->nama) }}')"
                    title="Tambah Item ke Grup ini">
                <i class="fa-solid fa-plus"></i> Item
            </button>

            {{-- Edit Grup --}}
            <button class="btn btn-warning btn-sm btn-icon"
                    onclick="openEditModal({{ $group->id }}, '', '{{ addslashes($group->nama) }}', '', {{ $group->urutan }}, {{ $group->is_active ? 1 : 0 }}, true)"
                    title="Edit Nama Grup">
                <i class="fa-solid fa-pen"></i>
            </button>

            {{-- Toggle Aktif Grup --}}
            <form action="{{ route('inspection-items.toggle', $group->id) }}" method="POST" style="display:inline">
                @csrf
                @method('PATCH')
                <button type="submit" class="btn btn-sm btn-icon {{ $group->is_active ? 'btn-secondary' : 'btn-primary' }}"
                        title="{{ $group->is_active ? 'Nonaktifkan Grup' : 'Aktifkan Grup' }}">
                    <i class="fa-solid {{ $group->is_active ? 'fa-eye-slash' : 'fa-eye' }}"></i>
                </button>
            </form>
        </div>
    </div>

    <table class="item-table">
        <thead>
            <tr>
                <th style="width:48px">Urut</th>
                <th style="width:200px">Nama Item</th>
                <th>Tolok Ukur / SOP Kriteria Pemeriksaan</th>
                <th style="width:110px">Status</th>
                <th style="width:130px">Audit Editor</th>
                <th style="width:100px;text-align:center">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($group->children as $item)
            <tr class="{{ $item->is_active ? '' : 'row-inactive' }}">
                <td style="color:var(--text-muted);font-weight:600;font-size:12px">
                    #{{ $item->urutan }}
                </td>

                <td style="font-weight:600;color:var(--text-primary)">
                    {{ $item->nama }}
                </td>

                <td style="color:var(--text-secondary);font-size:12px;line-height:1.5">
                    {{ $item->criteria ?: '— Belum ada kriteria khusus —' }}
                </td>

                <td>
                    @if($item->is_active)
                        <span class="badge badge-success"><i class="fa-solid fa-circle-check"></i> Aktif</span>
                    @else
                        <span class="badge badge-neutral"><i class="fa-solid fa-circle-xmark"></i> Nonaktif</span>
                    @endif
                </td>

                <td style="font-size:11px;color:var(--text-muted)">
                    @if($item->editor)
                        <i class="fa-regular fa-user" style="margin-right:2px"></i> {{ $item->editor->name }}
                    @else
                        <span style="opacity:0.6">Sistem</span>
                    @endif
                </td>

                <td>
                    <div class="d-flex gap-8 align-center" style="justify-content:center">
                        {{-- Edit Item --}}
                        <button class="btn btn-warning btn-sm btn-icon"
                                title="Edit Item"
                                onclick="openEditModal({{ $item->id }}, {{ $group->id }}, '{{ addslashes($item->nama) }}', '{{ addslashes($item->criteria ?? '') }}', {{ $item->urutan }}, {{ $item->is_active ? 1 : 0 }}, false)">
                            <i class="fa-solid fa-pen"></i>
                        </button>

                        {{-- Toggle Status --}}
                        <form action="{{ route('inspection-items.toggle', $item->id) }}" method="POST" style="display:inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-sm btn-icon {{ $item->is_active ? 'btn-secondary' : 'btn-primary' }}"
                                    title="{{ $item->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                <i class="fa-solid {{ $item->is_active ? 'fa-toggle-on' : 'fa-toggle-off' }}"></i>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align:center;padding:24px;color:var(--text-muted)">
                    Belum ada parameter di dalam grup ini. Klik "<strong>+ Item</strong>" di kanan atas grup untuk menambahkan.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@empty
<div class="card">
    <div class="card-body">
        <div class="empty-state">
            <div class="empty-icon"><i class="fa-solid fa-folder-open"></i></div>
            <div class="empty-title">Belum Ada Grup Parameter</div>
            <div class="empty-text">Buat grup pertama dengan klik tombol "Tambah Grup" di atas.</div>
        </div>
    </div>
</div>
@endforelse

@endsection

{{-- ===================== MODALS ===================== --}}
@section('modals')

{{-- ===== MODAL TAMBAH GRUP ===== --}}
<div class="modal-overlay" id="modalCreateGroup">
    <div class="modal-box" style="max-width:480px">
        <div class="modal-header">
            <div class="modal-title">
                <i class="fa-solid fa-folder-plus" style="color:var(--primary);margin-right:8px"></i>
                Tambah Grup Parameter
            </div>
            <button class="modal-close" onclick="closeModal('modalCreateGroup')" type="button">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form action="{{ route('inspection-items.store') }}" method="POST">
            @csrf
            <input type="hidden" name="category" value="{{ $category }}">

            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label" for="cg_nama">
                        Nama Grup <span class="required">*</span>
                    </label>
                    <input type="text" id="cg_nama" name="nama" class="form-control"
                           placeholder="Contoh: Ban & Rem, Mesin, dsb." required>
                </div>

                <div class="form-group" style="margin-bottom:0">
                    <label class="form-label" for="cg_urutan">Nomor Urutan Tampil</label>
                    <input type="number" id="cg_urutan" name="urutan" class="form-control"
                           value="{{ $groups->count() + 1 }}" min="1">
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalCreateGroup')">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Simpan Grup</button>
            </div>
        </form>
    </div>
</div>

{{-- ===== MODAL TAMBAH ITEM ===== --}}
<div class="modal-overlay" id="modalCreateItem">
    <div class="modal-box" style="max-width:540px">
        <div class="modal-header">
            <div class="modal-title">
                <i class="fa-solid fa-plus" style="color:var(--primary);margin-right:8px"></i>
                Tambah Parameter Pemeriksaan
            </div>
            <button class="modal-close" onclick="closeModal('modalCreateItem')" type="button">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form action="{{ route('inspection-items.store') }}" method="POST">
            @csrf
            <input type="hidden" name="category" value="{{ $category }}">

            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label" for="ci_parent_id">
                        Pilih Grup <span class="required">*</span>
                    </label>
                    <select id="ci_parent_id" name="parent_id" class="form-control" required>
                        <option value="">— Pilih Grup Induk —</option>
                        @foreach($groups as $g)
                            <option value="{{ $g->id }}">{{ $g->nama }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="ci_nama">
                        Nama Parameter <span class="required">*</span>
                    </label>
                    <input type="text" id="ci_nama" name="nama" class="form-control"
                           placeholder="Contoh: Ban Depan, Air Radiator, APAR" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="ci_criteria">Tolok Ukur / Standar SOP Kriteria</label>
                    <textarea id="ci_criteria" name="criteria" rows="2" class="form-control"
                              placeholder="Penjelasan kondisi baik / batas kelayakan..."></textarea>
                </div>

                <div class="form-group" style="margin-bottom:0">
                    <label class="form-label" for="ci_urutan">Nomor Urutan Tampil</label>
                    <input type="number" id="ci_urutan" name="urutan" class="form-control" min="1" placeholder="Opsional (otomatis di akhir)">
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalCreateItem')">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Simpan Parameter</button>
            </div>
        </form>
    </div>
</div>

{{-- ===== MODAL EDIT ITEM / GRUP ===== --}}
<div class="modal-overlay" id="modalEditItem">
    <div class="modal-box" style="max-width:540px">
        <div class="modal-header">
            <div class="modal-title">
                <i class="fa-solid fa-pen-to-square" style="color:var(--warning);margin-right:8px"></i>
                Edit Parameter / Grup
            </div>
            <button class="modal-close" onclick="closeModal('modalEditItem')" type="button">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form id="formEditItem" action="" method="POST">
            @csrf
            @method('PUT')

            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label" for="ei_nama">
                        Nama <span class="required">*</span>
                    </label>
                    <input type="text" id="ei_nama" name="nama" class="form-control" required>
                </div>

                <div class="form-group" id="ei_criteria_group">
                    <label class="form-label" for="ei_criteria">Tolok Ukur / Standar SOP Kriteria</label>
                    <textarea id="ei_criteria" name="criteria" rows="2" class="form-control"></textarea>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                    <div class="form-group" style="margin-bottom:0">
                        <label class="form-label" for="ei_urutan">Nomor Urut <span class="required">*</span></label>
                        <input type="number" id="ei_urutan" name="urutan" class="form-control" required min="0">
                    </div>

                    <div class="form-group" style="margin-bottom:0">
                        <label class="form-label" for="ei_is_active">Status <span class="required">*</span></label>
                        <select id="ei_is_active" name="is_active" class="form-control" required>
                            <option value="1">Aktif</option>
                            <option value="0">Nonaktif</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalEditItem')">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

@endsection

{{-- ===================== PAGE SCRIPTS ===================== --}}
@push('scripts')
<script>
(function () {
    'use strict';

    window.openCreateItemForGroup = function (groupId, groupName) {
        var sel = document.getElementById('ci_parent_id');
        if (sel) {
            sel.value = groupId;
        }
        window.openModal('modalCreateItem');
    };

    window.openEditModal = function (id, parentId, nama, criteria, urutan, isActive, isGroup) {
        var form = document.getElementById('formEditItem');
        form.action = '/inspection-items/' + id;

        document.getElementById('ei_nama').value      = nama;
        document.getElementById('ei_urutan').value    = urutan;
        document.getElementById('ei_is_active').value = isActive ? '1' : '0';

        var criteriaGroup = document.getElementById('ei_criteria_group');
        var criteriaInput = document.getElementById('ei_criteria');

        if (isGroup) {
            criteriaGroup.style.display = 'none';
            criteriaInput.value = '';
        } else {
            criteriaGroup.style.display = 'block';
            criteriaInput.value = criteria || '';
        }

        window.openModal('modalEditItem');
    };
})();
</script>
@endpush

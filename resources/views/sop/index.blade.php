@extends('layouts.app')

@section('title', 'Standar Operasional Prosedur (SOP)')
@section('page-title', 'Standar Operasional Prosedur (SOP)')
@section('page-subtitle', 'Panduan dan aturan operasional aplikasi TOYACHECK per peran pengguna')

{{-- ===================== PAGE ACTIONS ===================== --}}
@section('page-actions')
    <div class="d-flex gap-8 align-center">
        <a href="{{ route('sop.pdf', $roleFilter ? ['role' => $roleFilter] : []) }}"
           target="_blank"
           class="btn btn-secondary"
           id="btn-preview-sop-pdf">
            <i class="fa-solid fa-file-pdf"></i>
            Preview &amp; Cetak PDF
        </a>
        <button type="button" class="btn btn-primary" onclick="openCreateSop('{{ $roleFilter ?? 'driver' }}')">
            <i class="fa-solid fa-plus"></i>
            Tambah SOP
        </button>
    </div>
@endsection

{{-- ===================== STYLES ===================== --}}
@push('styles')
<style>
    .sop-role-tabs {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 18px;
        border-bottom: 2px solid var(--border);
        padding-bottom: 10px;
    }
    .sop-tab {
        padding: 8px 14px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        color: var(--text-secondary);
        background: #F8FAFC;
        border: 1px solid var(--border);
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.15s ease;
    }
    .sop-tab:hover {
        color: var(--primary);
        border-color: var(--primary);
        background: #EFF6FF;
    }
    .sop-tab.active {
        background: var(--primary);
        color: #FFFFFF;
        border-color: var(--primary);
    }
    .sop-tab-count {
        font-size: 11px;
        font-weight: 700;
        padding: 1px 7px;
        border-radius: 999px;
        background: rgba(15, 30, 54, 0.08);
    }
    .sop-tab.active .sop-tab-count {
        background: rgba(255, 255, 255, 0.22);
        color: #FFFFFF;
    }
    .sop-card {
        border: 1px solid var(--border);
        border-radius: var(--radius-md, 12px);
        margin-bottom: 20px;
        background: #FFFFFF;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
    }
    .sop-card-header {
        padding: 14px 20px;
        background: #F8FAFC;
        border-bottom: 1px solid var(--border);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
    }
    .sop-role-heading {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .sop-role-icon {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 15px;
        flex-shrink: 0;
    }
    .sop-role-title {
        font-size: 15px;
        font-weight: 700;
        color: var(--text-primary);
    }
    .sop-role-sub {
        font-size: 12px;
        color: var(--text-muted);
    }
    .sop-table {
        width: 100%;
        border-collapse: collapse;
    }
    .sop-table th {
        padding: 10px 20px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: var(--text-muted);
        background: #FAFAFA;
        border-bottom: 1px solid var(--border);
        text-align: left;
    }
    .sop-table td {
        padding: 13px 20px;
        font-size: 13.5px;
        line-height: 1.55;
        color: var(--text-primary);
        border-bottom: 1px solid #F1F5F9;
        vertical-align: top;
    }
    .sop-table tbody tr:last-child td {
        border-bottom: none;
    }
    .sop-table tbody tr:hover {
        background: #FAFBFD;
    }
    .sop-num-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 26px;
        height: 26px;
        border-radius: 7px;
        background: #F1F5F9;
        color: #334155;
        font-weight: 700;
        font-size: 12px;
    }
</style>
@endpush

{{-- ===================== CONTENT ===================== --}}
@section('content')

{{-- ── TAB FILTER PER ROLE + PENCARIAN ── --}}
<div class="d-flex justify-between align-center gap-12 flex-wrap" style="margin-bottom: 16px;">
    <div class="sop-role-tabs" style="margin-bottom: 0; border-bottom: none; padding-bottom: 0;">
        <a href="{{ route('sop.index', $search !== '' ? ['q' => $search] : []) }}"
           class="sop-tab {{ empty($roleFilter) ? 'active' : '' }}">
            <i class="fa-solid fa-layer-group"></i>
            Semua Role
            <span class="sop-tab-count">{{ $totalAll }}</span>
        </a>

        @foreach($roles as $roleKey => $roleLabel)
            @php $meta = $roleMeta[$roleKey] ?? ['icon' => 'fa-circle', 'color' => '#334155', 'bg' => '#F1F5F9']; @endphp
            <a href="{{ route('sop.index', array_filter(['role' => $roleKey, 'q' => $search ?: null])) }}"
               class="sop-tab {{ $roleFilter === $roleKey ? 'active' : '' }}">
                <i class="fa-solid {{ $meta['icon'] }}"></i>
                {{ $roleLabel }}
                <span class="sop-tab-count">{{ $countsByRole[$roleKey] ?? 0 }}</span>
            </a>
        @endforeach
    </div>

    <form method="GET" action="{{ route('sop.index') }}" class="d-flex gap-8 align-center">
        @if($roleFilter)
            <input type="hidden" name="role" value="{{ $roleFilter }}">
        @endif
        <div style="position: relative;">
            <i class="fa-solid fa-magnifying-glass"
               style="position:absolute;left:11px;top:50%;transform:translateY(-50%);color:var(--text-muted);font-size:12.5px;pointer-events:none"></i>
            <input type="text"
                   name="q"
                   value="{{ $search }}"
                   placeholder="Cari isi prosedur SOP..."
                   class="form-control"
                   style="padding-left:32px;width:240px;">
        </div>
        @if($search !== '')
            <a href="{{ route('sop.index', $roleFilter ? ['role' => $roleFilter] : []) }}" class="btn btn-secondary" title="Reset Pencarian">
                <i class="fa-solid fa-xmark"></i>
            </a>
        @endif
    </form>
</div>

{{-- ── DAFTAR SOP PER ROLE ── --}}
@php $hasAnyVisible = false; @endphp

@foreach($visibleRoles as $roleKey => $roleLabel)
    @php
        $items = $groupedSops->get($roleKey, collect());
        $meta  = $roleMeta[$roleKey] ?? ['icon' => 'fa-book', 'color' => '#0B2E8A', 'bg' => '#EFF6FF'];
        if ($items->isNotEmpty()) {
            $hasAnyVisible = true;
        }
    @endphp

    @if($search === '' || $items->isNotEmpty())
    <div class="sop-card">
        <div class="sop-card-header">
            <div class="sop-role-heading">
                <div class="sop-role-icon" style="background:{{ $meta['bg'] }};color:{{ $meta['color'] }};">
                    <i class="fa-solid {{ $meta['icon'] }}"></i>
                </div>
                <div>
                    <div class="sop-role-title">{{ $roleLabel }}</div>
                    <div class="sop-role-sub">{{ $items->count() }} butir standar operasional prosedur</div>
                </div>
            </div>

            <button type="button"
                    class="btn btn-secondary btn-sm"
                    onclick="openCreateSop('{{ $roleKey }}')">
                <i class="fa-solid fa-plus"></i>
                Tambah Poin {{ $roleLabel }}
            </button>
        </div>

        @if($items->isNotEmpty())
        <div class="table-wrap">
            <table class="sop-table">
                <thead>
                    <tr>
                        <th style="width:68px;text-align:center;">No</th>
                        <th>Isi Prosedur</th>
                        <th style="width:110px;text-align:center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $sop)
                    <tr>
                        <td style="text-align:center;">
                            <span class="sop-num-badge">{{ $sop->urutan }}</span>
                        </td>
                        <td>{{ $sop->isi_prosedur }}</td>
                        <td style="text-align:center;">
                            <div class="d-flex gap-6 align-center" style="justify-content:center;">
                                <button type="button"
                                        class="btn btn-warning btn-sm btn-icon"
                                        title="Edit SOP"
                                        data-id="{{ $sop->id }}"
                                        data-role="{{ $sop->role_target }}"
                                        data-urutan="{{ $sop->urutan }}"
                                        data-isi="{{ $sop->isi_prosedur }}"
                                        onclick="openEditSop(this)">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                <button type="button"
                                        class="btn btn-danger btn-sm btn-icon"
                                        title="Hapus SOP"
                                        data-id="{{ $sop->id }}"
                                        data-role="{{ $roleLabel }}"
                                        data-isi="{{ \Illuminate\Support\Str::limit($sop->isi_prosedur, 90) }}"
                                        onclick="openDeleteSop(this)">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="empty-state" style="padding:28px 20px;">
            <div class="empty-icon"><i class="fa-solid fa-book-open"></i></div>
            <div class="empty-title">Belum Ada Prosedur untuk {{ $roleLabel }}</div>
            <div class="empty-text">Klik tombol "Tambah Poin {{ $roleLabel }}" di kanan atas untuk menambahkan.</div>
        </div>
        @endif
    </div>
    @endif
@endforeach

@if($search !== '' && !$hasAnyVisible)
    <div class="card">
        <div class="empty-state" style="padding:40px 20px;">
            <div class="empty-icon"><i class="fa-solid fa-magnifying-glass"></i></div>
            <div class="empty-title">Prosedur Tidak Ditemukan</div>
            <div class="empty-text">Tidak ada butir SOP yang cocok dengan kata kunci "{{ $search }}".</div>
        </div>
    </div>
@endif


{{-- ===================== MODAL: TAMBAH SOP ===================== --}}
<div class="modal-overlay" id="modalCreateSop">
    <div class="modal-box" style="max-width:560px">
        <div class="modal-header">
            <div class="modal-title">
                <i class="fa-solid fa-plus-circle" style="color:var(--primary);margin-right:8px"></i>
                Tambah Butir SOP Baru
            </div>
            <button type="button" class="modal-close" onclick="closeModal('modalCreateSop')">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form action="{{ route('sop.store') }}" method="POST">
            @csrf
            <div class="modal-body">
                <div style="display:grid;grid-template-columns:1.5fr 1fr;gap:14px;">
                    <div class="form-group">
                        <label class="form-label" for="cs_role_target">
                            Role / Bagian <span class="required">*</span>
                        </label>
                        <select id="cs_role_target" name="role_target" class="form-control" required>
                            @foreach($roles as $roleKey => $roleLabel)
                                <option value="{{ $roleKey }}" {{ old('role_target') === $roleKey ? 'selected' : '' }}>
                                    {{ $roleLabel }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="cs_urutan">Nomor Urut</label>
                        <input type="number"
                               id="cs_urutan"
                               name="urutan"
                               class="form-control"
                               min="1"
                               max="999"
                               value="{{ old('urutan') }}"
                               placeholder="Otomatis">
                    </div>
                </div>

                <div class="form-group" style="margin-bottom:0">
                    <label class="form-label" for="cs_isi_prosedur">
                        Isi Prosedur SOP <span class="required">*</span>
                    </label>
                    <textarea id="cs_isi_prosedur"
                              name="isi_prosedur"
                              rows="4"
                              class="form-control"
                              placeholder="Tuliskan langkah / ketentuan Standar Operasional Prosedur..."
                              required>{{ old('isi_prosedur') }}</textarea>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalCreateSop')">Batal</button>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-floppy-disk"></i>
                    Simpan SOP
                </button>
            </div>
        </form>
    </div>
</div>


{{-- ===================== MODAL: EDIT SOP ===================== --}}
<div class="modal-overlay" id="modalEditSop">
    <div class="modal-box" style="max-width:560px">
        <div class="modal-header">
            <div class="modal-title">
                <i class="fa-solid fa-pen-to-square" style="color:var(--warning);margin-right:8px"></i>
                Edit Butir SOP
            </div>
            <button type="button" class="modal-close" onclick="closeModal('modalEditSop')">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form id="formEditSop" action="" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <div style="display:grid;grid-template-columns:1.5fr 1fr;gap:14px;">
                    <div class="form-group">
                        <label class="form-label" for="es_role_target">
                            Role / Bagian <span class="required">*</span>
                        </label>
                        <select id="es_role_target" name="role_target" class="form-control" required>
                            @foreach($roles as $roleKey => $roleLabel)
                                <option value="{{ $roleKey }}">{{ $roleLabel }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="es_urutan">
                            Nomor Urut <span class="required">*</span>
                        </label>
                        <input type="number"
                               id="es_urutan"
                               name="urutan"
                               class="form-control"
                               min="1"
                               max="999"
                               required>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom:0">
                    <label class="form-label" for="es_isi_prosedur">
                        Isi Prosedur SOP <span class="required">*</span>
                    </label>
                    <textarea id="es_isi_prosedur"
                              name="isi_prosedur"
                              rows="4"
                              class="form-control"
                              required></textarea>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalEditSop')">Batal</button>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-floppy-disk"></i>
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>


{{-- ===================== MODAL: KONFIRMASI HAPUS SOP ===================== --}}
<div class="modal-overlay" id="modalDeleteSop">
    <div class="modal-box" style="max-width:440px">
        <div class="modal-header">
            <div class="modal-title">
                <i class="fa-solid fa-triangle-exclamation" style="color:var(--danger);margin-right:8px"></i>
                Hapus Butir SOP
            </div>
            <button type="button" class="modal-close" onclick="closeModal('modalDeleteSop')">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form id="formDeleteSop" action="" method="POST">
            @csrf
            @method('DELETE')
            <div class="modal-body">
                <p style="font-size:13.5px;color:var(--text-primary);margin-bottom:10px;">
                    Apakah Anda yakin ingin menghapus butir SOP untuk role <strong id="ds_role_label"></strong> ini?
                </p>
                <div id="ds_isi_preview"
                     style="background:#FEF2F2;border:1px solid #FECACA;border-radius:8px;padding:10px 12px;font-size:12.5px;color:#7F1D1D;">
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalDeleteSop')">Batal</button>
                <button type="submit" class="btn btn-danger">
                    <i class="fa-solid fa-trash"></i>
                    Ya, Hapus
                </button>
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

    window.openCreateSop = function (defaultRole) {
        var select = document.getElementById('cs_role_target');
        if (select && defaultRole) {
            select.value = defaultRole;
        }
        window.openModal('modalCreateSop');
    };

    window.openEditSop = function (btn) {
        var id     = btn.getAttribute('data-id');
        var role   = btn.getAttribute('data-role');
        var urutan = btn.getAttribute('data-urutan');
        var isi    = btn.getAttribute('data-isi');

        document.getElementById('formEditSop').action     = '/sop/' + id;
        document.getElementById('es_role_target').value   = role;
        document.getElementById('es_urutan').value        = urutan;
        document.getElementById('es_isi_prosedur').value  = isi;

        window.openModal('modalEditSop');
    };

    window.openDeleteSop = function (btn) {
        var id   = btn.getAttribute('data-id');
        var role = btn.getAttribute('data-role');
        var isi  = btn.getAttribute('data-isi');

        document.getElementById('formDeleteSop').action          = '/sop/' + id;
        document.getElementById('ds_role_label').textContent     = role;
        document.getElementById('ds_isi_preview').textContent    = isi;

        window.openModal('modalDeleteSop');
    };
})();
</script>
@endpush

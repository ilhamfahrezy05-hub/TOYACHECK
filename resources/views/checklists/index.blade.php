@extends('layouts.app')

@section('title', auth()->user()->hasRole('mekanik') ? 'Checklist Perbaikan' : 'Checklist Armada')
@section('page-title', auth()->user()->hasRole('mekanik') ? 'Checklist Perbaikan Armada' : 'Checklist Armada')
@section('page-subtitle', auth()->user()->hasRole('mekanik') ? 'Informasi hasil pemeriksaan Driver yang melaporkan unit Dalam Perbaikan (Read-Only)' : 'Pemeriksaan kondisi kendaraan sebelum beroperasi')

{{-- ===================== PAGE ACTIONS ===================== --}}
@section('page-actions')
    @if(!empty($allowedTypes))
    <button class="btn btn-primary" onclick="openModal('modalCreateChecklist')">
        <i class="fa-solid fa-plus"></i>
        Buat Checklist
    </button>
    @endif
@endsection

{{-- ===================== STYLES ===================== --}}
@push('styles')
<style>
    .ck-group {
        border: 1px solid var(--border);
        border-radius: var(--radius-sm, 8px);
        margin-bottom: 14px;
        overflow: hidden;
    }
    .ck-group-title {
        background: #F8FAFC;
        padding: 9px 14px;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
        color: var(--text-secondary);
        border-bottom: 1px solid var(--border);
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .ck-row {
        padding: 10px 14px;
        border-bottom: 1px solid #F1F5F9;
        transition: background .15s;
    }
    .ck-row:last-child { border-bottom: 0; }
    .ck-row.is-bad { background: var(--danger-bg); }
    .ck-row-main {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }
    .ck-row-label { font-size: 13px; font-weight: 500; color: var(--text-primary); }
    .ck-row-criteria {
        font-size: 11px;
        color: var(--text-muted);
        margin-top: 2px;
    }
    .ck-photo-upload {
        margin-top: 8px;
        padding-top: 8px;
        border-top: 1px dashed rgba(220,38,38,0.2);
        display: none;
    }
    .ck-row.is-bad .ck-photo-upload {
        display: block;
    }

    .ck-toggle { display: inline-flex; background: #F1F5F9; border-radius: 8px; padding: 3px; flex-shrink: 0; }
    .ck-toggle input { display: none; }
    .ck-toggle label {
        margin: 0;
        padding: 5px 12px;
        font-size: 12px;
        font-weight: 600;
        border-radius: 6px;
        cursor: pointer;
        color: var(--text-secondary);
        transition: all .15s;
        user-select: none;
    }
    .ck-toggle input.ok:checked + label  { background: var(--success); color: #fff; box-shadow: 0 1px 4px rgba(22,163,74,.35); }
    .ck-toggle input.bad:checked + label { background: var(--danger);  color: #fff; box-shadow: 0 1px 4px rgba(220,38,38,.35); }

    .ck-summary {
        display: flex; align-items: center; justify-content: space-between; gap: 12px;
        padding: 10px 14px; border-radius: 8px; margin-bottom: 14px;
        background: var(--success-bg); color: var(--success);
        font-size: 13px; font-weight: 600; transition: all .2s;
    }
    .ck-summary.has-problem { background: var(--danger-bg); color: var(--danger); }
    .ck-summary button {
        border: 0; background: rgba(255,255,255,.7); border-radius: 6px;
        padding: 4px 10px; font-size: 12px; font-weight: 600; cursor: pointer; color: inherit;
    }
    .ck-summary button:hover { background: #fff; }

    .ck-plat {
        background:#0F1E36;color:#fff;padding:3px 10px;border-radius:4px;
        font-size:12px;font-weight:700;letter-spacing:.08em;font-family:monospace;
    }
</style>
@endpush

{{-- ===================== CONTENT ===================== --}}
@section('content')

@php
    $categoryIcons = [
        'Mesin & Fluida'        => 'fa-oil-can',
        'Ban & Rem'             => 'fa-circle-dot',
        'Kelistrikan'           => 'fa-lightbulb',
        'Bodi & Kelengkapan'    => 'fa-box',
        'Peralatan Keselamatan' => 'fa-shield-halved',
        'Kebersihan Kendaraan'  => 'fa-broom',
    ];
@endphp

{{-- ── STAT CARDS ── --}}
@if(auth()->user()->hasRole('mekanik'))
<div class="stats-grid" style="grid-template-columns: repeat(2, 1fr)">
    <div class="stat-card">
        <div class="stat-icon amber"><i class="fa-solid fa-screwdriver-wrench"></i></div>
        <div class="stat-info">
            <div class="stat-label">Total Laporan Dalam Perbaikan</div>
            <div class="stat-value">{{ $stats['perbaikan'] ?? $stats['total'] }}</div>
            <div class="stat-change">Hasil pemeriksaan driver (Mode Dalam Perbaikan)</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon violet"><i class="fa-solid fa-calendar-day"></i></div>
        <div class="stat-info">
            <div class="stat-label">Laporan Hari Ini</div>
            <div class="stat-value">{{ $stats['hari_ini'] }}</div>
            <div class="stat-change">{{ now()->translatedFormat('d M Y') }}</div>
        </div>
    </div>
</div>
@else
<div class="stats-grid" style="grid-template-columns: repeat(5, 1fr)">

    <div class="stat-card">
        <div class="stat-icon blue"><i class="fa-solid fa-clipboard-list"></i></div>
        <div class="stat-info">
            <div class="stat-label">Total Checklist</div>
            <div class="stat-value">{{ $stats['total'] }}</div>
            <div class="stat-change">Seluruh pemeriksaan</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon violet"><i class="fa-solid fa-calendar-day"></i></div>
        <div class="stat-info">
            <div class="stat-label">Hari Ini</div>
            <div class="stat-value">{{ $stats['hari_ini'] }}</div>
            <div class="stat-change">{{ now()->translatedFormat('d M Y') }}</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon green"><i class="fa-solid fa-circle-check"></i></div>
        <div class="stat-info">
            <div class="stat-label">Kondisi Aman</div>
            <div class="stat-value">{{ $stats['aman'] }}</div>
            <div class="stat-change">Semua item baik</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon red"><i class="fa-solid fa-triangle-exclamation"></i></div>
        <div class="stat-info">
            <div class="stat-label">Ada Kendala</div>
            <div class="stat-value">{{ $stats['kendala'] }}</div>
            <div class="stat-change">Perlu tindak lanjut</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon amber"><i class="fa-solid fa-screwdriver-wrench"></i></div>
        <div class="stat-info">
            <div class="stat-label">Dalam Perbaikan</div>
            <div class="stat-value">{{ $stats['perbaikan'] ?? 0 }}</div>
            <div class="stat-change">Unit tidak beroperasi</div>
        </div>
    </div>

</div>
@endif

{{-- ── TABLE CARD ── --}}
<div class="card">
    <div class="card-header">
        <div>
            <div class="card-title">
                {{ auth()->user()->hasRole('mekanik') ? 'Daftar Pemeriksaan Driver — Dalam Perbaikan' : 'Riwayat Checklist' }}
            </div>
            <div class="card-subtitle">Total {{ $checklists->total() }} pemeriksaan tercatat</div>
        </div>

        <div class="d-flex gap-8 align-center">
            <div style="position:relative">
                <i class="fa-solid fa-magnifying-glass"
                   style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:var(--text-muted);font-size:13px;pointer-events:none"></i>
                <input
                    type="text"
                    id="checklistSearch"
                    placeholder="Cari armada / petugas..."
                    class="form-control"
                    style="padding-left:32px;width:230px"
                    oninput="applyChecklistFilter()">
            </div>

            @unless(auth()->user()->hasRole('mekanik'))
            <select id="checklistTypeFilter" class="form-control" style="width:130px" onchange="applyChecklistFilter()">
                <option value="">Semua Tipe</option>
                <option value="fisik">Fisik</option>
                <option value="k3">K3 & Safety</option>
            </select>

            <select id="checklistStatusFilter" class="form-control" style="width:165px" onchange="applyChecklistFilter()">
                <option value="">Semua Status</option>
                <option value="aman">Aman</option>
                <option value="kendala">Kendala</option>
                <option value="perbaikan">Dalam Perbaikan</option>
            </select>
            @endunless
        </div>
    </div>

    <div class="card-body-p0">
        <div class="table-wrap">
            <table class="tc-table" id="checklistTable">
                <thead>
                    <tr>
                        <th style="width:52px">#</th>
                        <th>Tanggal</th>
                        <th>Tipe</th>
                        <th>Armada</th>
                        <th>Petugas</th>
                        <th>Item / Catatan Keterangan</th>
                        <th>Status</th>
                        <th style="width:90px;text-align:center">Aksi</th>
                    </tr>
                </thead>
                <tbody id="checklistTableBody">
                    @forelse ($checklists as $index => $checklist)
                    @php
                        $totalItem   = $checklist->items->count();
                        $badItem     = $checklist->items->where('kondisi', false)->count();
                        $isAman      = $checklist->status === 'aman';
                        $isPerbaikan = $checklist->status === 'perbaikan';
                        $petugasNama = $checklist->driver->name ?? $checklist->user->name ?? '—';
                    @endphp
                    <tr
                        data-armada="{{ strtolower(($checklist->vehicle->plat_nomor ?? '') . ' ' . ($checklist->vehicle->merk ?? '') . ' ' . ($checklist->vehicle->jenis_kendaraan ?? '')) }}"
                        data-petugas="{{ strtolower($petugasNama) }}"
                        data-type="{{ $checklist->checklist_type }}"
                        data-status="{{ $checklist->status }}">

                        <td style="color:var(--text-muted);font-size:12px">
                            {{ $checklists->firstItem() + $index }}
                        </td>

                        {{-- Tanggal --}}
                        <td style="white-space:nowrap">
                            <div style="font-weight:600;font-size:13px">
                                {{ $checklist->tanggal->translatedFormat('d M Y') }}
                            </div>
                            <div style="font-size:11px;color:var(--text-muted)">
                                {{ $checklist->tanggal->format('H:i') }} WIB
                            </div>
                        </td>

                        {{-- Tipe --}}
                        <td>
                            @if($checklist->checklist_type === 'k3')
                                <span class="badge badge-info"><i class="fa-solid fa-shield-halved"></i> K3</span>
                            @else
                                <span class="badge badge-primary"><i class="fa-solid fa-truck-ramp-box"></i> Fisik</span>
                            @endif
                        </td>

                        {{-- Armada --}}
                        <td>
                            <div class="d-flex align-center gap-8">
                                <div style="width:32px;height:32px;border-radius:8px;background:var(--primary-subtle);display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:13px;color:var(--primary)">
                                    <i class="fa-solid fa-truck-front"></i>
                                </div>
                                <div>
                                    <div style="font-weight:600;font-family:monospace;font-size:13px">{{ $checklist->vehicle->merk ?? '—' }}</div>
                                    <div style="font-weight:600;font-family:monospace;font-size:13px">{{ $checklist->vehicle->jenis_kendaraan ?? '—' }}</div>
                                    <span class="ck-plat" style="font-size:10px;padding:1px 7px">
                                        {{ strtoupper($checklist->vehicle->plat_nomor ?? '-') }}
                                    </span>
                                </div>
                            </div>
                        </td>

                        {{-- Petugas --}}
                        <td style="font-weight:500">
                            <i class="fa-regular fa-user" style="color:var(--text-muted);margin-right:4px"></i>
                            {{ $petugasNama }}
                        </td>

                        {{-- Item / Catatan Keterangan --}}
                        <td style="font-size:13px;color:var(--text-secondary);max-width:300px">
                            @if($isPerbaikan)
                                <div style="font-weight:700;color:#B45309;font-size:12px">
                                    <i class="fa-solid fa-lock" style="margin-right:4px"></i> Komponen Dikunci (Dalam Perbaikan)
                                </div>
                                @if($checklist->catatan)
                                    <div style="font-size:12px;color:var(--text-primary);margin-top:3px;background:#FFFBEB;border:1px solid #FDE68A;padding:5px 8px;border-radius:6px">
                                        <strong>Keterangan Driver:</strong> {{ $checklist->catatan }}
                                    </div>
                                @endif
                            @else
                                {{ $totalItem - $badItem }}/{{ $totalItem }} baik
                                @if($badItem > 0)
                                    <span style="color:var(--danger);font-weight:600">
                                        · {{ $badItem }} bermasalah
                                    </span>
                                @endif
                                @if($checklist->catatan)
                                    <div style="font-size:11.5px;color:var(--text-muted);margin-top:2px">
                                        <i class="fa-regular fa-comment"></i> {{ \Illuminate\Support\Str::limit($checklist->catatan, 65) }}
                                    </div>
                                @endif
                            @endif
                        </td>

                        {{-- Status --}}
                        <td>
                            @if($isAman)
                                <span class="badge badge-success">
                                    <i class="fa-solid fa-circle-check"></i> Aman
                                </span>
                            @elseif($isPerbaikan)
                                <span class="badge badge-warning">
                                    <i class="fa-solid fa-screwdriver-wrench"></i> Dalam Perbaikan
                                </span>
                            @else
                                <span class="badge badge-danger">
                                    <i class="fa-solid fa-triangle-exclamation"></i> Kendala
                                </span>
                            @endif
                        </td>

                        {{-- Aksi --}}
                        <td>
                            <div class="d-flex gap-8 align-center" style="justify-content:center">
                                <a href="{{ route('checklists.show', $checklist->id) }}"
                                   class="btn btn-secondary btn-sm btn-icon"
                                   title="Lihat Detail">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                                @role('admin')
                                <a href="{{ route('checklists.edit', $checklist->id) }}"
                                   class="btn btn-warning btn-sm btn-icon"
                                   title="Edit Hasil Checklist">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                                @endrole
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr id="emptyChecklistRow">
                        <td colspan="8">
                            <div class="empty-state">
                                <div class="empty-icon"><i class="fa-solid fa-clipboard-check"></i></div>
                                <div class="empty-title">Belum Ada Checklist</div>
                                <div class="empty-text">Mulai pemeriksaan pertama dengan klik tombol "Buat Checklist"</div>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($checklists->hasPages())
    <div class="card-footer">
        {{ $checklists->links('pagination::bootstrap-4') }}
    </div>
    @endif
</div>

@endsection

{{-- ===================== MODALS ===================== --}}
@section('modals')

@if(!empty($allowedTypes))
<div class="modal-overlay" id="modalCreateChecklist">
    <div class="modal-box modal-lg" style="max-height:92vh;display:flex;flex-direction:column">

        <div class="modal-header">
            <div class="modal-title">
                <i class="fa-solid fa-clipboard-check" style="color:var(--primary);margin-right:8px"></i>
                Buat Checklist Armada
            </div>
            <button class="modal-close" onclick="closeModal('modalCreateChecklist')" type="button">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form action="{{ route('checklists.store') }}" method="POST" id="formCreateChecklist"
              enctype="multipart/form-data"
              style="display:flex;flex-direction:column;min-height:0;flex:1">
            @csrf

            <div class="modal-body" style="overflow-y:auto">

                {{-- Row 1: Pilih armada & Tipe checklist --}}
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px">
                    <div class="form-group" style="margin-bottom:0">
                        <label class="form-label" for="ck_vehicle_id">
                            Armada <span class="required">*</span>
                        </label>
                        <select id="ck_vehicle_id" name="vehicle_id"
                                class="form-control {{ $errors->has('vehicle_id') ? 'is-invalid' : '' }}"
                                required>
                            <option value="">— Pilih Armada —</option>
                            @foreach($vehicles as $v)
                                <option value="{{ $v->id }}" {{ old('vehicle_id') == $v->id ? 'selected' : '' }}>
                                    {{ strtoupper($v->plat_nomor) }} · {{ $v->merk }} - {{ $v->jenis_kendaraan }} ({{ ucfirst(str_replace('_', ' ', $v->status)) }})
                                </option>
                            @endforeach
                        </select>
                        @error('vehicle_id')
                            <div class="invalid-feedback">
                                <i class="fa-solid fa-circle-exclamation"></i> {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="form-group" style="margin-bottom:0">
                        <label class="form-label" for="ck_type_select">
                            Tipe Pemeriksaan <span class="required">*</span>
                        </label>
                        <select id="ck_type_select" name="checklist_type" class="form-control" onchange="switchChecklistType(this.value)">
                            @foreach($allowedTypes as $type)
                                <option value="{{ $type }}" {{ old('checklist_type', $allowedTypes[0]) === $type ? 'selected' : '' }}>
                                    {{ $type === 'k3' ? 'K3 & Kebersihan (HSE)' : 'Fisik Kendaraan (Driver)' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Ringkasan live --}}
                <div class="ck-summary" id="ckSummary">
                    <span id="ckSummaryText">
                        <i class="fa-solid fa-circle-check"></i>
                        Semua item dalam kondisi baik
                    </span>
                    <button type="button" onclick="setAllChecklist('1')">
                        <i class="fa-solid fa-rotate-left"></i> Setel Semua Baik
                    </button>
                </div>

                @error('items')
                    <div class="invalid-feedback" style="margin-bottom:10px">
                        <i class="fa-solid fa-circle-exclamation"></i> {{ $message }}
                    </div>
                @enderror

                {{-- Wadah Item Pemeriksaan per Tipe --}}
                @foreach($allowedTypes as $type)
                <div class="type-container" id="typeContainer_{{ $type }}" style="{{ old('checklist_type', $allowedTypes[0]) === $type ? '' : 'display:none' }}">
                    @php
                        $groups = $inspectionGroups[$type] ?? collect();
                    @endphp

                    @forelse($groups as $group)
                    <div class="ck-group">
                        <div class="ck-group-title">
                            <i class="fa-solid {{ $categoryIcons[$group->nama] ?? 'fa-list-check' }}" style="color:var(--primary)"></i>
                            {{ $group->nama }}
                        </div>

                        @foreach($group->children as $inspection)
                        @php
                            $value = old('items.' . $inspection->id, '1');
                        @endphp
                        <div class="ck-row {{ (string) $value === '0' ? 'is-bad' : '' }}">
                            <div class="ck-row-main">
                                <div>
                                    <div class="ck-row-label">{{ $inspection->nama }}</div>
                                    @if($inspection->criteria)
                                        <div class="ck-row-criteria"><i class="fa-solid fa-circle-info"></i> {{ $inspection->criteria }}</div>
                                    @endif
                                </div>

                                <div class="ck-toggle">
                                    <input type="radio" class="ok ck-input"
                                           id="ck_{{ $inspection->id }}_1" name="items[{{ $inspection->id }}]" value="1"
                                           {{ (string) $value !== '0' ? 'checked' : '' }}>
                                    <label for="ck_{{ $inspection->id }}_1">Baik</label>

                                    <input type="radio" class="bad ck-input"
                                           id="ck_{{ $inspection->id }}_0" name="items[{{ $inspection->id }}]" value="0"
                                           {{ (string) $value === '0' ? 'checked' : '' }}>
                                    <label for="ck_{{ $inspection->id }}_0">Bermasalah</label>
                                </div>
                            </div>

                            {{-- Unggah foto bukti temuan (opsional jika bermasalah) --}}
                            <div class="ck-photo-upload">
                                <label style="font-size:12px;font-weight:600;color:var(--danger);display:block;margin-bottom:4px">
                                    <i class="fa-solid fa-camera"></i> Foto Bukti Temuan Kerusakan (Opsional)
                                </label>
                                <input type="file" name="foto[{{ $inspection->id }}]" accept="image/*" class="form-control" style="font-size:12px;padding:4px 8px">
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @empty
                    <div class="empty-state" style="padding:32px 16px">
                        <div class="empty-icon"><i class="fa-solid fa-list-check"></i></div>
                        <div class="empty-title">Belum Ada Item Pemeriksaan ({{ strtoupper($type) }})</div>
                        <div class="empty-text">Admin dapat menambahkannya melalui menu Data Master > Item Pemeriksaan.</div>
                    </div>
                    @endforelse
                </div>
                @endforeach

                {{-- Catatan --}}
                <div class="form-group" style="margin-bottom:0;margin-top:16px">
                    <label class="form-label" for="ck_catatan">Catatan Umum</label>
                    <textarea id="ck_catatan" name="catatan" rows="3"
                              class="form-control"
                              placeholder="Jelaskan ringkasan temuan atau catatan rute kendaraan">{{ old('catatan') }}</textarea>
                </div>

            </div>{{-- /.modal-body --}}

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalCreateChecklist')">
                    Batal
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-paper-plane"></i>
                    Kirim Checklist
                </button>
            </div>
        </form>
    </div>
</div>
@endif

@endsection

{{-- ===================== PAGE SCRIPTS ===================== --}}
@push('scripts')
<script>
(function () {
    'use strict';

    /* ─────────────────────────────────────────
       Re-open modal bila validasi gagal
    ───────────────────────────────────────── */
    @if($errors->any())
        document.addEventListener('DOMContentLoaded', function () {
            window.openModal('modalCreateChecklist');
            updateSummary();
        });
    @endif

    /* ─────────────────────────────────────────
       Ganti Tipe Checklist (Fisik / K3)
    ───────────────────────────────────────── */
    window.switchChecklistType = function (selectedType) {
        document.querySelectorAll('.type-container').forEach(function (el) {
            el.style.display = 'none';
            // Disable inputs in hidden containers to prevent validation conflict
            el.querySelectorAll('input').forEach(function (input) { input.disabled = true; });
        });

        var activeContainer = document.getElementById('typeContainer_' + selectedType);
        if (activeContainer) {
            activeContainer.style.display = 'block';
            activeContainer.querySelectorAll('input').forEach(function (input) { input.disabled = false; });
        }
        updateSummary();
    };

    // Jalankan disable pada container yang tidak aktif saat pertama kali load
    var initType = document.getElementById('ck_type_select')?.value;
    if (initType) {
        window.switchChecklistType(initType);
    }

    /* ─────────────────────────────────────────
       Ringkasan live + highlight baris bermasalah
    ───────────────────────────────────────── */
    function updateSummary() {
        var activeContainer = document.querySelector('.type-container:not([style*="display: none"])');
        if (!activeContainer) return;

        var bad = 0;
        var total = activeContainer.querySelectorAll('.ck-row').length;

        activeContainer.querySelectorAll('.ck-row').forEach(function (row) {
            var isBad = !!row.querySelector('input.bad:checked');
            row.classList.toggle('is-bad', isBad);
            if (isBad) bad++;
        });

        var box  = document.getElementById('ckSummary');
        var text = document.getElementById('ckSummaryText');
        if (!box || !text) return;

        if (bad > 0) {
            box.classList.add('has-problem');
            text.innerHTML = '<i class="fa-solid fa-triangle-exclamation"></i> ' +
                bad + ' item bermasalah — status armada akan otomatis diubah menjadi <u>Rusak</u>.';
        } else {
            box.classList.remove('has-problem');
            text.innerHTML = '<i class="fa-solid fa-circle-check"></i> Semua ' +
                total + ' item dalam kondisi baik';
        }
    }

    document.querySelectorAll('#formCreateChecklist .ck-input').forEach(function (input) {
        input.addEventListener('change', updateSummary);
    });

    window.setAllChecklist = function (val) {
        var activeContainer = document.querySelector('.type-container:not([style*="display: none"])');
        if (!activeContainer) return;

        activeContainer.querySelectorAll('input.' + (val === '1' ? 'ok' : 'bad'))
            .forEach(function (el) { el.checked = true; });
        updateSummary();
    };

    /* ─────────────────────────────────────────
       Search + filter status + filter tipe
    ───────────────────────────────────────── */
    window.applyChecklistFilter = function () {
        var query     = (document.getElementById('checklistSearch').value || '').toLowerCase().trim();
        var typeVal   = (document.getElementById('checklistTypeFilter').value || '').toLowerCase();
        var statusVal = (document.getElementById('checklistStatusFilter').value || '').toLowerCase();
        var rows      = document.querySelectorAll('#checklistTableBody tr[data-armada]');
        var visible   = 0;

        rows.forEach(function (row) {
            var haystack = (row.getAttribute('data-armada') || '') + ' ' + (row.getAttribute('data-petugas') || '');
            var textMatch   = !query || haystack.includes(query);
            var typeMatch   = !typeVal || row.getAttribute('data-type') === typeVal;
            var statusMatch = !statusVal || row.getAttribute('data-status') === statusVal;
            var show = textMatch && typeMatch && statusMatch;

            row.style.display = show ? '' : 'none';
            if (show) visible++;
        });

        var noResult = document.getElementById('noChecklistResult');

        if ((query || typeVal || statusVal) && visible === 0) {
            if (!noResult) {
                noResult = document.createElement('tr');
                noResult.id = 'noChecklistResult';
                noResult.innerHTML =
                    '<td colspan="8"><div class="empty-state">' +
                    '<div class="empty-icon"><i class="fa-solid fa-magnifying-glass"></i></div>' +
                    '<div class="empty-title">Tidak Ditemukan</div>' +
                    '<div class="empty-text">Tidak ada checklist yang cocok dengan filter.</div>' +
                    '</div></td>';
                document.getElementById('checklistTableBody').appendChild(noResult);
            }
        } else if (noResult) {
            noResult.remove();
        }
    };

})();
</script>
@endpush

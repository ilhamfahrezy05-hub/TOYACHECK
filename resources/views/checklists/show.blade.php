@extends('layouts.app')

@section('title', 'Detail Checklist')
@section('page-title', 'Detail Checklist')
@section('page-subtitle', 'Hasil pemeriksaan ' . ($checklist->vehicle->kode_armada ?? 'armada'))

@section('page-actions')
    <div class="d-flex gap-8 align-center">
        @role('admin')
        <a href="{{ route('checklists.edit', $checklist->id) }}" class="btn btn-warning">
            <i class="fa-solid fa-pen-to-square"></i>
            Edit Hasil Checklist
        </a>
        @endrole
        <a href="{{ route('checklists.index') }}" class="btn btn-secondary">
            <i class="fa-solid fa-arrow-left"></i>
            Kembali
        </a>
    </div>
@endsection

@push('styles')
<style>
    .ck-detail-grid { display: grid; grid-template-columns: 340px 1fr; gap: 20px; align-items: start; }
    @media (max-width: 992px) { .ck-detail-grid { grid-template-columns: 1fr; } }

    .ck-meta-row {
        display: flex; justify-content: space-between; align-items: center; gap: 12px;
        padding: 11px 0; border-bottom: 1px dashed var(--border); font-size: 13px;
    }
    .ck-meta-row:last-child { border-bottom: 0; }
    .ck-meta-key { color: var(--text-secondary); }
    .ck-meta-val { font-weight: 600; color: var(--text-primary); text-align: right; }

    .ck-item-row {
        padding: 14px 20px; border-bottom: 1px solid #F1F5F9; font-size: 13px;
    }
    .ck-item-row:last-child { border-bottom: 0; }
    .ck-item-row.is-bad { background: #FEF2F2; }
    .ck-item-row-header {
        display: flex; align-items: center; justify-content: space-between; gap: 12px;
    }
    .ck-item-name { display: flex; align-items: center; gap: 10px; font-weight: 600; }
    .ck-item-dot {
        width: 26px; height: 26px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center; font-size: 12px;
        flex-shrink: 0;
    }
    .ck-item-dot.ok  { background: var(--success-bg); color: var(--success); }
    .ck-item-dot.bad { background: var(--danger); color: #fff; }
    .ck-item-criteria {
        margin-top: 4px;
        margin-left: 36px;
        font-size: 12px;
        color: var(--text-muted);
        line-height: 1.5;
    }
    .ck-item-photo {
        margin-top: 10px;
        margin-left: 36px;
    }
    .ck-item-photo img {
        max-width: 220px;
        height: auto;
        border-radius: 8px;
        border: 1px solid #FCA5A5;
        box-shadow: 0 2px 6px rgba(0,0,0,0.06);
        cursor: pointer;
        transition: transform .2s;
    }
    .ck-item-photo img:hover {
        transform: scale(1.02);
    }
</style>
@endpush

@section('content')

@php
    $isAman    = $checklist->status === 'aman';
    $totalItem = $checklist->items->count();
    $badItem   = $checklist->items->where('kondisi', false)->count();
    $okItem    = $totalItem - $badItem;
    $percent   = $totalItem > 0 ? round(($okItem / $totalItem) * 100) : 0;

    // Item bermasalah ditampilkan paling atas
    $sortedItems = $checklist->items->sortBy(fn ($i) => $i->kondisi ? 1 : 0)->values();
    $petugasNama = $checklist->driver->name ?? $checklist->user->name ?? '—';
@endphp

{{-- ── Banner status ── --}}
<div style="
    display:flex;align-items:center;gap:14px;padding:16px 20px;border-radius:12px;margin-bottom:20px;
    background:{{ $isAman ? 'var(--success-bg)' : 'var(--danger-bg)' }};
    color:{{ $isAman ? 'var(--success)' : 'var(--danger)' }}">
    <div style="font-size:26px">
        <i class="fa-solid {{ $isAman ? 'fa-circle-check' : 'fa-triangle-exclamation' }}"></i>
    </div>
    <div>
        <div style="font-size:15px;font-weight:700">
            {{ $isAman ? 'Armada dalam kondisi aman' : 'Ditemukan kendala pada armada' }}
        </div>
        <div style="font-size:13px;opacity:.85">
            {{ $isAman
                ? 'Seluruh item pemeriksaan dinyatakan baik.'
                : $badItem . ' dari ' . $totalItem . ' item pemeriksaan bermasalah dan memerlukan perbaikan/maintenance.' }}
        </div>
    </div>
</div>

<div class="ck-detail-grid">

    {{-- ── Kolom kiri: informasi ── --}}
    <div class="card">
        <div class="card-header">
            <div>
                <div class="card-title">Informasi Checklist</div>
                <div class="card-subtitle">#{{ str_pad($checklist->id, 5, '0', STR_PAD_LEFT) }}</div>
            </div>
        </div>
        <div style="padding:6px 20px 16px">

            <div class="ck-meta-row">
                <span class="ck-meta-key">Tipe Checklist</span>
                <span class="ck-meta-val">
                    @if($checklist->checklist_type === 'k3')
                        <span class="badge badge-info"><i class="fa-solid fa-shield-halved"></i> K3 & Kebersihan</span>
                    @else
                        <span class="badge badge-primary"><i class="fa-solid fa-truck-ramp-box"></i> Fisik Kendaraan</span>
                    @endif
                </span>
            </div>

            <div class="ck-meta-row">
                <span class="ck-meta-key">Kode Armada</span>
                <span class="ck-meta-val" style="font-family:monospace">
                    {{ $checklist->vehicle->kode_armada ?? '—' }}
                </span>
            </div>

            <div class="ck-meta-row">
                <span class="ck-meta-key">Plat Nomor</span>
                <span class="ck-meta-val">
                    <span style="background:#0F1E36;color:#fff;padding:3px 10px;border-radius:4px;font-size:12px;letter-spacing:.08em;font-family:monospace">
                        {{ strtoupper($checklist->vehicle->plat_nomor ?? '-') }}
                    </span>
                </span>
            </div>

            <div class="ck-meta-row">
                <span class="ck-meta-key">Kendaraan</span>
                <span class="ck-meta-val">
                    {{ $checklist->vehicle->merk ?? '—' }}
                    <div style="font-size:11px;color:var(--text-muted);font-weight:500">
                        {{ $checklist->vehicle->jenis_kendaraan ?? '' }}
                    </div>
                </span>
            </div>

            <div class="ck-meta-row">
                <span class="ck-meta-key">Tanggal</span>
                <span class="ck-meta-val">{{ $checklist->tanggal->translatedFormat('d F Y') }}</span>
            </div>

            <div class="ck-meta-row">
                <span class="ck-meta-key">Waktu Inspeksi</span>
                <span class="ck-meta-val">{{ $checklist->tanggal->format('H:i') }} WIB</span>
            </div>

            <div class="ck-meta-row">
                <span class="ck-meta-key">Petugas (Inspektor)</span>
                <span class="ck-meta-val">{{ $petugasNama }}</span>
            </div>

            @if($checklist->user && $checklist->user->id !== $checklist->driver_id)
            <div class="ck-meta-row">
                <span class="ck-meta-key">Diinput Oleh</span>
                <span class="ck-meta-val">{{ $checklist->user->name }}</span>
            </div>
            @endif

            <div class="ck-meta-row">
                <span class="ck-meta-key">Status</span>
                <span class="ck-meta-val">
                    <span class="badge {{ $isAman ? 'badge-success' : 'badge-danger' }}">
                        <i class="fa-solid {{ $isAman ? 'fa-circle-check' : 'fa-triangle-exclamation' }}"></i>
                        {{ $isAman ? 'Aman' : 'Kendala' }}
                    </span>
                </span>
            </div>

            {{-- Progress --}}
            <div style="margin-top:14px">
                <div class="d-flex" style="justify-content:space-between;font-size:12px;margin-bottom:6px">
                    <span class="ck-meta-key">Item Baik</span>
                    <strong>{{ $okItem }}/{{ $totalItem }} ({{ $percent }}%)</strong>
                </div>
                <div style="height:8px;border-radius:99px;background:#F1F5F9;overflow:hidden">
                    <div style="height:100%;width:{{ $percent }}%;border-radius:99px;
                        background:{{ $isAman ? 'var(--success)' : 'var(--warning)' }}"></div>
                </div>
            </div>

            {{-- Catatan --}}
            <div style="margin-top:18px">
                <div class="ck-meta-key" style="font-size:12px;margin-bottom:6px">
                    <i class="fa-regular fa-note-sticky"></i> Catatan
                </div>
                <div style="background:#F8FAFC;border:1px solid var(--border);border-radius:8px;padding:12px;font-size:13px;line-height:1.6;color:var(--text-primary)">
                    {{ $checklist->catatan ?: 'Tidak ada catatan khusus.' }}
                </div>
            </div>

        </div>
    </div>

    {{-- ── Kolom kanan: item ── --}}
    <div class="card">
        <div class="card-header">
            <div>
                <div class="card-title">Hasil Pemeriksaan</div>
                <div class="card-subtitle">
                    {{ $okItem }} baik · {{ $badItem }} bermasalah
                </div>
            </div>
        </div>

        <div class="card-body-p0">
            @forelse($sortedItems as $item)
                <div class="ck-item-row {{ $item->kondisi ? '' : 'is-bad' }}">
                    <div class="ck-item-row-header">
                        <div class="ck-item-name">
                            <span class="ck-item-dot {{ $item->kondisi ? 'ok' : 'bad' }}">
                                <i class="fa-solid {{ $item->kondisi ? 'fa-check' : 'fa-xmark' }}"></i>
                            </span>
                            {{ $item->item }}
                        </div>
                        <span class="badge {{ $item->kondisi ? 'badge-success' : 'badge-danger' }}">
                            {{ $item->kondisi ? 'Baik' : 'Bermasalah' }}
                        </span>
                    </div>

                    @if($item->criteria)
                    <div class="ck-item-criteria">
                        <i class="fa-solid fa-circle-info" style="margin-right:4px;color:var(--text-muted)"></i>
                        <strong>Kriteria:</strong> {{ $item->criteria }}
                    </div>
                    @endif

                    @if($item->foto_dokumentasi)
                    <div class="ck-item-photo">
                        <a href="{{ asset('storage/' . $item->foto_dokumentasi) }}" target="_blank" title="Klik untuk perbesar">
                            <img src="{{ asset('storage/' . $item->foto_dokumentasi) }}" alt="Bukti {{ $item->item }}">
                        </a>
                        <div style="font-size:11px;color:var(--danger);margin-top:4px">
                            <i class="fa-solid fa-camera"></i> Bukti Temuan Kerusakan (Klik gambar untuk perbesar)
                        </div>
                    </div>
                    @endif
                </div>
            @empty
                <div class="empty-state">
                    <div class="empty-icon"><i class="fa-solid fa-clipboard-question"></i></div>
                    <div class="empty-title">Tidak Ada Item</div>
                    <div class="empty-text">Checklist ini tidak memiliki item pemeriksaan.</div>
                </div>
            @endforelse
        </div>
    </div>

</div>

@endsection

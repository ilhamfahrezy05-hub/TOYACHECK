@extends('driver.layout')

@section('title', 'Riwayat — TOYACHECK Mobile')
@section('back_url', route('driver.index'))
@section('header_title', 'Riwayat')
@section('right_action')
    <a href="{{ route('driver.notifications') }}" class="m-topbar-btn" title="Info">
        <i class="fa-regular fa-circle-question"></i>
    </a>
@endsection

@push('styles')
<style>
    .m-filter-bar {
        padding: 12px 18px 8px;
        display: flex;
        gap: 8px;
        overflow-x: auto;
    }

    .m-filter-pill {
        padding: 6px 14px;
        border-radius: 999px;
        font-size: 11.5px;
        font-weight: 700;
        text-decoration: none;
        background: #E5E7EB;
        color: #374151;
        white-space: nowrap;
    }

    .m-filter-pill.active {
        background: #0B2E8A;
        color: #FFFFFF;
    }

    .m-history-cards {
        padding: 10px 18px 24px;
        display: flex;
        flex-direction: column;
        gap: 14px;
    }

    .m-riwayat-card {
        background: linear-gradient(180deg, #F5F6F8 0%, #E7E9ED 100%);
        border-radius: 16px;
        padding: 14px 16px;
        display: flex;
        align-items: center;
        gap: 14px;
        text-decoration: none;
        color: #111827;
        box-shadow: 0 4px 10px rgba(0,0,0,0.12);
        border: 1px solid rgba(255,255,255,0.8);
    }

    .m-riwayat-date {
        font-size: 11px;
        font-weight: 700;
        color: #111827;
        text-transform: lowercase;
    }

    .m-riwayat-plat {
        font-size: 13.5px;
        font-weight: 800;
        color: #111827;
        margin: 2px 0 6px;
    }

    .m-badge-status {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 10px;
        font-weight: 700;
        padding: 3px 9px;
        border-radius: 999px;
    }
    .m-badge-status.aman    { background: #DCFCE7; color: #166534; }
    .m-badge-status.kendala { background: #FEE2E2; color: #991B1B; }
</style>
@endpush

@section('content')

@php
    $hariMap = [
        'Sunday' => 'minggu', 'Monday' => 'senin', 'Tuesday' => 'selasa',
        'Wednesday' => 'rabu', 'Thursday' => 'kamis', 'Friday' => 'jumat', 'Saturday' => 'sabtu',
    ];
    $bulanMap = [
        1 => 'januari', 2 => 'februari', 3 => 'maret', 4 => 'april',
        5 => 'mei', 6 => 'juni', 7 => 'juli', 8 => 'agustus',
        9 => 'september', 10 => 'oktober', 11 => 'november', 12 => 'desember',
    ];
@endphp

@role('hse')
<div style="padding:14px 18px 4px;display:flex;align-items:center;justify-content:space-between">
    <span style="font-size:12px;font-weight:700;color:#4B5563">
        Riwayat Pemeriksaan K3 & Kebersihan
    </span>
    <span style="font-size:11px;font-weight:700;color:#0B2E8A;background:#EFF6FF;padding:3px 10px;border-radius:999px">
        {{ $checklists->total() }} Laporan
    </span>
</div>
@else
<div class="m-filter-bar">
    <a href="{{ route('driver.history') }}" class="m-filter-pill {{ !$type ? 'active' : '' }}">Semua</a>
    <a href="{{ route('driver.history', ['type' => 'fisik']) }}" class="m-filter-pill {{ $type === 'fisik' ? 'active' : '' }}">Cek Fisik</a>
    <a href="{{ route('driver.history', ['type' => 'k3']) }}" class="m-filter-pill {{ $type === 'k3' ? 'active' : '' }}">Cek K3 & Kebersihan</a>
</div>
@endrole

<div class="m-history-cards">
    @forelse($checklists as $row)
        @php
            $hari  = $hariMap[$row->tanggal->format('l')] ?? $row->tanggal->format('l');
            $bulan = $bulanMap[(int) $row->tanggal->format('n')] ?? $row->tanggal->format('F');
            $tglFormatted = $hari . ', ' . $row->tanggal->format('d') . ' ' . $bulan . ' ' . $row->tanggal->format('Y');
            $badCount = $row->items->where('kondisi', false)->count();
        @endphp

        <a href="{{ route('driver.history.detail', $row->id) }}" class="m-riwayat-card">
            @include('driver.partials.truck-illust', ['vehicle' => $row->vehicle, 'size' => 'sm'])

            <div style="flex:1;min-width:0">
                <div class="m-riwayat-date">{{ $tglFormatted }}</div>
                <div class="m-riwayat-plat">{{ $row->vehicle->plat_nomor ?? '-' }}</div>

                <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap">
                    <span class="m-badge-status {{ $row->status }}">
                        <i class="fa-solid {{ $row->status === 'aman' ? 'fa-circle-check' : 'fa-triangle-exclamation' }}"></i>
                        {{ $row->status === 'aman' ? 'Aman' : $badCount . ' Kendala' }}
                    </span>

                    <span style="font-size:10px;font-weight:700;color:#4B5563;background:#FFFFFF;padding:2px 8px;border-radius:999px">
                        {{ strtoupper($row->checklist_type) }}
                    </span>
                </div>
            </div>

            <i class="fa-solid fa-chevron-right" style="color:#6B7280;font-size:13px"></i>
        </a>
    @empty
        <div style="text-align:center;padding:48px 20px;color:#6B7280;font-size:13px">
            <i class="fa-solid fa-clock-rotate-left" style="font-size:32px;margin-bottom:10px;opacity:0.4;display:block"></i>
            Belum ada riwayat checklist yang tercatat.
        </div>
    @endforelse
</div>

@endsection


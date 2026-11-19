@extends('driver.layout')

@section('title', $vehicle->plat_nomor . ' — Detail Kendaraan')
@section('back_url', route('driver.index'))

@push('styles')
<style>
    /* Hero Showcase Kendaraan dengan gradasi bawah abu-abu seperti Figma */
    .m-vehicle-hero {
        position: relative;
        padding: 16px 20px 26px;
        background: linear-gradient(180deg, #FFFFFF 0%, #FFFFFF 55%, #D6D8DC 100%);
        display: flex;
        align-items: center;
        gap: 12px;
        border-bottom: 1px solid #C8CBD0;
    }

    .m-hero-truck {
        width: 52%;
        display: flex;
        justify-content: flex-start;
        margin-left: -10px;
    }

    .m-hero-details {
        width: 48%;
        display: flex;
        flex-direction: column;
    }

    .m-hero-name {
        font-size: 13.5px;
        font-weight: 700;
        color: #111827;
        line-height: 1.25;
    }

    .m-hero-plat {
        font-size: 14px;
        font-weight: 800;
        color: #111827;
        margin-bottom: 6px;
    }

    .m-hero-stats {
        font-size: 9.5px;
        color: #374151;
        line-height: 1.5;
        margin-bottom: 14px;
    }

    /* Tombol CEK FISIK & CEK K3 persis Figma */
    .m-action-buttons {
        display: flex;
        flex-direction: column;
        gap: 8px;
        width: 100%;
    }

    .m-btn-cek {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        width: 100%;
        padding: 8px 12px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 700;
        text-decoration: none;
        letter-spacing: 0.03em;
        text-transform: uppercase;
        transition: transform 0.15s, box-shadow 0.15s;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.12);
    }

    .m-btn-cek:active {
        transform: scale(0.97);
    }

    /* CEK FISIK: Abu-abu terang atau Hijau jika sudah dicek hari ini */
    .m-btn-cek.fisik {
        background: #E2E4E8;
        color: #374151;
        border: 1px solid #D1D5DB;
    }
    .m-btn-cek.fisik.done {
        background: #4CAF28;
        color: #FFFFFF;
        border-color: #3B8C1E;
    }

    /* CEK K3: Hijau terang sesuai Figma (#52B838) */
    .m-btn-cek.k3 {
        background: #52B838;
        color: #FFFFFF;
        border: 1px solid #3B8C1E;
    }

    /* Tombol Export pdf ungu sesuai Figma APP K3/HSE (#4318D1) */
    .m-btn-export-pdf {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        background: #4318D1;
        color: #FFFFFF;
        font-size: 11px;
        font-weight: 700;
        padding: 6px 14px;
        border-radius: 999px;
        text-decoration: none;
        box-shadow: 0 3px 8px rgba(67, 24, 209, 0.28);
        transition: transform 0.15s, background 0.15s;
        white-space: nowrap;
    }

    .m-btn-export-pdf:hover {
        background: #3612AC;
    }

    .m-btn-export-pdf:active {
        transform: scale(0.96);
    }

    /* Bagian Riwayat Penggunaan Kendaraan */
    .m-history-section {
        background: #FFFFFF;
        flex: 1;
    }

    .m-history-title-row {
        padding: 16px 22px 12px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
    }

    .m-history-title {
        font-size: 15px;
        font-weight: 600;
        color: #111827;
        line-height: 1.3;
    }

    .m-history-header {
        background: #D9DADC;
        padding: 9px 22px;
        display: grid;
        grid-template-columns: 1fr 1.25fr;
        font-size: 11.5px;
        font-weight: 700;
        color: #111827;
        align-items: center;
    }

    /* Timeline List Sopir & Waktu seperti di Figma */
    .m-timeline-list {
        position: relative;
        padding: 0;
    }

    .m-timeline-row {
        display: grid;
        grid-template-columns: 1fr 1.25fr;
        align-items: center;
        padding: 10px 22px;
        border-bottom: 1px solid #D1D5DB;
        text-decoration: none;
        color: inherit;
        position: relative;
        transition: background 0.15s;
    }

    .m-timeline-row:hover {
        background: #F9FAFB;
    }

    .m-driver-col {
        display: flex;
        align-items: center;
        gap: 10px;
        position: relative;
    }

    /* Garis vertikal penghubung antar avatar sesuai Figma */
    .m-timeline-row:not(:last-child) .m-driver-col::after {
        content: '';
        position: absolute;
        left: 15px;
        top: 32px;
        bottom: -22px;
        width: 4px;
        background: #27273A;
        z-index: 1;
    }

    .m-timeline-row.kendala-row:not(:last-child) .m-driver-col::after {
        background: #DC2626;
    }

    .m-driver-avatar {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        object-fit: cover;
        position: relative;
        z-index: 2;
        border: 2px solid #27273A;
        background: #E5E7EB;
        flex-shrink: 0;
    }

    .m-timeline-row.kendala-row .m-driver-avatar {
        border-color: #DC2626;
    }

    .m-driver-name {
        font-size: 11.5px;
        font-weight: 600;
        color: #111827;
        line-height: 1.2;
    }

    .m-driver-nik {
        font-size: 9.5px;
        color: #4B5563;
        margin-top: 1px;
    }

    .m-time-col {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 6px;
    }

    .m-date-text {
        font-size: 11.5px;
        font-weight: 600;
        color: #111827;
        line-height: 1.25;
    }

    .m-year-sub {
        font-size: 9.5px;
        color: #4B5563;
        display: block;
        margin-top: 1px;
    }

    .m-type-pill {
        font-size: 9.5px;
        font-weight: 700;
        padding: 2px 7px;
        border-radius: 999px;
        text-transform: uppercase;
        flex-shrink: 0;
    }
    .m-type-pill.fisik { background: #DBEAFE; color: #1E40AF; }
    .m-type-pill.k3    { background: #DCFCE7; color: #166534; }
</style>
@endpush

@section('content')

{{-- Search Bar di atas (sesuai Figma) --}}
<div class="m-search-wrap">
    <form action="{{ route('driver.index') }}" method="GET" class="m-search-box">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input
            type="text"
            name="q"
            class="m-search-input"
            placeholder="Cari kendaraan...">
    </form>
</div>

{{-- Showcase Kendaraan + Tombol CEK FISIK & CEK K3 --}}
<div class="m-vehicle-hero">
    <div class="m-hero-truck">
        @include('driver.partials.truck-illust', ['vehicle' => $vehicle, 'size' => 'lg'])
    </div>

    <div class="m-hero-details">
        <div class="m-hero-name">
            {{ $vehicle->merk }} {{ str_contains(strtolower($vehicle->merk), 'box') ? '' : $vehicle->jenis_kendaraan }}
        </div>
        <div class="m-hero-plat">{{ strtoupper($vehicle->plat_nomor) }}</div>

        <div class="m-hero-stats">
            <div>Jumlah servis berkala : <strong>{{ $vehicle->servis_count }}</strong></div>
            <div>Jumlah kendala : <strong>{{ $vehicle->kendala_count }}</strong></div>
        </div>

        <div class="m-action-buttons">
            @role('hse')
                {{-- Role K3/HSE: Hanya bisa CEK K3 sesuai Figma Node 997:6317 --}}
                <a href="{{ route('driver.checklist.form', [$vehicle->id, 'k3']) }}"
                   class="m-btn-cek k3">
                    @if($todayK3)
                        <i class="fa-solid fa-circle-check"></i>
                    @endif
                    CEK K3
                </a>
            @else
                {{-- Role Driver: Tetap menampilkan CEK FISIK dan CEK K3 --}}
                @php
                    $isRecheckToday = $todayFisik && $todayFisik->status === 'perbaikan' && $vehicle->status === 'siap_jalan';
                    $isRepairToday  = $todayFisik && $todayFisik->status === 'perbaikan' && $vehicle->status !== 'siap_jalan';
                @endphp
                <a href="{{ route('driver.checklist.form', [$vehicle->id, 'fisik']) }}"
                   class="m-btn-cek fisik {{ ($todayFisik && !$isRecheckToday && !$isRepairToday) ? 'done' : '' }}"
                   @if($isRecheckToday)
                       style="background:#2563EB;color:#FFFFFF;border-color:#1D4ED8;"
                   @elseif($isRepairToday)
                       style="background:#FEF3C7;color:#B45309;border-color:#F59E0B;"
                   @endif>
                    @if($isRecheckToday)
                        <i class="fa-solid fa-rotate"></i>
                        LAKUKAN PEMERIKSAAN ULANG
                    @elseif($isRepairToday)
                        <i class="fa-solid fa-screwdriver-wrench"></i>
                        DALAM PERBAIKAN
                    @else
                        @if($todayFisik)
                            <i class="fa-solid fa-circle-check"></i>
                        @endif
                        CEK FISIK
                    @endif
                </a>

                <a href="{{ route('driver.checklist.form', [$vehicle->id, 'k3']) }}"
                   class="m-btn-cek k3">
                    @if($todayK3)
                        <i class="fa-solid fa-circle-check"></i>
                    @endif
                    CEK K3
                </a>
            @endrole
        </div>
    </div>
</div>

{{-- Riwayat Penggunaan Kendaraan sesuai Figma --}}
<div class="m-history-section">
    <div class="m-history-title-row">
        <div class="m-history-title">
            Riwayat Penggunaan<br>Kendaraan
        </div>

        @role('hse')
            <a href="{{ route('driver.vehicle.export-pdf', $vehicle->id) }}"
               target="_blank"
               class="m-btn-export-pdf">
                Export pdf
            </a>
        @endrole
    </div>

    <div class="m-history-header">
        <span>Sopir</span>
        <span style="display:inline-flex;align-items:center;gap:4px">
            Waktu
            <i class="fa-solid fa-sort" style="font-size:10px;color:#6B7280"></i>
        </span>
    </div>

    <div class="m-timeline-list">
        @php
            $hariMap = [
                'Sunday'    => 'Minggu',
                'Monday'    => 'Senin',
                'Tuesday'   => 'Selasa',
                'Wednesday' => 'Rabu',
                'Thursday'  => 'Kamis',
                'Friday'    => 'Jumat',
                'Saturday'  => 'Sabtu',
            ];
            $bulanMap = [
                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
            ];
        @endphp

        @forelse($usageHistory as $log)
            @php
                $driverUser = $log->driver ?? $log->user;
                $hariIndo   = $hariMap[$log->tanggal->format('l')] ?? $log->tanggal->format('l');
                $bulanIndo  = $bulanMap[(int) $log->tanggal->format('n')] ?? $log->tanggal->format('F');
                $tglStr     = $hariIndo . ', ' . $log->tanggal->format('d') . ' ' . $bulanIndo;
            @endphp

            <a href="{{ route('driver.history.detail', $log->id) }}"
               class="m-timeline-row {{ $log->status === 'kendala' ? 'kendala-row' : '' }}">

                <div class="m-driver-col">
                    <img src="{{ $driverUser?->avatar_url ?? 'https://ui-avatars.com/api/?name=Driver&background=0F1E36&color=fff' }}"
                         alt="Sopir"
                         class="m-driver-avatar">
                    <div>
                        <div class="m-driver-name">{{ $driverUser?->name ?? 'Driver' }}</div>
                        <div class="m-driver-nik">{{ $driverUser?->employee_id ?? $driverUser?->username ?? '-' }}</div>
                    </div>
                </div>

                <div class="m-time-col">
                    <div>
                        <div class="m-date-text">{{ $tglStr }}</div>
                        <span class="m-year-sub">
                            {{ $log->tanggal->format('Y') }} &bull; {{ $log->tanggal->format('H:i') }} WIB
                        </span>
                    </div>
                    <span class="m-type-pill {{ $log->checklist_type }}">
                        {{ $log->checklist_type }}
                    </span>
                </div>
            </a>
        @empty
            <div style="text-align:center;padding:36px 20px;color:#6B7280;font-size:12.5px">
                <i class="fa-regular fa-clock" style="font-size:24px;margin-bottom:8px;display:block;opacity:0.5"></i>
                Belum ada riwayat pemeriksaan untuk unit <strong>{{ $vehicle->plat_nomor }}</strong>.
                <div style="margin-top:6px;font-size:11.5px">
                    Klik tombol <strong>{{ auth()->user()->hasRole('hse') ? 'CEK K3' : 'CEK FISIK' }}</strong> di atas untuk memulai laporan.
                </div>
            </div>
        @endforelse
    </div>

    @role('hse')
        @if($usageHistory->count() > 0)
            <div style="padding:12px 22px 24px;display:flex;justify-content:flex-end">
                <a href="{{ route('driver.vehicle.export-pdf', $vehicle->id) }}"
                   target="_blank"
                   class="m-btn-export-pdf">
                    Export pdf
                </a>
            </div>
        @endif
    @endrole
</div>

@endsection


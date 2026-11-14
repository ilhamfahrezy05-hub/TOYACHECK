@extends('layouts.app')

@section('title', 'Dashboard')

@if(auth()->user()->hasRole('mekanik'))
    @section('page-title', 'Rekap Perbaikan')
    @section('page-subtitle', 'Monitoring persediaan suku cadang bengkel (ABC), rekap servis armada, dan tren perbaikan dari database')
@else
    @section('page-title', $greetingTime . ' MOUNTOYA JAYA JAYA LOGISTIK JAYA')
    @section('page-subtitle', 'Berikut ringkasan kondisi armada terbaru!')
@endif

@push('styles')
<style>
    /* =============================================
       DASHBOARD STYLES — UNIFIED WEB DESKTOP
    ============================================= */
    .dash-stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 18px;
        margin-bottom: 24px;
    }
    @media (max-width: 1024px) {
        .dash-stats-grid { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 640px) {
        .dash-stats-grid { grid-template-columns: 1fr; }
    }

    .dash-stat-card {
        background: #FFFFFF;
        border-radius: 14px;
        padding: 20px 22px;
        display: flex;
        align-items: center;
        gap: 16px;
        border: 1px solid var(--border);
        box-shadow: 0 2px 8px rgba(15,30,54,0.03);
        transition: transform .2s, box-shadow .2s;
    }
    .dash-stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(15,30,54,0.06);
    }
    .dash-stat-card.card-sehat {
        border-color: #86EFAC;
        background: linear-gradient(180deg, #FFFFFF 0%, #F0FDF4 100%);
    }
    .dash-stat-card.card-rusak {
        border-color: #FCA5A5;
        background: linear-gradient(180deg, #FFFFFF 0%, #FEF2F2 100%);
    }
    .dash-stat-card.card-repair {
        border-color: #DDD6FE;
        background: linear-gradient(180deg, #FFFFFF 0%, #FAF5FF 100%);
    }

    .dash-stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        flex-shrink: 0;
    }
    .dash-stat-icon.blue   { background: #EFF6FF; color: #2563EB; }
    .dash-stat-icon.green  { background: #DCFCE7; color: #16A34A; }
    .dash-stat-icon.red    { background: #FEE2E2; color: #DC2626; }
    .dash-stat-icon.purple { background: #F3E8FF; color: #9333EA; }

    .dash-stat-info { flex: 1; min-width: 0; }
    .dash-stat-label {
        font-size: 13px;
        color: var(--text-secondary);
        font-weight: 500;
        margin-bottom: 2px;
    }
    .dash-stat-value {
        font-size: 28px;
        font-weight: 800;
        color: var(--text-primary);
        line-height: 1.1;
    }

    /* ── DUA WIDGET PEMANTAUAN (FISIK & K3) ── */
    .dash-two-cols {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-bottom: 24px;
    }
    @media (max-width: 900px) {
        .dash-two-cols { grid-template-columns: 1fr; }
    }

    .dash-widget-card {
        background: #FFFFFF;
        border: 1px solid var(--border);
        border-radius: 14px;
        padding: 20px;
        box-shadow: 0 2px 8px rgba(15,30,54,0.03);
        min-height: 220px;
        display: flex;
        flex-direction: column;
    }
    .dash-widget-header {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 16px;
    }
    .dash-widget-badge {
        width: 30px;
        height: 30px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
    }
    .dash-widget-badge.blue  { background: #EFF6FF; color: #2563EB; }
    .dash-widget-badge.green { background: #DCFCE7; color: #16A34A; }
    .dash-widget-title {
        font-size: 15px;
        font-weight: 700;
        color: var(--text-primary);
    }

    .dash-issue-row {
        background: #DC2626;
        color: #FFFFFF;
        border-radius: 8px;
        padding: 10px 16px;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-size: 13px;
        font-weight: 600;
        box-shadow: 0 2px 6px rgba(220,38,38,0.25);
        transition: transform .15s;
    }
    .dash-issue-row:hover { transform: scale(1.01); }
    .dash-issue-row:last-child { margin-bottom: 0; }
    .dash-issue-plat {
        font-family: monospace;
        letter-spacing: .05em;
        font-weight: 700;
    }
    .dash-issue-part {
        background: rgba(255,255,255,0.2);
        padding: 2px 10px;
        border-radius: 4px;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .04em;
    }
    .dash-issue-type { font-size: 12px; opacity: .9; }

    .dash-empty-widget {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 24px 10px;
        text-align: center;
        flex: 1;
        color: var(--text-muted);
    }
    .dash-empty-widget i {
        font-size: 42px;
        color: #CBD5E1;
        margin-bottom: 10px;
    }
    .dash-empty-widget span {
        font-size: 13px;
        color: var(--text-secondary);
        font-weight: 500;
    }

    /* ── TABEL DAFTAR INSPEKSI ── */
    .dash-table-card {
        background: #FFFFFF;
        border: 1px solid var(--border);
        border-radius: 14px;
        box-shadow: 0 2px 8px rgba(15,30,54,0.03);
        overflow: hidden;
        margin-bottom: 24px;
    }
    .dash-table-header {
        padding: 18px 22px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-bottom: 1px solid var(--border);
    }
    .dash-table-title {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 15px;
        font-weight: 700;
        color: var(--text-primary);
    }
    .dash-table-title i { color: var(--primary); }

    .badge-done {
        background: #22C55E;
        color: #FFFFFF;
        font-weight: 700;
        font-size: 11px;
        padding: 4px 14px;
        border-radius: 999px;
        display: inline-block;
        letter-spacing: .03em;
    }
    .badge-undone {
        background: #EF4444;
        color: #FFFFFF;
        font-weight: 700;
        font-size: 11px;
        padding: 4px 14px;
        border-radius: 999px;
        display: inline-block;
        letter-spacing: .03em;
    }

    /* ── MODUL REKAP PERBAIKAN & PERSEDIAAN MEKANIK (FIGMA INTEGRATED) ── */
    .mk-panel-card {
        background: #FFFFFF;
        border: 1px solid var(--border);
        border-radius: 14px;
        padding: 24px 26px;
        box-shadow: 0 2px 8px rgba(15,30,54,0.03);
        margin-bottom: 24px;
    }

    .mk-persediaan-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 16px;
        flex-wrap: wrap;
        gap: 14px;
    }

    .mk-sec-title {
        font-size: 16px;
        font-weight: 800;
        color: #091E3A;
    }

    .mk-cat-tabs {
        display: inline-flex;
        align-items: center;
        background: #F1F4F9;
        padding: 4px;
        border-radius: 10px;
        gap: 4px;
    }

    .mk-cat-tab {
        padding: 6px 14px;
        border-radius: 7px;
        font-size: 12px;
        font-weight: 700;
        color: #64748B;
        text-decoration: none;
        transition: all 0.15s ease;
    }

    .mk-cat-tab:hover { color: #091E3A; }
    .mk-cat-tab.active {
        background: #FFFFFF;
        color: #091E3A;
        box-shadow: 0 2px 6px rgba(15, 30, 54, 0.08);
    }

    .mk-kritis-pill {
        position: relative;
        display: inline-flex;
        align-items: center;
        gap: 14px;
        background: #F7B7B7;
        padding: 12px 26px;
        border-radius: 20px;
        color: #FFFFFF;
        text-decoration: none;
        box-shadow: 0 6px 16px rgba(220, 38, 38, 0.14);
    }

    .mk-kritis-pill-label {
        font-size: 11.5px;
        font-weight: 800;
        letter-spacing: 0.05em;
        color: #FFFFFF;
        text-transform: uppercase;
    }

    .mk-kritis-pill-num {
        font-size: 22px;
        font-weight: 900;
        color: #DC2626;
        line-height: 1;
    }

    .mk-kritis-bang {
        position: absolute;
        top: -8px;
        right: 10px;
        font-size: 18px;
        font-weight: 900;
        color: #DC2626;
    }

    .mk-abc-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 28px;
        height: 20px;
        padding: 0 9px;
        border-radius: 999px;
        font-size: 10.5px;
        font-weight: 800;
    }
    .mk-abc-a { background: #0F2942; color: #FFFFFF; }
    .mk-abc-b { background: #334155; color: #FFFFFF; }
    .mk-abc-c { background: #E2E8F0; color: #475569; }

    .mk-status-normal {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 12px;
        font-weight: 700;
        color: #334155;
    }
    .mk-status-normal::before {
        content: '';
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #0F2942;
    }

    .mk-status-low {
        display: inline-flex;
        align-items: center;
        padding: 4px 12px;
        border-radius: 999px;
        background: #FDE2E2;
        color: #B91C1C;
        font-size: 11px;
        font-weight: 800;
    }

    .mk-kendala-pill {
        display: inline-block;
        padding: 4px 11px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 700;
    }
    .mk-kendala-gray  { background: #E2E8F0; color: #334155; }
    .mk-kendala-red   { background: #FEE2E2; color: #DC2626; }
    .mk-kendala-amber { background: #FFEDD5; color: #C2410C; }

    .mk-trend-card {
        background: #EFECE9;
        border-radius: 12px;
        padding: 24px 30px;
        margin-top: 18px;
    }

    .mk-trend-title {
        font-size: 14px;
        font-weight: 800;
        color: #091E3A;
        letter-spacing: 0.02em;
        text-transform: uppercase;
    }

    .mk-trend-sub {
        font-size: 11.5px;
        color: #64748B;
        font-weight: 600;
        margin-top: 2px;
        margin-bottom: 20px;
    }

    .mk-trend-row { margin-bottom: 16px; }
    .mk-trend-track {
        width: 100%;
        height: 9px;
        background: #E4E2DF;
        border-radius: 999px;
        overflow: hidden;
        margin-bottom: 6px;
    }
    .mk-trend-fill { height: 100%; border-radius: 999px; }
    .mk-trend-meta {
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-size: 12.5px;
        font-weight: 700;
        color: #1E293B;
    }
</style>
@endpush

@section('content')

@role('mekanik')
{{-- =========================================================================
     TAMPILAN DASHBOARD KHUSUS ROLE MEKANIK (FIGMA NODE 917-5235)
     Hanya menampilkan Persediaan Sparepart (ABC), Rekap Armada, & Tren Perbaikan
========================================================================= --}}
<div class="mk-panel-card">
    {{-- 1. Persediaan Sparepart --}}
    <div class="mk-persediaan-bar">
        <div class="mk-sec-title">Persediaan</div>

        <div class="mk-cat-tabs">
            <a href="{{ route('dashboard') }}" class="mk-cat-tab {{ empty($kat) ? 'active' : '' }}">Semua</a>
            <a href="{{ route('dashboard', ['kat' => 'A']) }}" class="mk-cat-tab {{ $kat === 'A' ? 'active' : '' }}">Kat. A</a>
            <a href="{{ route('dashboard', ['kat' => 'B']) }}" class="mk-cat-tab {{ $kat === 'B' ? 'active' : '' }}">Kat. B</a>
            <a href="{{ route('dashboard', ['kat' => 'C']) }}" class="mk-cat-tab {{ $kat === 'C' ? 'active' : '' }}">Kat. C</a>
        </div>

        <a href="{{ route('mekanik.inventory') }}" class="mk-kritis-pill">
            <span class="mk-kritis-bang">!</span>
            <span class="mk-kritis-pill-label">STOK KRITIS</span>
            <span class="mk-kritis-pill-num">{{ str_pad($criticalCount, 2, '0', STR_PAD_LEFT) }}</span>
        </a>
    </div>

    <div class="table-wrap" style="border:1px solid var(--border);border-radius:10px;overflow:hidden;margin-bottom:10px">
        <table class="tc-table">
            <thead>
                <tr>
                    <th>NAMA SPAREPART</th>
                    <th style="text-align:center">KATEGORI (ABC)</th>
                    <th style="text-align:center">STOK</th>
                    <th style="text-align:center">BATAS MIN.</th>
                    <th>STATUS</th>
                </tr>
            </thead>
            <tbody>
                @forelse($persediaan as $sp)
                    @php
                        $isLow = $sp->stok <= $sp->minimal_stok;
                        $abc   = strtoupper($sp->kategori_abc ?: 'A');
                        $abcClass = match($abc) {
                            'A' => 'mk-abc-a',
                            'B' => 'mk-abc-b',
                            default => 'mk-abc-c',
                        };
                    @endphp
                    <tr>
                        <td style="font-weight:700;color:#0F172A">
                            {{ $sp->nama_sparepart }}
                            <div style="font-size:11px;color:var(--text-muted);font-weight:500">{{ $sp->kode_sparepart }}</div>
                        </td>
                        <td style="text-align:center">
                            <span class="mk-abc-badge {{ $abcClass }}">{{ $abc }}</span>
                        </td>
                        <td style="text-align:center;font-weight:800;font-size:14px;color:{{ $isLow ? '#B91C1C' : '#091E3A' }}">
                            {{ str_pad($sp->stok, 2, '0', STR_PAD_LEFT) }}
                        </td>
                        <td style="text-align:center;color:#64748B;font-weight:600">
                            {{ str_pad($sp->minimal_stok, 2, '0', STR_PAD_LEFT) }}
                        </td>
                        <td>
                            @if($isLow)
                                <span class="mk-status-low">Stok Rendah</span>
                            @else
                                <span class="mk-status-normal">Normal</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align:center;padding:24px;color:#64748B">Belum ada data suku cadang pada kategori ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="display:flex;justify-content:flex-end;margin-bottom:22px">
        <a href="{{ route('mekanik.inventory') }}" style="font-size:12px;font-weight:700;color:#2563EB;text-decoration:none">
            View all Inventory &gt;
        </a>
    </div>

    {{-- 2. Rekap Armada (Riwayat Perbaikan) --}}
    <div class="mk-sec-title" style="margin-bottom:12px">Rekap armada</div>

    <div class="table-wrap" style="border:1px solid var(--border);border-radius:10px;overflow:hidden;margin-bottom:10px">
        <table class="tc-table">
            <thead>
                <tr>
                    <th>TANGGAL</th>
                    <th>UNIT</th>
                    <th>DRIVER / MEKANIK</th>
                    <th>KENDALA</th>
                    <th>TINDAKAN</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rekapArmada as $idx => $log)
                    @php
                        $kendalaText  = \Illuminate\Support\Str::limit($log->deskripsi_masalah, 34);
                        $lowerKendala = strtolower($log->deskripsi_masalah);
                        if (str_contains($lowerKendala, 'blong') || str_contains($lowerKendala, 'mati') || str_contains($lowerKendala, 'rusak')) {
                            $pillClass = 'mk-kendala-red';
                        } elseif (str_contains($lowerKendala, 'klakson') || str_contains($lowerKendala, 'drakling') || str_contains($lowerKendala, 'ban')) {
                            $pillClass = 'mk-kendala-amber';
                        } else {
                            $pillClass = 'mk-kendala-gray';
                        }
                    @endphp
                    <tr>
                        <td style="font-size:13px;color:#334155;font-weight:600">
                            {{ $log->tanggal_maintenance->format('d/m/Y') }}
                        </td>
                        <td style="font-weight:800;color:#091E3A;font-size:13px">
                            {{ strtoupper($log->vehicle->plat_nomor ?? '—') }}
                        </td>
                        <td style="font-weight:600;color:#334155;font-size:13px">
                            {{ $log->nama_driver ?: ($log->mekanik->name ?? '—') }}
                        </td>
                        <td>
                            <span class="mk-kendala-pill {{ $pillClass }}">
                                {{ $kendalaText }}
                            </span>
                        </td>
                        <td style="font-size:13px;color:#475569">
                            {{ $log->solusi ?: 'Pemeriksaan & penanganan berkala' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align:center;padding:24px;color:#64748B">Belum ada rekap perbaikan armada di database.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="display:flex;justify-content:flex-end">
        <a href="{{ route('mekanik.repairs') }}" style="font-size:12px;font-weight:700;color:#2563EB;text-decoration:none">
            LIHAT SEMUA LOG PERBAIKAN &gt;
        </a>
    </div>

    {{-- 3. Tren Perbaikan Teratas (100% dari Database) --}}
    <div class="mk-trend-card">
        <div class="mk-trend-title">TREN PERBAIKAN TERATAS</div>
        <div class="mk-trend-sub">{{ $trends['month_label'] }} · Berdasarkan {{ $trends['total_logs'] ?? 0 }} catatan perbaikan di database</div>

        @foreach($trends['items'] as $tr)
            <div class="mk-trend-row">
                <div class="mk-trend-track">
                    <div class="mk-trend-fill" style="width: {{ $tr['percent'] }}%; background: {{ $tr['color'] }};"></div>
                </div>
                <div class="mk-trend-meta">
                    <span>{{ $tr['label'] }} ({{ $tr['count'] ?? 0 }} kasus)</span>
                    <span style="color:#091E3A;font-weight:800">{{ $tr['percent'] }}%</span>
                </div>
            </div>
        @endforeach

        <div style="display:flex;align-items:center;gap:12px;margin-top:20px">
            <div style="width:24px;height:24px;border-radius:6px;background:#FCD5D5;color:#B91C1C;display:flex;align-items:center;justify-content:center;font-weight:900;font-size:13px">!</div>
            <div style="font-size:12.5px;font-weight:700;color:#1E293B">
                Masalah terbanyak pada periode {{ strtolower($trends['month_label']) }} terdeteksi pada kategori <strong>{{ $trends['top_category'] }}</strong>
            </div>
        </div>
    </div>
</div>

@else
{{-- =========================================================================
     TAMPILAN DASHBOARD KHUSUS ROLE ADMIN DISTRIBUSI
     Hanya menampilkan 4 KPI Checklist/Armada, Checklist Fisik & K3, & Daftar Inspeksi
========================================================================= --}}

{{-- ── 1. TOP STAT CARDS (4 KARTU UTAMA ARMADA & CHECKLIST) ── --}}
<div class="dash-stats-grid">

    <div class="dash-stat-card">
        <div class="dash-stat-icon blue">
            <i class="fa-solid fa-file-lines"></i>
        </div>
        <div class="dash-stat-info">
            <div class="dash-stat-label">Total Checklist</div>
            <div class="dash-stat-value">{{ $stats['total_checklist'] }}</div>
        </div>
    </div>

    <div class="dash-stat-card card-sehat">
        <div class="dash-stat-icon green">
            <i class="fa-solid fa-circle-check"></i>
        </div>
        <div class="dash-stat-info">
            <div class="dash-stat-label">Armada Sehat</div>
            <div class="dash-stat-value">{{ $stats['armada_sehat'] }}</div>
        </div>
    </div>

    <div class="dash-stat-card card-rusak">
        <div class="dash-stat-icon red">
            <i class="fa-solid fa-triangle-exclamation"></i>
        </div>
        <div class="dash-stat-info">
            <div class="dash-stat-label">Tidak Layak Jalan</div>
            <div class="dash-stat-value">{{ $stats['tidak_layak_jalan'] }}</div>
        </div>
    </div>

    <div class="dash-stat-card card-repair">
        <div class="dash-stat-icon purple">
            <i class="fa-solid fa-wrench"></i>
        </div>
        <div class="dash-stat-info">
            <div class="dash-stat-label">Sedang Diperbaiki</div>
            <div class="dash-stat-value">{{ $stats['sedang_diperbaiki'] }}</div>
        </div>
    </div>

</div>

{{-- ── 2. TWO MONITORING WIDGETS (CHECKLIST FISIK & CHECKLIST K3) ── --}}
<div class="dash-two-cols">

    <div class="dash-widget-card">
        <div class="dash-widget-header">
            <div class="dash-widget-badge blue">
                <i class="fa-solid fa-truck"></i>
            </div>
            <div class="dash-widget-title">Checklist Fisik</div>
        </div>

        @if($kendalaFisik->isNotEmpty())
            <div style="display:flex;flex-direction:column;gap:8px">
                @foreach($kendalaFisik as $cf)
                    @php
                        $partNames = $cf->items->pluck('item')->take(2)->join(', ');
                        if ($cf->items->count() > 2) {
                            $partNames .= ' +' . ($cf->items->count() - 2);
                        }
                    @endphp
                    <a href="{{ route('checklists.show', $cf->id) }}" class="dash-issue-row" style="text-decoration:none">
                        <span class="dash-issue-plat">{{ strtoupper($cf->vehicle->plat_nomor ?? '—') }}</span>
                        <span class="dash-issue-part">{{ $partNames ?: 'Kendala Fisik' }}</span>
                        <span class="dash-issue-type">{{ $cf->vehicle->jenis_kendaraan ?? $cf->vehicle->merk ?? 'Armada' }}</span>
                    </a>
                @endforeach
            </div>
        @else
            <div class="dash-empty-widget">
                <i class="fa-solid fa-circle-check" style="color:#22C55E"></i>
                <span>Semua armada dalam kondisi fisik prima</span>
                <span style="font-size:11px;color:var(--text-muted);margin-top:2px">Tidak ditemukan laporan kendala fisik hari ini</span>
            </div>
        @endif
    </div>

    <div class="dash-widget-card">
        <div class="dash-widget-header">
            <div class="dash-widget-badge green">
                <i class="fa-solid fa-shield-halved"></i>
            </div>
            <div class="dash-widget-title">Checklist K3</div>
        </div>

        @if($kendalaK3->isNotEmpty())
            <div style="display:flex;flex-direction:column;gap:8px">
                @foreach($kendalaK3 as $ck3)
                    @php
                        $k3Parts = $ck3->items->pluck('item')->take(2)->join(', ');
                        if ($ck3->items->count() > 2) {
                            $k3Parts .= ' +' . ($ck3->items->count() - 2);
                        }
                    @endphp
                    <a href="{{ route('checklists.show', $ck3->id) }}" class="dash-issue-row" style="background:#B91C1C;text-decoration:none">
                        <span class="dash-issue-plat">{{ strtoupper($ck3->vehicle->plat_nomor ?? '—') }}</span>
                        <span class="dash-issue-part">{{ $k3Parts ?: 'Kendala K3' }}</span>
                        <span class="dash-issue-type">{{ $ck3->vehicle->jenis_kendaraan ?? 'Unit' }}</span>
                    </a>
                @endforeach
            </div>
        @else
            <div class="dash-empty-widget">
                <i class="fa-regular fa-clipboard"></i>
                <span>Tidak ada data saat ini</span>
                <span style="font-size:11px;color:var(--text-muted);margin-top:2px">Seluruh standar K3 & kebersihan terpenuhi</span>
            </div>
        @endif
    </div>

</div>

{{-- ── 3. TABEL DAFTAR INSPEKSI TERBARU ── --}}
<div class="dash-table-card">
    <div class="dash-table-header" style="flex-wrap:wrap;gap:12px">
        <div class="dash-table-title">
            <i class="fa-solid fa-table-list"></i>
            <span>Daftar Inspeksi Terbaru</span>
            @if($tanggalFilter)
                <span class="badge badge-info" style="font-size:11px;font-weight:600;margin-left:6px">
                    <i class="fa-regular fa-calendar"></i> {{ \Carbon\Carbon::parse($tanggalFilter)->translatedFormat('d M Y') }}
                </span>
            @endif
        </div>

        <form method="GET" action="{{ route('dashboard') }}" class="d-flex align-center gap-8" id="filterDateForm" style="margin-left:auto">
            <div style="display:flex;align-items:center;gap:6px;background:#F8FAFC;padding:3px 8px;border-radius:8px;border:1px solid var(--border)">
                <i class="fa-regular fa-calendar" style="color:var(--text-secondary);font-size:12px"></i>
                <input
                    type="date"
                    name="tanggal"
                    id="dashTanggalInput"
                    value="{{ $tanggalFilter }}"
                    class="form-control"
                    style="border:none;background:transparent;padding:4px 6px;font-size:12px;height:28px;width:130px;outline:none;box-shadow:none"
                    title="Pilih tanggal inspeksi"
                    onchange="this.form.submit()">
            </div>

            @if($tanggalFilter)
                <a href="{{ route('dashboard') }}" class="btn btn-secondary btn-sm" title="Hapus Filter Tanggal" style="height:34px;padding:0 10px;font-size:12px">
                    <i class="fa-solid fa-xmark"></i> Reset
                </a>
            @else
                <button type="button" class="btn btn-secondary btn-sm" style="font-size:12px;height:34px" onclick="document.getElementById('dashTanggalInput').value='{{ now()->toDateString() }}'; document.getElementById('filterDateForm').submit();" title="Filter checklist hari ini">
                    <i class="fa-regular fa-calendar-check"></i> Hari Ini
                </button>
            @endif

            <a href="{{ route('checklists.index') }}" class="btn btn-secondary btn-sm" style="font-size:12px;font-weight:600;height:34px" title="Buka Semua Riwayat Inspeksi">
                <i class="fa-solid fa-arrow-up-right-from-square"></i>
                Semua
            </a>
        </form>
    </div>

    <div class="table-wrap">
        <table class="tc-table">
            <thead>
                <tr style="background:#F8FAFC">
                    <th>NOMOR PLAT</th>
                    <th>SUPIR</th>
                    <th>TIPE MOBIL</th>
                    <th style="text-align:center">WAKTU</th>
                    <th style="text-align:center">STATUS</th>
                    <th style="text-align:center">KELAYAKAN</th>
                    <th style="width:40px;text-align:center"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentInspections as $ins)
                @php
                    $isAman      = $ins->status === 'aman';
                    $totalItems  = $ins->items->count();
                    $baikItems   = $ins->items->where('kondisi', true)->count();
                    $kelayakan   = $totalItems > 0 ? round(($baikItems / $totalItems) * 100) : 100;
                    $driverNama  = $ins->driver->name ?? $ins->user->name ?? '—';
                @endphp
                <tr>
                    <td>
                        <span style="background:#0F1E36;color:#fff;padding:2px 8px;border-radius:4px;font-size:11px;font-weight:700;letter-spacing:.08em;font-family:monospace">
                            {{ strtoupper($ins->vehicle->plat_nomor ?? '-') }}
                        </span>
                    </td>
                    <td style="font-weight:600;color:var(--text-primary)">
                        {{ $driverNama }}
                    </td>
                    <td style="color:var(--text-secondary)">
                        {{ $ins->vehicle->jenis_kendaraan ?? $ins->vehicle->merk ?? 'Armada' }}
                    </td>
                    <td style="text-align:center;font-weight:600;font-size:12px">
                        {{ $ins->tanggal->format('H.i') }}
                    </td>
                    <td style="text-align:center">
                        @if($isAman)
                            <span class="badge-done">Done</span>
                        @else
                            <span class="badge-undone">Undone</span>
                        @endif
                    </td>
                    <td style="text-align:center;font-weight:700;font-size:13px">
                        <span style="color:{{ $kelayakan >= 80 ? 'var(--success)' : ($kelayakan >= 50 ? 'var(--warning)' : 'var(--danger)') }}">
                            {{ $kelayakan }}%
                        </span>
                    </td>
                    <td style="text-align:center">
                        <a href="{{ route('checklists.show', $ins->id) }}"
                           class="btn btn-secondary btn-sm btn-icon"
                           style="width:26px;height:26px;font-size:11px"
                           title="Lihat Detail">
                            <i class="fa-solid fa-ellipsis-vertical"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7">
                        <div class="empty-state" style="padding:36px 20px">
                            <div class="empty-icon"><i class="fa-solid fa-clipboard-list"></i></div>
                            <div class="empty-title">Belum Ada Inspeksi Tercatat</div>
                            <div class="empty-text">Data inspeksi harian oleh supir atau tim HSE akan muncul di sini.</div>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endrole

@endsection

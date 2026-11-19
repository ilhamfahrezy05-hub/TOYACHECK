@extends('layouts.app')

@section('title', 'Riwayat Checklist')
@section('page-title', 'Riwayat Checklist')
@section('page-subtitle', 'Arsip seluruh pemeriksaan armada, bisa difilter dan diexport')

@section('page-actions')
    <a href="{{ route('riwayat.export-pdf', $filters) }}" target="_blank" class="btn btn-primary" id="btn-export-pdf-riwayat">
        <i class="fa-solid fa-file-pdf"></i>
        Export PDF
    </a>
    <a href="{{ route('riwayat.export', $filters) }}" class="btn btn-secondary" id="btn-export-riwayat">
        <i class="fa-solid fa-file-csv"></i>
        Export CSV
    </a>
@endsection

@section('content')

{{-- ── RINGKASAN (mengikuti filter aktif) ── --}}
<div class="stats-grid" style="grid-template-columns: repeat(3, 1fr); margin-bottom: 24px;">

    <div class="stat-card">
        <div class="stat-icon blue"><i class="fa-solid fa-clipboard-list"></i></div>
        <div class="stat-info">
            <div class="stat-label">Total Pemeriksaan</div>
            <div class="stat-value">{{ $stats['total'] }}</div>
            <div class="stat-change">Sesuai filter aktif</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon green"><i class="fa-solid fa-circle-check"></i></div>
        <div class="stat-info">
            <div class="stat-label">Aman</div>
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

</div>

{{-- ── FILTER ── --}}
<div class="card" style="margin-bottom: 20px;">
    <form method="GET" action="{{ route('riwayat.index') }}" id="formFilterRiwayat"
          style="padding:16px 20px;display:grid;grid-template-columns:repeat(5,1fr) auto;gap:12px;align-items:end">

        <div>
            <label class="form-label" for="f_dari">Dari Tanggal</label>
            <input type="date" id="f_dari" name="dari" class="form-control" value="{{ $filters['dari'] ?? '' }}">
        </div>

        <div>
            <label class="form-label" for="f_sampai">Sampai Tanggal</label>
            <input type="date" id="f_sampai" name="sampai" class="form-control" value="{{ $filters['sampai'] ?? '' }}">
        </div>

        <div>
            <label class="form-label" for="f_vehicle">Armada</label>
            <select id="f_vehicle" name="vehicle_id" class="form-control">
                <option value="">Semua Armada</option>
                @foreach($vehicles ?? [] as $v)
                    <option value="{{ $v->id }}" {{ (string)($filters['vehicle_id'] ?? '') === (string)$v->id ? 'selected' : '' }}>
                        {{ strtoupper($v->plat_nomor) }} ({{ $v->merk }})
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="form-label" for="f_type">Jenis</label>
            <select id="f_type" name="type" class="form-control">
                <option value="">Semua Jenis</option>
                <option value="fisik" {{ ($filters['type'] ?? '') === 'fisik' ? 'selected' : '' }}>Fisik</option>
                <option value="k3" {{ ($filters['type'] ?? '') === 'k3' ? 'selected' : '' }}>K3 &amp; Kebersihan</option>
            </select>
        </div>

        <div>
            <label class="form-label" for="f_status">Status</label>
            <select id="f_status" name="status" class="form-control">
                <option value="">Semua Status</option>
                <option value="aman" {{ ($filters['status'] ?? '') === 'aman' ? 'selected' : '' }}>Aman</option>
                <option value="kendala" {{ ($filters['status'] ?? '') === 'kendala' ? 'selected' : '' }}>Kendala</option>
                <option value="perbaikan" {{ ($filters['status'] ?? '') === 'perbaikan' ? 'selected' : '' }}>Dalam Perbaikan</option>
            </select>
        </div>

        <div class="d-flex gap-8">
            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-filter"></i> Terapkan
            </button>
            <a href="{{ route('riwayat.index') }}" class="btn btn-secondary" title="Reset filter">
                <i class="fa-solid fa-rotate-left"></i>
            </a>
        </div>
    </form>
</div>

{{-- ── TABEL ── --}}
<div class="card">
    <div class="card-header">
        <div>
            <div class="card-title">Daftar Riwayat</div>
            <div class="card-subtitle">{{ $checklists->total() }} pemeriksaan ditemukan</div>
        </div>
    </div>

    <div class="card-body-p0">
        <div class="table-wrap">
            <table class="tc-table">
                <thead>
                    <tr>
                        <th style="width:52px">#</th>
                        <th>Tanggal</th>
                        <th>Armada</th>
                        <th>Petugas</th>
                        <th>Jenis</th>
                        <th>Status</th>
                        <th style="text-align:center">Kelayakan</th>
                        <th style="width:140px;text-align:center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($checklists as $index => $row)
                    @php
                        $total     = $row->items->count();
                        $baik      = $row->items->where('kondisi', true)->count();
                        $kelayakan = $total > 0 ? round(($baik / $total) * 100) : 100;
                        $isAman    = $row->status === 'aman';
                    @endphp
                    <tr>
                        <td style="color:var(--text-muted);font-size:12px">{{ $checklists->firstItem() + $index }}</td>

                        <td style="white-space:nowrap">
                            <div style="font-weight:600;font-size:13px">{{ $row->tanggal->translatedFormat('d M Y') }}</div>
                            <div style="font-size:11px;color:var(--text-muted)">{{ $row->tanggal->format('H:i') }} WIB</div>
                        </td>

                        <td>
                            <div style="font-weight:600;font-family:monospace;font-size:13px">{{ $row->vehicle->merk ?? '—' }}</div>
                            <div style="font-weight:600;font-family:monospace;font-size:13px">{{ $row->vehicle->jenis_kendaraan ?? '—' }}</div>
                            <span style="background:#0F1E36;color:#fff;padding:1px 7px;border-radius:4px;font-size:10px;font-weight:700;font-family:monospace">
                                {{ strtoupper($row->vehicle->plat_nomor ?? '-') }}
                            </span>
                        </td>

                        <td style="font-weight:500">{{ $row->driver->name ?? '—' }}</td>

                        <td>
                            @if($row->checklist_type === 'k3')
                                <span class="badge badge-info"><i class="fa-solid fa-shield-halved"></i> K3</span>
                            @else
                                <span class="badge badge-primary"><i class="fa-solid fa-truck-ramp-box"></i> Fisik</span>
                            @endif
                        </td>

                        <td>
                            @if($isAman)
                                <span class="badge badge-success">
                                    <i class="fa-solid fa-circle-check"></i> Aman
                                </span>
                            @elseif($row->status === 'perbaikan')
                                <span class="badge badge-warning">
                                    <i class="fa-solid fa-screwdriver-wrench"></i> Dalam Perbaikan
                                </span>
                            @else
                                <span class="badge badge-danger">
                                    <i class="fa-solid fa-triangle-exclamation"></i> Kendala
                                </span>
                            @endif
                        </td>

                        <td style="text-align:center;font-weight:700;color:{{ $row->status === 'perbaikan' ? 'var(--warning)' : ($kelayakan >= 80 ? 'var(--success)' : ($kelayakan >= 50 ? 'var(--warning)' : 'var(--danger)')) }}">
                            {{ $row->status === 'perbaikan' ? '—' : $kelayakan . '%' }}
                        </td>

                        <td style="text-align:center">
                            <div class="d-flex gap-6 align-center" style="justify-content:center">
                                <a href="{{ route('checklists.show', $row->id) }}" class="btn btn-secondary btn-sm btn-icon" title="Lihat Detail">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                                @role('admin')
                                    <a href="{{ route('checklists.edit', $row->id) }}" class="btn btn-warning btn-sm btn-icon" title="Edit Hasil Checklist">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                @endrole
                                @if($row->vehicle)
                                    <a href="{{ route('riwayat.vehicle-pdf', ['vehicle' => $row->vehicle_id, 'bulan' => $row->tanggal->format('Y-m')]) }}" target="_blank" class="btn btn-primary btn-sm btn-icon" title="Cetak PDF Armada (2 Lembar: Fisik &amp; K3)">
                                        <i class="fa-solid fa-file-pdf"></i>
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8">
                            <div class="empty-state">
                                <div class="empty-icon"><i class="fa-solid fa-clock-rotate-left"></i></div>
                                <div class="empty-title">Tidak Ada Riwayat</div>
                                <div class="empty-text">Tidak ada pemeriksaan yang cocok dengan filter ini.</div>
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

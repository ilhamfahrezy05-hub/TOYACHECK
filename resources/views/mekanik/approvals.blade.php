@extends('layouts.app')

@section('title', 'History Pembelanjaan & Persetujuan')
@section('page-title', 'History Pembelanjaan')
@section('page-subtitle', 'Lacak dan kelola semua transaksi pembelian serta status persetujuan suku cadang armada dari database')

@section('page-actions')
    <a href="{{ route('mekanik.approvals.export') }}" class="btn btn-secondary" style="background:#0F172A;color:#FFFFFF">
        <i class="fa-solid fa-download"></i>
        Unduh CSV
    </a>
    <a href="{{ route('mekanik.inventory.request') }}" class="btn btn-primary" style="background:#0F2647">
        <i class="fa-solid fa-plus"></i>
        Pengajuan Stok
    </a>
@endsection

@push('styles')
<style>
    .mk-status-disetujui {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 999px;
        background: #DCFCE7;
        color: #15803D;
        font-size: 11px;
        font-weight: 800;
    }

    .mk-status-pending {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 999px;
        background: #FEF3C7;
        color: #B45309;
        font-size: 11px;
        font-weight: 800;
    }

    .mk-status-ditolak {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 999px;
        background: #FEE2E2;
        color: #B91C1C;
        font-size: 11px;
        font-weight: 800;
    }
</style>
@endpush

@section('content')

<div class="card">
    <div class="card-header" style="flex-wrap:wrap;gap:12px">
        <div>
            <div class="card-title">Daftar Transaksi & Status Persetujuan Suku Cadang</div>
            <div class="card-subtitle">Menampilkan {{ $requests->count() }} dari {{ $totalInDb ?? $requests->count() }} permintaan di database</div>
        </div>

        <form method="GET" action="{{ route('mekanik.approvals') }}" class="d-flex align-center gap-8" style="margin-left:auto;flex-wrap:wrap">
            <div style="position:relative">
                <i class="fa-solid fa-magnifying-glass" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#94A3B8;font-size:12px"></i>
                <input type="text"
                       name="q"
                       value="{{ $search }}"
                       class="form-control"
                       style="padding-left:34px;width:230px"
                       placeholder="Cari part atau vendor...">
            </div>

            <select name="status" class="form-control" style="width:165px" onchange="this.form.submit()">
                <option value="">Semua Status</option>
                <option value="approved" {{ $status === 'approved' ? 'selected' : '' }}>Disetujui</option>
                <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Menunggu (Pending)</option>
                <option value="rejected" {{ $status === 'rejected' ? 'selected' : '' }}>Ditolak</option>
            </select>

            @if($search || $status)
                <a href="{{ route('mekanik.approvals') }}" class="btn btn-secondary btn-sm">Reset</a>
            @endif
        </form>
    </div>

    <div class="card-body-p0">
        <div class="table-wrap">
            <table class="tc-table">
                <thead>
                    <tr>
                        <th>ID PERMINTAAN</th>
                        <th>NAMA SUKU CADANG</th>
                        <th>KATEGORI</th>
                        <th>NAMA VENDOR</th>
                        <th>DIAJUKAN OLEH</th>
                        <th>TANGGAL</th>
                        <th>STATUS</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requests as $req)
                        @php
                            $reqCode  = '#REQ-' . $req->created_at->format('Y') . '-' . str_pad($req->id, 3, '0', STR_PAD_LEFT);
                            $kategori = $req->sparepart->kategori ?? 'Suku Cadang';
                            $vendor   = $req->vendor_name ?: 'PT. Nusantara Perkasa';
                        @endphp
                        <tr>
                            <td style="font-weight:700;color:#334155;font-family:monospace">
                                {{ $reqCode }}
                            </td>
                            <td>
                                <div style="font-weight:700;color:#0F172A">
                                    {{ $req->nama_sparepart }}
                                    <span class="badge badge-neutral" style="font-size:10.5px;margin-left:4px">x{{ $req->qty }}</span>
                                </div>
                                @if($req->alasan)
                                    <div style="font-size:11.5px;color:var(--text-muted)">{{ \Illuminate\Support\Str::limit($req->alasan, 55) }}</div>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-neutral">{{ $kategori }}</span>
                            </td>
                            <td style="color:#475569;font-weight:600">
                                {{ $vendor }}
                            </td>
                            <td style="font-weight:500">
                                {{ $req->requester->name ?? 'Mekanik' }}
                            </td>
                            <td style="color:#334155;font-weight:600;white-space:nowrap">
                                {{ $req->created_at->translatedFormat('d M Y') }}
                            </td>
                            <td>
                                @if($req->status === 'approved')
                                    <span class="mk-status-disetujui">Disetujui</span>
                                @elseif($req->status === 'rejected')
                                    <span class="mk-status-ditolak" title="{{ $req->catatan_admin }}">Ditolak</span>
                                @else
                                    <span class="mk-status-pending">Pending</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">
                                    <div class="empty-icon"><i class="fa-solid fa-receipt"></i></div>
                                    <div class="empty-title">Belum Ada Riwayat Pembelanjaan</div>
                                    <div class="empty-text">Semua pengajuan stok suku cadang akan tampil secara real-time di sini.</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card-footer" style="justify-content:space-between;font-size:12px;color:var(--text-secondary)">
        <span>Menampilkan {{ $requests->count() }} dari {{ $totalInDb ?? $requests->count() }} permintaan (semua data dimuat dari database)</span>
        <span style="font-weight:700;color:var(--text-primary)">Tersinkronisasi dengan Persetujuan Admin Distribusi</span>
    </div>
</div>

@endsection

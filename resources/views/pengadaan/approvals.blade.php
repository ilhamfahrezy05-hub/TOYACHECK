@extends('layouts.app')

@section('title', 'Persetujuan — Riwayat Pembelanjaan')
@section('page-title', 'Riwayat Pembelanjaan')
@section('page-subtitle', 'Lacak dan kelola semua transaksi pembelian suku cadang armada.')

@section('page-actions')
    <div style="display:flex;flex-direction:column;gap:8px;align-items:flex-end">
        <div class="d-flex align-center gap-8" style="flex-wrap:wrap">
            <form method="GET" action="{{ route('pengadaan.approvals') }}" class="d-flex align-center gap-8">
                @if($startDate)<input type="hidden" name="start_date" value="{{ $startDate }}">@endif
                @if($endDate)<input type="hidden" name="end_date" value="{{ $endDate }}">@endif
                <div style="position:relative">
                    <i class="fa-solid fa-magnifying-glass" style="position:absolute;left:11px;top:50%;transform:translateY(-50%);color:#94A3B8;font-size:12px"></i>
                    <input type="text"
                           name="q"
                           value="{{ $search }}"
                           class="form-control"
                           style="padding-left:32px;height:36px;width:230px;font-size:12.5px"
                           placeholder="Cari part atau vendor...">
                </div>
            </form>

            {{-- Tombol Kalender Popover (Figma approved/tanggal 1265:4783) --}}
            <button type="button"
                    class="btn btn-secondary btn-sm"
                    style="height:36px;background:#FFFFFF;border:1px solid #CBD5E1;color:#1E293B;font-weight:600;font-size:12px"
                    onclick="openModal('modalCalendarApprovals')">
                <i class="fa-regular fa-calendar"></i>
                @if($startDate || $endDate)
                    {{ $startDate ? \Carbon\Carbon::parse($startDate)->translatedFormat('d M Y') : '...' }} - {{ $endDate ? \Carbon\Carbon::parse($endDate)->translatedFormat('d M Y') : '...' }}
                @else
                    01 {{ now()->translatedFormat('M Y') }} - {{ now()->endOfMonth()->translatedFormat('d M Y') }}
                @endif
            </button>

            @if($search || $startDate || $endDate)
                <a href="{{ route('pengadaan.approvals') }}" class="btn btn-secondary btn-sm" style="height:36px">Reset</a>
            @endif
        </div>

        <div>
            <a href="{{ route('pengadaan.approvals.export-pdf', array_filter(['q' => $search, 'start_date' => $startDate, 'end_date' => $endDate])) }}"
               target="_blank"
               class="btn btn-sm"
               style="background:#090D16;color:#FFFFFF;font-weight:800;font-size:11.5px;height:34px;padding:0 16px;border-radius:4px;letter-spacing:.03em">
                <i class="fa-solid fa-download"></i> DOWNLOAD PDF
            </a>
        </div>
    </div>
@endsection

@section('content')

{{-- TABEL PERMINTAAN DISETUJUI (FIGMA 1113:2312) --}}
<div class="card">
    <div class="card-body-p0">
        <div class="table-wrap">
            <table class="tc-table">
                <thead>
                    <tr>
                        <th>ID PERMINTAAN</th>
                        <th>NAMA SUKU CADANG</th>
                        <th>KATEGORI</th>
                        <th>NAMA VENDOR</th>
                        <th>TANGGAL</th>
                        <th>STATUS</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($approvedRequests as $req)
                        @php
                            $reqCode  = '#REQ-' . $req->created_at->format('Y') . '-' . str_pad($req->id, 3, '0', STR_PAD_LEFT);
                            $kategori = $req->sparepart->kategori ?? 'Kaki-kaki';
                            $vendorNm = $req->vendor_name ?: 'PT. Nusantara Perkasa';
                        @endphp
                        <tr>
                            <td style="font-weight:700;color:#475569;font-family:monospace;font-size:12.5px">
                                {{ $reqCode }}
                            </td>
                            <td style="font-weight:800;color:#0F172A;font-size:13.5px">
                                {{ $req->nama_sparepart }}
                            </td>
                            <td>
                                <span style="background:#E0F2FE;color:#0369A1;font-size:11px;font-weight:700;padding:3px 10px;border-radius:4px">
                                    {{ $kategori }}
                                </span>
                            </td>
                            <td style="color:#334155;font-weight:600;font-size:13px">
                                {{ $vendorNm }}
                            </td>
                            <td style="color:#475569;font-weight:600;font-size:12.5px">
                                {{ ($req->reviewed_at ?? $req->created_at)->translatedFormat('d M y') }}
                            </td>
                            <td>
                                <span style="background:#DCFCE7;color:#15803D;font-size:11px;font-weight:800;padding:4px 12px;border-radius:4px">
                                    Disetujui
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align:center;padding:36px;color:#64748B">
                                Belum ada permintaan suku cadang berstatus Disetujui.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div style="padding:14px 22px;border-top:1px solid #E2E8F0;background:#F8FAFC;display:flex;justify-content:space-between;align-items:center;font-size:11.5px;color:#64748B">
        <span>Menampilkan {{ $approvedRequests->count() }} dari {{ $totalApproved }} permintaan disetujui (semua data dimuat)</span>
        <span style="font-weight:600">Scroll untuk memuat lebih banyak &darr;</span>
    </div>
</div>

@endsection

@section('modals')
{{-- Modal Kalender Filter Tanggal Pengadaan (Figma approved/tanggal 1265:4783) --}}
<div class="modal-overlay" id="modalCalendarApprovals">
    <div class="modal-box" style="max-width:400px">
        <div class="modal-header">
            <div class="modal-title" style="font-size:14.5px;font-weight:800;display:flex;align-items:center;gap:8px">
                <i class="fa-regular fa-calendar"></i> Pilih tanggal pengadaan
            </div>
            <button type="button" class="modal-close" onclick="closeModal('modalCalendarApprovals')">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <form method="GET" action="{{ route('pengadaan.approvals') }}">
            @if($search)<input type="hidden" name="q" value="{{ $search }}">@endif
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Dari Tanggal (Start Date)</label>
                    <input type="date" name="start_date" value="{{ $startDate ?: now()->startOfMonth()->toDateString() }}" class="form-control">
                </div>
                <div class="form-group" style="margin-bottom:0">
                    <label class="form-label">Sampai Tanggal (End Date)</label>
                    <input type="date" name="end_date" value="{{ $endDate ?: now()->endOfMonth()->toDateString() }}" class="form-control">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalCalendarApprovals')">Cancel</button>
                <button type="submit" class="btn btn-primary" style="background:#090D16">Apply Filter</button>
            </div>
        </form>
    </div>
</div>
@endsection

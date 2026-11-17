@extends('layouts.app')

@section('title', 'Riwayat Belanja — Laporan Pengadaan')
@section('page-title', 'RIWAYAT BELANJA')
@section('page-subtitle', 'Lacak dan kelola semua transaksi pembelian suku cadang armada.')

@section('page-actions')
    <div style="display:flex;flex-direction:column;gap:8px;align-items:flex-end">
        <div class="d-flex align-center gap-8" style="flex-wrap:wrap">
            <form method="GET" action="{{ route('pengadaan.reports') }}" class="d-flex align-center gap-8">
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

            {{-- Tombol Kalender Popover (Figma Klender 918:35447) --}}
            <button type="button"
                    class="btn btn-secondary btn-sm"
                    style="height:36px;background:#FFFFFF;border:1px solid #CBD5E1;color:#1E293B;font-weight:600;font-size:12px"
                    onclick="openModal('modalCalendarReports')">
                <i class="fa-regular fa-calendar"></i>
                @if($startDate || $endDate)
                    {{ $startDate ? \Carbon\Carbon::parse($startDate)->translatedFormat('d M Y') : '...' }} - {{ $endDate ? \Carbon\Carbon::parse($endDate)->translatedFormat('d M Y') : '...' }}
                @else
                    01 {{ now()->translatedFormat('M Y') }} - {{ now()->endOfMonth()->translatedFormat('d M Y') }}
                @endif
            </button>

            @if($search || $startDate || $endDate)
                <a href="{{ route('pengadaan.reports') }}" class="btn btn-secondary btn-sm" style="height:36px">Reset</a>
            @endif
        </div>

        <div class="d-flex align-center gap-8">
            {{-- Tombol Lime Green + ADD HISTORY (Figma 918:34465) --}}
            <a href="{{ route('pengadaan.reports.create') }}"
               class="btn btn-sm"
               style="background:#84CC16;color:#0F172A;font-weight:800;font-size:11.5px;height:34px;padding:0 16px;border-radius:4px;letter-spacing:.03em">
                <i class="fa-solid fa-plus"></i> ADD HISTORY
            </a>

            {{-- Tombol Black DOWNLOAD PDF --}}
            <a href="{{ route('pengadaan.reports.export-pdf', array_filter(['q' => $search, 'start_date' => $startDate, 'end_date' => $endDate])) }}"
               target="_blank"
               class="btn btn-sm"
               style="background:#090D16;color:#FFFFFF;font-weight:800;font-size:11.5px;height:34px;padding:0 16px;border-radius:4px;letter-spacing:.03em">
                <i class="fa-solid fa-download"></i> DOWNLOAD PDF
            </a>
        </div>
    </div>
@endsection

@section('content')

{{-- 1. TABEL UTAMA RIWAYAT BELANJA (FIGMA 918:34465) --}}
<div class="card" style="margin-bottom:24px">
    <div class="card-body-p0">
        <div class="table-wrap">
            <table class="tc-table">
                <thead>
                    <tr>
                        <th>NAMA PART</th>
                        <th>NAMA VENDOR</th>
                        <th>TANGGAL &amp; JAM</th>
                        <th style="text-align:center">BUKTI TRANSFER</th>
                        <th style="width:72px;text-align:center">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($purchases as $pur)
                        @php
                            $firstItem = $pur->items->first();
                            $partName  = $firstItem?->nama_sparepart ?? ('Pembelian ' . $pur->kode_pembelian);
                            $partSku   = $firstItem?->kode_barang ?? $pur->kode_pembelian;
                            $extraCnt  = max(0, $pur->items->count() - 1);
                            $vendorNm  = $pur->vendor?->nama_vendor ?: ($pur->vendor_name ?: 'Mitra Otomotif Sejahtera');
                        @endphp
                        <tr>
                            <td>
                                <div style="font-weight:800;color:#0F172A;font-size:13.5px">
                                    {{ $partName }}
                                    @if($extraCnt > 0)
                                        <span class="badge badge-info" style="font-size:10px;margin-left:4px">+{{ $extraCnt }} item lain</span>
                                    @endif
                                </div>
                                <div style="font-size:11px;color:#94A3B8;font-family:monospace;margin-top:2px">
                                    SKU: {{ $partSku }} &bull; Total: Rp {{ number_format($pur->total_harga, 0, ',', '.') }}
                                </div>
                            </td>

                            <td style="font-weight:600;color:#334155;font-size:13px">
                                {{ $vendorNm }}
                            </td>

                            <td>
                                <div style="font-weight:700;color:#0F172A;font-size:12.5px">
                                    {{ $pur->tanggal_pembelian->translatedFormat('d M Y') }}
                                </div>
                                <div style="font-size:11px;color:#94A3B8">
                                    {{ $pur->waktu_pembelian ?: '14:25' }} WIB
                                </div>
                            </td>

                            <td style="text-align:center">
                                @if($pur->bukti_pembayaran)
                                    <a href="{{ asset('storage/' . $pur->bukti_pembayaran) }}"
                                       target="_blank"
                                       style="display:inline-flex;align-items:center;justify-content:center;width:40px;height:40px;border-radius:6px;border:1px solid #CBD5E1;background:#F8FAFC;overflow:hidden;text-decoration:none"
                                       title="Lihat Bukti Transfer">
                                        @if(str_ends_with(strtolower($pur->bukti_pembayaran), '.pdf'))
                                            <i class="fa-solid fa-file-pdf" style="color:#DC2626;font-size:18px"></i>
                                        @else
                                            <img src="{{ asset('storage/' . $pur->bukti_pembayaran) }}" alt="Nota" style="width:100%;height:100%;object-fit:cover">
                                        @endif
                                    </a>
                                @else
                                    <div style="display:inline-flex;align-items:center;justify-content:center;width:36px;height:40px;border-radius:6px;background:#1E293B;color:#FFFFFF;font-size:15px"
                                         title="Invoice Tercatat ({{ $pur->kode_pembelian }})">
                                        <i class="fa-solid fa-file-invoice"></i>
                                    </div>
                                @endif
                            </td>

                            <td style="text-align:center">
                                <button type="button"
                                        class="btn btn-sm btn-icon"
                                        style="background:transparent;color:#EF4444"
                                        title="Hapus Riwayat Pembelian"
                                        onclick="openDeletePurchaseModal(
                                            {{ $pur->id }},
                                            {{ \Illuminate\Support\Js::from($partName) }},
                                            {{ \Illuminate\Support\Js::from($vendorNm) }},
                                            {{ \Illuminate\Support\Js::from($pur->kode_pembelian) }},
                                            {{ \Illuminate\Support\Js::from('Rp ' . number_format($pur->total_harga, 0, ',', '.')) }}
                                        )">
                                    <i class="fa-regular fa-trash-can"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align:center;padding:36px;color:#64748B">
                                Belum ada transaksi pembelanjaan suku cadang yang sesuai filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- 2. TIGA KARTU RINGKASAN DI BAWAH TABEL (FIGMA 918:34465) --}}
<div style="display:grid;grid-template-columns:repeat(3, 1fr);gap:20px">
    <div class="card" style="padding:20px 22px;display:flex;align-items:center;gap:16px">
        <div style="width:44px;height:44px;border-radius:10px;background:#EFF6FF;color:#2563EB;display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0">
            <i class="fa-solid fa-boxes-stacked"></i>
        </div>
        <div>
            <div style="font-size:10px;font-weight:800;color:#94A3B8;letter-spacing:.06em;text-transform:uppercase">TOTAL PEMBELIAN</div>
            <div style="font-size:20px;font-weight:900;color:#0F172A;margin-top:2px">{{ number_format($totalItemsDibeli, 0, ',', '.') }} Items</div>
        </div>
    </div>

    <div class="card" style="padding:20px 22px;display:flex;align-items:center;gap:16px">
        <div style="width:44px;height:44px;border-radius:10px;background:#DCFCE7;color:#16A34A;display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0">
            <i class="fa-solid fa-money-bill-wave"></i>
        </div>
        <div>
            <div style="font-size:10px;font-weight:800;color:#94A3B8;letter-spacing:.06em;text-transform:uppercase">TOTAL PENGELUARAN (BULAN INI)</div>
            <div style="font-size:20px;font-weight:900;color:#0F172A;margin-top:2px">Rp {{ number_format($pengeluaranBulanIni, 0, ',', '.') }}</div>
        </div>
    </div>

    <div class="card" style="padding:20px 22px;display:flex;align-items:center;gap:16px">
        <div style="width:44px;height:44px;border-radius:10px;background:#FEF3C7;color:#D97706;display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0">
            <i class="fa-solid fa-user-tie"></i>
        </div>
        <div>
            <div style="font-size:10px;font-weight:800;color:#94A3B8;letter-spacing:.06em;text-transform:uppercase">VENDOR AKTIF</div>
            <div style="font-size:20px;font-weight:900;color:#0F172A;margin-top:2px">{{ $vendorAktifCount }} Vendors</div>
        </div>
    </div>
</div>

@endsection

@section('modals')
{{-- 1. Modal Hapus Riwayat Pembelian (Figma History hapus 918:36198) --}}
<div class="modal-overlay" id="modalDeletePurchase">
    <div class="modal-box" style="max-width:440px">
        <div class="modal-header">
            <div class="modal-title" style="font-size:15px;font-weight:800">Hapus Riwayat Pembelian</div>
            <button type="button" class="modal-close" onclick="closeModal('modalDeletePurchase')">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form method="POST" id="formDeletePurchase" action="">
            @csrf
            @method('DELETE')
            <div class="modal-body">
                <div style="background:#FFF1F2;border:1px solid #FECDD3;border-radius:8px;padding:14px 16px;display:flex;gap:12px;align-items:flex-start;margin-bottom:16px">
                    <i class="fa-solid fa-triangle-exclamation" style="color:#E11D48;font-size:18px;margin-top:2px"></i>
                    <div>
                        <div style="font-size:13px;font-weight:700;color:#881337">Apakah Anda yakin ingin menghapus riwayat pembelian ini?</div>
                        <div style="font-size:11.5px;color:#9F1239;margin-top:2px">Tindakan ini tidak dapat dibatalkan.</div>
                    </div>
                </div>

                <div style="background:#F8FAFC;border:1px solid #E2E8F0;border-radius:8px;padding:14px 16px">
                    <div style="font-size:10px;font-weight:800;color:#94A3B8;text-transform:uppercase;margin-bottom:6px">DETAIL ITEM</div>
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;padding-bottom:10px;border-bottom:1px solid #E2E8F0;margin-bottom:10px">
                        <div>
                            <div id="dpPartName" style="font-size:13.5px;font-weight:800;color:#0F172A">—</div>
                            <div id="dpVendorName" style="font-size:11.5px;color:#64748B;margin-top:2px">—</div>
                        </div>
                        <div style="text-align:right">
                            <div style="font-size:9.5px;color:#94A3B8;font-weight:700">ID ORDER</div>
                            <div id="dpOrderCode" style="font-size:12px;font-weight:800;color:#0F172A;font-family:monospace">#PR-88289</div>
                        </div>
                    </div>
                    <div style="display:flex;justify-content:space-between;align-items:center">
                        <span style="font-size:12px;color:#64748B;font-weight:600">Total Transaksi</span>
                        <span id="dpTotalHarga" style="font-size:14px;font-weight:900;color:#0F172A">Rp 0</span>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalDeletePurchase')">Batal</button>
                <button type="submit" class="btn btn-danger" style="background:#DC2626">
                    <i class="fa-regular fa-trash-can"></i> Hapus Data
                </button>
            </div>
        </form>
    </div>
</div>

{{-- 2. Modal Kalender Filter Tanggal Pembelian (Figma Klender 918:35447) --}}
<div class="modal-overlay" id="modalCalendarReports">
    <div class="modal-box" style="max-width:400px">
        <div class="modal-header">
            <div class="modal-title" style="font-size:14.5px;font-weight:800;display:flex;align-items:center;gap:8px">
                <i class="fa-regular fa-calendar"></i> Pilih tanggal pembelian
            </div>
            <button type="button" class="modal-close" onclick="closeModal('modalCalendarReports')">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <form method="GET" action="{{ route('pengadaan.reports') }}">
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
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalCalendarReports')">Cancel</button>
                <button type="submit" class="btn btn-primary" style="background:#090D16">Apply Filter</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function openDeletePurchaseModal(id, partName, vendorName, orderCode, totalHarga) {
        document.getElementById('formDeletePurchase').action = '/pengadaan/reports/' + id;
        document.getElementById('dpPartName').textContent = partName;
        document.getElementById('dpVendorName').textContent = vendorName;
        document.getElementById('dpOrderCode').textContent = orderCode;
        document.getElementById('dpTotalHarga').textContent = totalHarga;
        openModal('modalDeletePurchase');
    }
</script>
@endpush

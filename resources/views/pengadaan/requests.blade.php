@extends('layouts.app')

@section('title', 'Permintaan Pengadaan')
@section('page-title', 'Permintaan Pengadaan')
@section('page-subtitle', 'Mengelola dan melacak alur kerja pengadaan inventaris di seluruh jaringan armada')

@section('page-actions')
    <a href="{{ route('mekanik.approvals.export') }}" class="btn btn-secondary btn-sm" style="border:1px solid #CBD5E1;background:#FFFFFF;color:#0F172A;font-weight:700">
        <i class="fa-solid fa-file-export"></i> EXPORT CSV
    </a>
@endsection

@push('styles')
<style>
    .pr-master-detail {
        display: grid;
        grid-template-columns: 1.55fr 1fr;
        gap: 24px;
        align-items: start;
    }
    @media (max-width: 1024px) {
        .pr-master-detail { grid-template-columns: 1fr; }
    }
    .pr-list-card {
        background: #FFFFFF;
        border: 1px solid #E2E8F0;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 1px 4px rgba(15,23,42,0.03);
    }
    .pr-row {
        cursor: pointer;
        transition: background .15s;
        border-left: 4px solid transparent;
    }
    .pr-row:hover {
        background: #F8FAFC;
    }
    .pr-row.active {
        background: #F0FDF4;
        border-left-color: #16A34A;
    }
    .pr-detail-card {
        background: #FFFFFF;
        border: 1px solid #E2E8F0;
        border-radius: 10px;
        padding: 24px;
        position: sticky;
        top: 84px;
        box-shadow: 0 2px 10px rgba(15,23,42,0.05);
    }
    .pr-menu-wrap {
        position: relative;
        display: inline-block;
    }
    .pr-menu-dropdown {
        display: none;
        position: absolute;
        right: 0;
        top: 100%;
        background: #FFFFFF;
        border: 1px solid #E2E8F0;
        border-radius: 8px;
        box-shadow: 0 8px 24px rgba(15,23,42,0.12);
        min-width: 170px;
        z-index: 50;
        padding: 6px;
    }
    .pr-menu-dropdown.show { display: block; }
    .pr-menu-item {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 8px 12px;
        font-size: 12px;
        font-weight: 600;
        color: #1E293B;
        text-decoration: none;
        border-radius: 6px;
        width: 100%;
        border: none;
        background: none;
        cursor: pointer;
        text-align: left;
    }
    .pr-menu-item:hover { background: #F1F5F9; }
    .pr-menu-item.danger { color: #DC2626; }
    .pr-menu-item.danger:hover { background: #FEF2F2; }
</style>
@endpush

@section('content')

<div class="pr-master-detail">

    {{-- ── KOLOM KIRI: TABEL PERMINTAAN SAAT INI (FIGMA 918:24651) ── --}}
    <div class="pr-list-card">
        <div style="padding:18px 22px;border-bottom:1px solid #E2E8F0;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px">
            <div style="font-size:14.5px;font-weight:800;color:#0F172A">
                Permintaan saat ini
            </div>

            <form method="GET" action="{{ route('pengadaan.requests') }}" class="d-flex align-center gap-8" style="flex-wrap:wrap">
                <div style="position:relative">
                    <i class="fa-solid fa-magnifying-glass" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#94A3B8;font-size:12px"></i>
                    <input type="text"
                           name="q"
                           value="{{ $search }}"
                           class="form-control"
                           style="padding-left:30px;height:34px;font-size:12.5px;width:190px"
                           placeholder="Cari suku cadang...">
                </div>

                <select name="status" class="form-control" style="height:34px;font-size:12.5px;width:140px" onchange="this.form.submit()">
                    <option value="">Semua Status</option>
                    <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Menunggu</option>
                    <option value="approved" {{ $status === 'approved' ? 'selected' : '' }}>Diterima</option>
                    <option value="rejected" {{ $status === 'rejected' ? 'selected' : '' }}>Ditolak</option>
                </select>
            </form>
        </div>

        <div class="table-wrap">
            <table class="tc-table">
                <tbody>
                    @forelse($requests as $req)
                        @php
                            $isSelected = $selectedRequest && $selectedRequest->id === $req->id;
                            $kategori   = $req->sparepart->kategori ?? 'Komponen Mesin';
                        @endphp
                        <tr class="pr-row {{ $isSelected ? 'active' : '' }}"
                            onclick="window.location.href='{{ route('pengadaan.requests', array_filter(['q' => $search, 'status' => $status, 'selected' => $req->id])) }}'">
                            <td style="font-weight:700;color:#0F172A;padding:14px 18px">
                                {{ $req->nama_sparepart }}
                                @if($req->status === 'approved')
                                    <span style="display:inline-block;width:7px;height:7px;border-radius:50%;background:#16A34A;margin-left:6px" title="Diterima"></span>
                                @elseif($req->status === 'rejected')
                                    <span style="display:inline-block;width:7px;height:7px;border-radius:50%;background:#DC2626;margin-left:6px" title="Ditolak"></span>
                                @else
                                    <span style="display:inline-block;width:7px;height:7px;border-radius:50%;background:#F59E0B;margin-left:6px" title="Menunggu"></span>
                                @endif
                            </td>
                            <td style="color:#64748B;font-size:12.5px;white-space:nowrap">
                                {{ $req->created_at->translatedFormat('d M Y') }}
                            </td>
                            <td style="color:#475569;font-size:12.5px">
                                {{ $kategori }}
                            </td>
                            <td style="width:44px;text-align:right" onclick="event.stopPropagation()">
                                <div class="pr-menu-wrap">
                                    <button type="button" class="btn btn-sm btn-icon" style="background:transparent;color:#64748B" onclick="togglePrMenu('prMenu{{ $req->id }}')">
                                        <i class="fa-solid fa-ellipsis-vertical"></i>
                                    </button>
                                    <div class="pr-menu-dropdown" id="prMenu{{ $req->id }}">
                                        <a href="{{ route('pengadaan.requests.show', $req->id) }}" class="pr-menu-item">
                                            <i class="fa-regular fa-eye"></i> Lihat Detail Penuh
                                        </a>
                                        <a href="{{ route('pengadaan.reports.create', ['request_id' => $req->id]) }}" class="pr-menu-item">
                                            <i class="fa-solid fa-receipt"></i> Input Nota Belanja
                                        </a>
                                        <button type="button" class="pr-menu-item danger"
                                                onclick="openDeleteReqModal({{ $req->id }}, {{ \Illuminate\Support\Js::from($req->nama_sparepart) }}, {{ \Illuminate\Support\Js::from($kategori) }}, '{{ '#PR-' . (8820 + $req->id) }}')">
                                            <i class="fa-regular fa-trash-can"></i> Hapus Permintaan
                                        </button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" style="padding:36px;text-align:center;color:#64748B">
                                Belum ada permintaan pengadaan yang tercatat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ── KOLOM KANAN: PANEL INSPEKSI & KEPUTUSAN PERMINTAAN (FIGMA 918:24651 & 1113:1272) ── --}}
    @if($selectedRequest)
        @php
            $prNumber = '#PR-' . (8820 + $selectedRequest->id);
            $skuCode  = $selectedRequest->sparepart->kode_sparepart ?? ('BP-H-' . (4490 + $selectedRequest->id));
        @endphp
        <div class="pr-detail-card">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;margin-bottom:18px">
                <div>
                    <div style="font-size:20px;font-weight:900;color:#0F172A;line-height:1.1">
                        {{ $prNumber }}
                    </div>
                    <div style="font-size:10px;font-weight:700;color:#94A3B8;letter-spacing:.05em;text-transform:uppercase;margin-top:4px">
                        TERKIRIM: {{ strtoupper($selectedRequest->created_at->translatedFormat('M d, Y')) }} | {{ $selectedRequest->created_at->format('H:i') }}
                    </div>
                </div>

                @if($selectedRequest->status === 'approved')
                    <span style="background:#FEF3C7;color:#B45309;font-size:10px;font-weight:800;padding:4px 10px;border-radius:4px;letter-spacing:.05em">
                        DITERIMA
                    </span>
                @elseif($selectedRequest->status === 'rejected')
                    <span style="background:#FEE2E2;color:#B91C1C;font-size:10px;font-weight:800;padding:4px 10px;border-radius:4px;letter-spacing:.05em">
                        DITOLAK
                    </span>
                @else
                    <span style="background:#FEF3C7;color:#B45309;font-size:10px;font-weight:800;padding:4px 10px;border-radius:4px;letter-spacing:.05em;text-align:center">
                        MENUNGGU<br>STATUS
                    </span>
                @endif
            </div>

            {{-- Tombol Keputusan Diterima / Ditolak (atau Status Selesai) --}}
            @if($selectedRequest->isPending())
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:22px">
                    <form method="POST" action="{{ route('pengadaan.requests.approve', $selectedRequest->id) }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit" style="width:100%;background:#090D16;color:#FFFFFF;border:1.5px solid #090D16;padding:10px 14px;border-radius:6px;font-size:12.5px;font-weight:700;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:6px">
                            <i class="fa-solid fa-check"></i> Diterima
                        </button>
                    </form>

                    <form method="POST" action="{{ route('pengadaan.requests.reject', $selectedRequest->id) }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit" style="width:100%;background:#FFFFFF;color:#DC2626;border:1.5px solid #FCA5A5;padding:10px 14px;border-radius:6px;font-size:12.5px;font-weight:700;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:6px">
                            <i class="fa-solid fa-ban"></i> Ditolak
                        </button>
                    </form>
                </div>
            @elseif($selectedRequest->status === 'approved')
                {{-- State Accepted (Figma acc/Request 1113:1272) --}}
                <div style="text-align:center;padding:10px 0 18px;border-bottom:1px solid #F1F5F9;margin-bottom:18px">
                    <div style="width:36px;height:36px;border-radius:50%;background:#DCFCE7;color:#16A34A;display:inline-flex;align-items:center;justify-content:center;font-size:16px">
                        <i class="fa-solid fa-check"></i>
                    </div>
                </div>
            @else
                <div style="text-align:center;padding:10px 0 18px;border-bottom:1px solid #F1F5F9;margin-bottom:18px">
                    <div style="width:36px;height:36px;border-radius:50%;background:#FEE2E2;color:#DC2626;display:inline-flex;align-items:center;justify-content:center;font-size:16px">
                        <i class="fa-solid fa-xmark"></i>
                    </div>
                </div>
            @endif

            {{-- Detail Barang Permintaan --}}
            <div style="border-top:1px solid #F1F5F9;padding-top:16px;margin-bottom:20px">
                <div style="font-size:10px;font-weight:800;color:#94A3B8;letter-spacing:.06em;text-transform:uppercase;margin-bottom:8px">
                    {{ $selectedRequest->status === 'approved' ? 'BARANG PERMINTAAN' : 'PERMINTAAN BARANG' }}
                </div>
                <div style="font-size:14.5px;font-weight:800;color:#0F172A">
                    {{ $selectedRequest->nama_sparepart }}
                </div>
                <div style="font-size:11.5px;color:#64748B;font-weight:600;margin-top:2px">
                    SKU: {{ $skuCode }} &bull; Qty: {{ $selectedRequest->qty }}
                </div>
                @if($selectedRequest->alasan)
                    <div style="font-size:12px;color:#475569;margin-top:8px;background:#F8FAFC;padding:8px 10px;border-radius:6px">
                        {{ $selectedRequest->alasan }}
                    </div>
                @endif
            </div>

            {{-- Riwayat Persetujuan (Timeline Vertikal) --}}
            <div style="border-top:1px solid #F1F5F9;padding-top:16px;margin-bottom:20px">
                <div style="font-size:10px;font-weight:800;color:#94A3B8;letter-spacing:.06em;text-transform:uppercase;margin-bottom:14px">
                    RIWAYAT PERSETUJUAN
                </div>

                @if($selectedRequest->status === 'approved')
                    <div style="display:flex;gap:12px;position:relative;padding-bottom:16px">
                        <div style="width:10px;height:10px;border-radius:50%;background:#16A34A;margin-top:4px;flex-shrink:0"></div>
                        <div>
                            <div style="font-size:11.5px;font-weight:800;color:#0F172A">JADWAL PERMINTAAN</div>
                            <div style="font-size:11px;color:#64748B">
                                Deadline &bull; {{ ($selectedRequest->expected_delivery_date ?? $selectedRequest->created_at->addDays(2))->translatedFormat('d M Y') }} / 11:01 WIB
                            </div>
                        </div>
                    </div>
                    <div style="display:flex;gap:12px">
                        <div style="width:10px;height:10px;border-radius:50%;background:#CBD5E1;margin-top:4px;flex-shrink:0"></div>
                        <div>
                            <div style="font-size:11.5px;font-weight:700;color:#64748B">Persetujuan permintaan</div>
                            <div style="font-size:11px;color:#94A3B8">
                                Disetujui oleh {{ $selectedRequest->reviewer->name ?? auth()->user()->name }}
                            </div>
                        </div>
                    </div>
                @else
                    <div style="display:flex;gap:12px;position:relative;padding-bottom:16px">
                        <div style="width:10px;height:10px;border-radius:50%;background:#16A34A;margin-top:4px;flex-shrink:0"></div>
                        <div>
                            <div style="font-size:11.5px;font-weight:800;color:#0F172A">PERMINTAAN</div>
                            <div style="font-size:11px;color:#64748B">
                                {{ $selectedRequest->requester->name ?? 'Mekanik' }} &bull; {{ $selectedRequest->created_at->format('M d, h:i A') }}
                            </div>
                        </div>
                    </div>
                    <div style="display:flex;gap:12px">
                        <div style="width:10px;height:10px;border-radius:50%;background:#CBD5E1;margin-top:4px;flex-shrink:0"></div>
                        <div>
                            <div style="font-size:11.5px;font-weight:700;color:#64748B">ULASAN SEDANG DIPROSES</div>
                            <div style="font-size:11px;color:#94A3B8">Warehouse Lead &bull; Assignee</div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Footer Links ke Detail Penuh & Catat Nota --}}
            <div style="display:flex;gap:8px;border-top:1px solid #F1F5F9;padding-top:14px">
                <a href="{{ route('pengadaan.requests.show', $selectedRequest->id) }}" class="btn btn-secondary btn-sm" style="flex:1;justify-content:center;font-size:12px">
                    <i class="fa-regular fa-file-lines"></i> Tahapan Detail
                </a>
                <a href="{{ route('pengadaan.reports.create', ['request_id' => $selectedRequest->id]) }}" class="btn btn-primary btn-sm" style="flex:1;justify-content:center;background:#0F172A;font-size:12px">
                    <i class="fa-solid fa-plus"></i> Input Nota
                </a>
            </div>
        </div>
    @endif

</div>

@endsection

@section('modals')
{{-- Modal Hapus Permintaan (Figma cancel/Request 1113:230) --}}
<div class="modal-overlay" id="modalDeletePengadaanReq">
    <div class="modal-box" style="max-width:440px">
        <div class="modal-header">
            <div class="modal-title" style="font-size:15px;font-weight:800">Hapus Riwayat Pembelian</div>
            <button type="button" class="modal-close" onclick="closeModal('modalDeletePengadaanReq')">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <form method="POST" id="formDeletePengadaanReq" action="">
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
                    <div style="display:flex;justify-content:space-between;align-items:flex-start">
                        <div>
                            <div id="delReqName" style="font-size:13.5px;font-weight:800;color:#0F172A">—</div>
                            <div id="delReqCat" style="font-size:11.5px;color:#64748B;margin-top:2px">—</div>
                        </div>
                        <div style="text-align:right">
                            <div style="font-size:9.5px;color:#94A3B8;font-weight:700">ID ORDER</div>
                            <div id="delReqCode" style="font-size:12px;font-weight:800;color:#0F172A;font-family:monospace">#PR-8821</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalDeletePengadaanReq')">Batal</button>
                <button type="submit" class="btn btn-danger" style="background:#DC2626">
                    <i class="fa-regular fa-trash-can"></i> Hapus Data
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function togglePrMenu(id) {
        document.querySelectorAll('.pr-menu-dropdown').forEach(el => {
            if (el.id !== id) el.classList.remove('show');
        });
        const target = document.getElementById(id);
        if (target) target.classList.toggle('show');
    }
    document.addEventListener('click', function () {
        document.querySelectorAll('.pr-menu-dropdown').forEach(el => el.classList.remove('show'));
    });

    function openDeleteReqModal(id, name, cat, code) {
        document.getElementById('formDeletePengadaanReq').action = '/pengadaan/requests/' + id;
        document.getElementById('delReqName').textContent = name;
        document.getElementById('delReqCat').textContent = cat;
        document.getElementById('delReqCode').textContent = code;
        openModal('modalDeletePengadaanReq');
    }
</script>
@endpush

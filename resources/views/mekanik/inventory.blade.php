@extends('layouts.app')

@section('title', 'Inventaris Suku Cadang')
@section('page-title', 'Daftar Suku Cadang Terkini')
@section('page-subtitle', 'Monitoring persediaan suku cadang bengkel berdasarkan klasifikasi prioritas ABC')

@section('page-actions')
    <a href="{{ route('mekanik.inventory.request') }}" class="btn btn-primary" style="background:#0F2647">
        <i class="fa-solid fa-circle-plus"></i>
        Pengajuan Stok
    </a>
@endsection

@push('styles')
<style>
    /* ── TOP 3 SUMMARY WIDGETS (FIGMA INVENTORY) ── */
    .mk-inv-top {
        display: grid;
        grid-template-columns: 240px 1fr 240px;
        gap: 20px;
        align-items: stretch;
        margin-bottom: 24px;
    }

    .mk-inv-green-card {
        background: #80FF44;
        border-radius: 16px;
        padding: 18px 22px;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .mk-inv-green-label {
        font-size: 10px;
        font-weight: 800;
        letter-spacing: 0.06em;
        color: #14532D;
        text-transform: uppercase;
        text-align: center;
        margin-bottom: 10px;
    }

    .mk-inv-green-counts {
        display: flex;
        align-items: center;
        justify-content: space-around;
        font-size: 23px;
        font-weight: 900;
        color: #052E16;
    }

    .mk-inv-green-counts a {
        color: inherit;
        text-decoration: none;
        padding: 2px 6px;
        border-radius: 6px;
        transition: background 0.15s;
    }

    .mk-inv-green-counts a:hover {
        background: rgba(255, 255, 255, 0.35);
    }

    .mk-inv-center {
        background: #FFFFFF;
        border: 1px solid var(--border);
        border-radius: 14px;
        padding: 16px 20px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        gap: 12px;
    }

    .mk-search-box {
        position: relative;
        width: 100%;
    }

    .mk-search-box i {
        position: absolute;
        left: 16px;
        top: 50%;
        transform: translateY(-50%);
        color: #94A3B8;
        font-size: 13px;
    }

    .mk-search-input {
        width: 100%;
        padding: 10px 16px 10px 40px;
        background: #F1F5F9;
        border: 1.5px solid transparent;
        border-radius: 999px;
        font-family: inherit;
        font-size: 13px;
        color: #0F172A;
        outline: none;
        transition: all 0.18s;
    }

    .mk-search-input:focus {
        background: #FFFFFF;
        border-color: #091E3A;
    }

    .mk-inv-filter-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        flex-wrap: wrap;
    }

    .mk-cat-pills {
        display: inline-flex;
        background: #F1F5F9;
        padding: 4px;
        border-radius: 10px;
        gap: 4px;
    }

    .mk-cat-pill {
        padding: 6px 13px;
        border-radius: 7px;
        font-size: 12px;
        font-weight: 700;
        color: #64748B;
        text-decoration: none;
    }

    .mk-cat-pill.active {
        background: #FFFFFF;
        color: #091E3A;
        box-shadow: 0 2px 5px rgba(15, 30, 54, 0.08);
    }

    .mk-inv-red-card {
        background: #F8B4B4;
        border-radius: 14px;
        padding: 16px 22px;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .mk-inv-red-label {
        font-size: 10px;
        font-weight: 800;
        letter-spacing: 0.06em;
        color: #450A0A;
        text-transform: uppercase;
    }

    .mk-inv-red-num {
        font-size: 30px;
        font-weight: 900;
        color: #B91C1C;
        line-height: 1.1;
        margin: 4px 0;
    }

    .mk-inv-red-sub {
        font-size: 11px;
        font-weight: 700;
        color: #B91C1C;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .mk-cat-banner {
        margin-bottom: 18px;
        padding: 16px 22px;
        background: #FFFFFF;
        border: 1px solid var(--border);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
    }

    .mk-cat-chip {
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
    .mk-cat-chip.a { background: #B91C1C; color: #FFFFFF; }
    .mk-cat-chip.b { background: #FFEDD5; color: #9A3412; }
    .mk-cat-chip.c { background: #E2E8F0; color: #475569; }

    .mk-stat-aman {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 12px;
        font-weight: 700;
        color: #334155;
    }
    .mk-stat-aman::before {
        content: '';
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #0F2942;
    }

    .mk-stat-min {
        display: inline-block;
        padding: 3px 12px;
        border-radius: 999px;
        background: #FFEDD5;
        color: #9A3412;
        font-size: 11px;
        font-weight: 800;
    }

    .mk-stat-low {
        display: inline-block;
        padding: 3px 12px;
        border-radius: 999px;
        background: #FDE2E2;
        color: #B91C1C;
        font-size: 11px;
        font-weight: 800;
    }

    .mk-trend-box {
        background: #EFECE9;
        border-radius: 12px;
        padding: 24px 30px;
        margin-top: 24px;
    }

    @media (max-width: 900px) {
        .mk-inv-top { grid-template-columns: 1fr; }
    }
</style>
@endpush

@section('content')

{{-- ── TOP SUMMARY ROW ── --}}
<div class="mk-inv-top">
    <div class="mk-inv-green-card">
        <div class="mk-inv-green-label">KATEGORI INVENTARIS</div>
        <div class="mk-inv-green-counts">
            <a href="{{ route('mekanik.inventory', ['kat' => 'A']) }}" title="Filter Kategori A">A{{ $countA }}</a>
            <a href="{{ route('mekanik.inventory', ['kat' => 'B']) }}" title="Filter Kategori B">B{{ $countB }}</a>
            <a href="{{ route('mekanik.inventory', ['kat' => 'C']) }}" title="Filter Kategori C">C{{ $countC }}</a>
        </div>
    </div>

    <div class="mk-inv-center">
        <form method="GET" action="{{ route('mekanik.inventory') }}" class="mk-search-box">
            @if($kat)
                <input type="hidden" name="kat" value="{{ $kat }}">
            @endif
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text"
                   name="q"
                   value="{{ $search }}"
                   class="mk-search-input"
                   placeholder="Cari nama sparepart, kode SKU, atau kategori...">
        </form>

        <div class="mk-inv-filter-row">
            <div class="mk-cat-pills">
                <a href="{{ route('mekanik.inventory', ['q' => $search]) }}"
                   class="mk-cat-pill {{ empty($kat) ? 'active' : '' }}">Semua ({{ $countA + $countB + $countC }})</a>
                <a href="{{ route('mekanik.inventory', ['kat' => 'A', 'q' => $search]) }}"
                   class="mk-cat-pill {{ $kat === 'A' ? 'active' : '' }}">Kat. A ({{ $countA }})</a>
                <a href="{{ route('mekanik.inventory', ['kat' => 'B', 'q' => $search]) }}"
                   class="mk-cat-pill {{ $kat === 'B' ? 'active' : '' }}">Kat. B ({{ $countB }})</a>
                <a href="{{ route('mekanik.inventory', ['kat' => 'C', 'q' => $search]) }}"
                   class="mk-cat-pill {{ $kat === 'C' ? 'active' : '' }}">Kat. C ({{ $countC }})</a>
            </div>
        </div>
    </div>

    <div class="mk-inv-red-card">
        <div class="mk-inv-red-label">STOK KRITIS</div>
        <div class="mk-inv-red-num">{{ str_pad($criticalCount, 2, '0', STR_PAD_LEFT) }}</div>
        <div class="mk-inv-red-sub">
            <i class="fa-solid fa-circle-exclamation"></i>
            Perlu pemesanan segera
        </div>
    </div>
</div>

@if($kat)
    @php
        $katTitle = match($kat) {
            'A' => 'INVENTARIS - KATEGORI A (KRITIKAL)',
            'B' => 'INVENTARIS - KATEGORI B (NORMAL)',
            'C' => 'INVENTARIS - KATEGORI C (PENDUKUNG)',
            default => 'INVENTARIS SUKU CADANG',
        };
    @endphp
    <div class="mk-cat-banner">
        <div>
            <div style="font-size:14px;font-weight:800;color:#091E3A">{{ $katTitle }}</div>
            <div style="font-size:12px;color:var(--text-secondary);margin-top:2px">Menampilkan daftar komponen Kategori {{ $kat }} dari database.</div>
        </div>
        <a href="{{ route('mekanik.inventory') }}" class="btn btn-secondary btn-sm">
            &lt; Tampilkan Semua Kategori
        </a>
    </div>
@endif

{{-- ── INVENTORY TABLE ── --}}
<div class="card">
    <div class="card-body-p0">
        <div class="table-wrap">
            <table class="tc-table">
                <thead>
                    <tr>
                        <th>NAMA SPAREPART</th>
                        <th style="text-align:center">KATEGORI (ABC)</th>
                        <th style="text-align:center">FREKUENSI</th>
                        <th style="text-align:center">STOK</th>
                        <th style="text-align:center">BATAS MIN.</th>
                        <th>STATUS</th>
                        <th style="text-align:center">ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($spareparts as $sp)
                        @php
                            $abc       = strtoupper($sp->kategori_abc ?: 'A');
                            $chipClass = strtolower($abc);
                            $isLow     = $sp->stok < $sp->minimal_stok;
                            $isMin     = $sp->stok === $sp->minimal_stok;
                        @endphp
                        <tr>
                            <td>
                                <div style="font-weight:700;color:#091E3A">{{ $sp->nama_sparepart }}</div>
                                <div style="font-size:11px;color:var(--text-muted);font-weight:600">ID: {{ $sp->kode_sparepart }} · {{ $sp->kategori }}</div>
                            </td>
                            <td style="text-align:center">
                                <span class="mk-cat-chip {{ $chipClass }}">
                                    {{ $kat ? 'CAT ' . $abc : $abc }}
                                </span>
                            </td>
                            <td style="text-align:center;color:#475569;font-weight:600;font-size:13px">
                                {{ $sp->frekuensi ?: 'Rutin' }}
                            </td>
                            <td style="text-align:center;font-weight:800;font-size:14px;color:{{ ($isLow || $isMin) ? '#B91C1C' : '#091E3A' }}">
                                {{ str_pad($sp->stok, 2, '0', STR_PAD_LEFT) }} {{ $sp->satuan }}
                            </td>
                            <td style="text-align:center;color:#64748B;font-weight:600">
                                {{ str_pad($sp->minimal_stok, 2, '0', STR_PAD_LEFT) }}
                            </td>
                            <td>
                                @if($isLow)
                                    <span class="mk-stat-low">{{ $kat ? '• Kritis' : 'Stok Rendah' }}</span>
                                @elseif($isMin)
                                    <span class="mk-stat-min">Minimum</span>
                                @else
                                    <span class="mk-stat-aman">Aman</span>
                                @endif
                            </td>
                            <td style="text-align:center;white-space:nowrap">
                                <div class="d-flex gap-8" style="justify-content:center">
                                    <button type="button"
                                            class="btn btn-secondary btn-sm btn-icon"
                                            title="Edit Sparepart"
                                            onclick="openEditInvModal({{ json_encode($sp) }})">
                                        <i class="fa-solid fa-pen"></i>
                                    </button>

                                    <a href="{{ route('mekanik.inventory.request', ['sparepart_id' => $sp->id]) }}"
                                       class="btn btn-primary btn-sm btn-icon"
                                       title="Ajukan Pengadaan Stok">
                                        <i class="fa-solid fa-cart-plus"></i>
                                    </a>

                                    <form action="{{ route('mekanik.master-data.destroy', $sp->id) }}"
                                          method="POST"
                                          style="display:inline"
                                          onsubmit="return confirm('Hapus sparepart {{ addslashes($sp->nama_sparepart) }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm btn-icon" title="Hapus">
                                            <i class="fa-regular fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">
                                    <div class="empty-icon"><i class="fa-solid fa-box-open"></i></div>
                                    <div class="empty-title">Tidak Ada Sparepart</div>
                                    <div class="empty-text">Tidak ditemukan sparepart yang sesuai dengan filter pencarian.</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ── TREN PERBAIKAN TERATAS (100% DATABASE SYNCED) ── --}}
<div class="mk-trend-box">
    <div style="font-size:14px;font-weight:800;color:#091E3A;text-transform:uppercase">TREN PERBAIKAN TERATAS</div>
    <div style="font-size:11.5px;color:#64748B;margin-top:2px;margin-bottom:18px">
        {{ $trends['month_label'] }} · Dihitung dari {{ $trends['total_logs'] ?? 0 }} catatan perbaikan di database
    </div>

    @foreach($trends['items'] as $tr)
        <div style="margin-bottom:16px">
            <div style="width:100%;height:9px;background:#E4E2DF;border-radius:999px;overflow:hidden;margin-bottom:6px">
                <div style="width:{{ $tr['percent'] }}%;height:100%;background:{{ $tr['color'] }};border-radius:999px"></div>
            </div>
            <div style="display:flex;justify-content:space-between;font-size:12.5px;font-weight:700;color:#1E293B">
                <span>{{ $tr['label'] }} ({{ $tr['count'] ?? 0 }} kasus)</span>
                <span style="font-weight:800">{{ $tr['percent'] }}%</span>
            </div>
        </div>
    @endforeach

    <div style="display:flex;align-items:center;gap:12px;margin-top:18px">
        <div style="width:24px;height:24px;border-radius:6px;background:#FCD5D5;color:#B91C1C;display:flex;align-items:center;justify-content:center;font-weight:900;font-size:13px">!</div>
        <div style="font-size:12.5px;font-weight:700;color:#1E293B">
            Masalah pada bulan {{ strtolower(now()->translatedFormat('F')) }} terdeteksi paling banyak pada kategori <strong>{{ $trends['top_category'] }}</strong>
        </div>
    </div>
</div>

@endsection

@section('modals')
<div class="modal-overlay" id="modalEditInv">
    <div class="modal-box">
        <div class="modal-header">
            <div class="modal-title">Edit Sparepart</div>
            <button type="button" class="modal-close" onclick="closeModal('modalEditInv')">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form id="formEditInv" method="POST" action="">
            @csrf
            @method('PUT')
            <input type="hidden" name="tipe_item" value="sparepart">

            <div class="modal-body">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
                    <div class="form-group">
                        <label class="form-label">ITEM ID <span class="required">*</span></label>
                        <input type="text" name="kode_sparepart" id="ei_kode" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">NAMA SPAREPART <span class="required">*</span></label>
                        <input type="text" name="nama_sparepart" id="ei_nama" class="form-control" required>
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:14px">
                    <div class="form-group">
                        <label class="form-label">KATEGORI ABC</label>
                        <select name="kategori_abc" id="ei_abc" class="form-control">
                            <option value="A">Kategori A (Critical)</option>
                            <option value="B">Kategori B (Normal)</option>
                            <option value="C">Kategori C (Auxiliary)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">FREKUENSI</label>
                        <select name="frekuensi" id="ei_frek" class="form-control">
                            <option value="Sering">Sering</option>
                            <option value="Rutin">Rutin</option>
                            <option value="Bulanan">Bulanan</option>
                            <option value="Jarang">Jarang</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">SATUAN <span class="required">*</span></label>
                        <input type="text" name="satuan" id="ei_satuan" class="form-control" required>
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
                    <div class="form-group">
                        <label class="form-label">STOK SAAT INI <span class="required">*</span></label>
                        <input type="number" name="stok" id="ei_stok" min="0" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">BATAS MINIMAL <span class="required">*</span></label>
                        <input type="number" name="minimal_stok" id="ei_min" min="0" class="form-control" required>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom:0">
                    <label class="form-label">KETERANGAN</label>
                    <textarea name="keterangan" id="ei_ket" rows="2" class="form-control"></textarea>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalEditInv')">Batal</button>
                <button type="submit" class="btn btn-primary" style="background:#0F2647">Simpan Data</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function openEditInvModal(sp) {
        document.getElementById('formEditInv').action = '/mekanik/master-data/' + sp.id;
        document.getElementById('ei_kode').value   = sp.kode_sparepart || '';
        document.getElementById('ei_nama').value   = sp.nama_sparepart || '';
        document.getElementById('ei_abc').value    = (sp.kategori_abc || 'A').toUpperCase();
        document.getElementById('ei_frek').value   = sp.frekuensi || 'Rutin';
        document.getElementById('ei_satuan').value = sp.satuan || 'Pcs';
        document.getElementById('ei_stok').value   = sp.stok || 0;
        document.getElementById('ei_min').value    = sp.minimal_stok || 5;
        document.getElementById('ei_ket').value    = sp.keterangan || '';
        openModal('modalEditInv');
    }
</script>
@endpush

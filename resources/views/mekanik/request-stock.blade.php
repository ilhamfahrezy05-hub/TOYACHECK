@extends('layouts.app')

@section('title', 'Pengajuan Stok')
@section('page-title', 'Pengajuan Stok Suku Cadang')
@section('page-subtitle', 'Formulir pengajuan stok suku cadang bengkel ke Admin Distribusi & Pengadaan')

@section('page-actions')
    <a href="{{ route('mekanik.inventory') }}" class="btn btn-secondary">
        <i class="fa-solid fa-arrow-left"></i>
        Kembali ke Inventaris
    </a>
@endsection

@push('styles')
<style>
    .mk-req-container {
        background: #FFFFFF;
        border: 1.5px solid #2563EB;
        border-radius: 14px;
        padding: 36px 40px;
        max-width: 920px;
        box-shadow: 0 4px 16px rgba(15, 30, 54, 0.04);
    }

    .mk-req-label {
        display: block;
        font-size: 11px;
        font-weight: 700;
        color: #334155;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        margin-bottom: 8px;
    }

    .mk-req-input {
        width: 100%;
        padding: 13px 16px;
        background: #F4F6FA;
        border: 1.5px solid #E2E8F0;
        border-radius: 8px;
        font-family: inherit;
        font-size: 13.5px;
        color: #0F172A;
        outline: none;
        transition: all 0.18s;
    }

    .mk-req-input:focus {
        background: #FFFFFF;
        border-color: #091E3A;
    }

    .mk-prio-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 16px;
        margin-bottom: 24px;
    }

    .mk-prio-card {
        background: #F4F6FA;
        border: 1.5px solid #E2E8F0;
        border-radius: 10px;
        padding: 18px 16px;
        text-align: center;
        cursor: pointer;
        transition: all 0.18s;
    }

    .mk-prio-card.active.critical {
        background: #FFFDFB;
        border-color: #D97706;
        box-shadow: 0 4px 14px rgba(217, 119, 6, 0.14);
    }

    .mk-prio-card.active.normal {
        background: #F8FAFC;
        border-color: #091E3A;
        box-shadow: 0 4px 14px rgba(9, 30, 58, 0.14);
    }

    .mk-prio-card.active.support {
        background: #F8FAFC;
        border-color: #2563EB;
        box-shadow: 0 4px 14px rgba(37, 99, 235, 0.14);
    }

    .mk-prio-icon { font-size: 16px; color: #334155; margin-bottom: 6px; }
    .mk-prio-name { font-size: 13.5px; font-weight: 800; color: #091E3A; }
    .mk-prio-sub  { font-size: 11px; color: #64748B; margin-top: 2px; }

    @media (max-width: 768px) {
        .mk-req-container { padding: 24px 20px; }
        .mk-prio-grid { grid-template-columns: 1fr; }
    }
</style>
@endpush

@section('content')

<div class="mk-req-container">
    <h2 style="font-size:24px;font-weight:800;color:#091E3A;margin-bottom:26px">Pengajuan Stok</h2>

    <form action="{{ route('mekanik.inventory.request.store') }}" method="POST">
        @csrf

        {{-- Row 1: Spare Part Search/Selection & Quantity --}}
        <div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;margin-bottom:24px">
            <div>
                <label class="mk-req-label">SPARE PART SEARCH/SELECTION <span style="color:var(--danger)">*</span></label>
                <select name="sparepart_id" id="reqSparepartSelect" class="mk-req-input" onchange="syncPartName(this)">
                    <option value="">— Pilih dari Katalog Database (atau ketik manual di bawah) —</option>
                    @foreach($spareparts as $sp)
                        <option value="{{ $sp->id }}"
                                data-nama="{{ $sp->nama_sparepart }}"
                                {{ (string)$selectedPartId === (string)$sp->id ? 'selected' : '' }}>
                            {{ $sp->kode_sparepart }} — {{ $sp->nama_sparepart }} (Stok: {{ $sp->stok }} {{ $sp->satuan }})
                        </option>
                    @endforeach
                </select>
                <input type="text"
                       name="nama_sparepart"
                       id="reqNamaSparepart"
                       class="mk-req-input"
                       style="margin-top:8px"
                       placeholder="Atau ketik nama suku cadang baru...">
            </div>

            <div>
                <label class="mk-req-label">QUANTITY (UNITS) <span style="color:var(--danger)">*</span></label>
                <input type="number"
                       name="qty"
                       min="1"
                       value="1"
                       required
                       class="mk-req-input">
            </div>
        </div>

        {{-- Row 2: Priority Level --}}
        <label class="mk-req-label">PRIORITY LEVEL</label>
        <input type="hidden" name="priority_level" id="reqPriorityLevel" value="critical">

        <div class="mk-prio-grid">
            <div class="mk-prio-card active critical" onclick="selectPriority('critical', this)">
                <div class="mk-prio-icon"><i class="fa-solid fa-triangle-exclamation" style="color:#D97706"></i></div>
                <div class="mk-prio-name">Kritikal</div>
                <div class="mk-prio-sub">Armada Rusak</div>
            </div>

            <div class="mk-prio-card normal" onclick="selectPriority('normal', this)">
                <div class="mk-prio-icon"><i class="fa-regular fa-circle-check"></i></div>
                <div class="mk-prio-name">Normal</div>
                <div class="mk-prio-sub">Restock Stok</div>
            </div>

            <div class="mk-prio-card support" onclick="selectPriority('support', this)">
                <div class="mk-prio-icon"><i class="fa-regular fa-clipboard"></i></div>
                <div class="mk-prio-name">Pendukung</div>
                <div class="mk-prio-sub">Operasional Umum</div>
            </div>
        </div>

        {{-- Row 3: Expected Delivery Date, Vendor & Unit Terkait --}}
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:18px;margin-bottom:24px">
            <div>
                <label class="mk-req-label">EXPECTED DELIVERY DATE</label>
                <input type="date"
                       name="expected_delivery_date"
                       value="{{ now()->addDays(3)->format('Y-m-d') }}"
                       class="mk-req-input">
            </div>

            <div>
                <label class="mk-req-label">VENDOR TUJUAN (DARI DATABASE)</label>
                <select name="vendor_name" class="mk-req-input">
                    @forelse($vendors as $vd)
                        <option value="{{ $vd->nama_vendor }}">{{ $vd->nama_vendor }} ({{ $vd->keahlian ?? 'Umum' }})</option>
                    @empty
                        <option value="PT. Nusantara Perkasa">PT. Nusantara Perkasa</option>
                    @endforelse
                </select>
            </div>

            <div>
                <label class="mk-req-label">UNTUK PERBAIKAN UNIT (OPSIONAL)</label>
                <select name="maintenance_id" class="mk-req-input">
                    <option value="">— Stok Gudang Umum —</option>
                    @foreach($maintenances as $m)
                        <option value="{{ $m->id }}">
                            {{ strtoupper($m->vehicle->plat_nomor ?? '-') }} ({{ $m->deskripsi_masalah }})
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Row 4: Reason For Request --}}
        <div style="margin-bottom:24px">
            <label class="mk-req-label">ALASAN PENGAJUAN <span style="color:var(--danger)">*</span></label>
            <textarea name="alasan"
                      rows="4"
                      required
                      placeholder="Jelaskan urgensi kebutuhan suku cadang atau kondisi unit armada..."
                      class="mk-req-input"></textarea>
        </div>

        <div style="display:flex;align-items:center;justify-content:flex-end;gap:14px">
            <a href="{{ route('mekanik.inventory') }}" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-primary" style="background:#0F2647;padding:11px 28px">
                <i class="fa-solid fa-paper-plane" style="margin-right:6px"></i>
                Kirim Pengajuan
            </button>
        </div>
    </form>
</div>

@endsection

@push('scripts')
<script>
    function selectPriority(level, cardEl) {
        document.getElementById('reqPriorityLevel').value = level;
        document.querySelectorAll('.mk-prio-card').forEach(function (c) {
            c.classList.remove('active');
        });
        cardEl.classList.add('active');
    }

    function syncPartName(selectEl) {
        var opt = selectEl.options[selectEl.selectedIndex];
        var nama = opt ? (opt.getAttribute('data-nama') || '') : '';
        var input = document.getElementById('reqNamaSparepart');
        if (nama) {
            input.value = nama;
        }
    }

    syncPartName(document.getElementById('reqSparepartSelect'));
</script>
@endpush

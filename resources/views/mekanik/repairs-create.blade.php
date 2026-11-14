@extends('layouts.app')

@section('title', 'Tambah Log Perbaikan')
@section('page-title', 'Tambah Log Perbaikan Armada')
@section('page-subtitle', 'Pencatatan tindakan perbaikan unit dan sinkronisasi otomatis penggunaan suku cadang')

@section('page-actions')
    <a href="{{ route('mekanik.repairs') }}" class="btn btn-secondary">
        <i class="fa-solid fa-arrow-left"></i>
        Kembali ke Log Perbaikan
    </a>
@endsection

@push('styles')
<style>
    .mk-add-grid {
        display: grid;
        grid-template-columns: 1fr 280px;
        gap: 24px;
        align-items: start;
    }

    .mk-info-card {
        background: #FFFFFF;
        border: 1px solid var(--border);
        border-radius: 14px;
        padding: 26px 28px;
        box-shadow: 0 2px 8px rgba(15, 30, 54, 0.03);
        margin-bottom: 24px;
    }

    .mk-sec-bar-title {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 13px;
        font-weight: 800;
        color: #091E3A;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        margin-bottom: 20px;
    }

    .mk-sec-bar-title::before {
        content: '';
        width: 3.5px;
        height: 16px;
        background: #091E3A;
        border-radius: 2px;
    }

    .mk-parts-box {
        background: #F4F6FA;
        border: 1px solid var(--border);
        border-radius: 14px;
        padding: 24px 26px;
        margin-bottom: 24px;
    }

    .mk-parts-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 18px;
    }

    .mk-selected-part-item {
        background: #FFFFFF;
        border: 1px solid #E2E8F0;
        border-radius: 8px;
        padding: 14px 18px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 12px;
    }

    .mk-empty-part-drop {
        border: 1.5px dashed #CBD5E1;
        border-radius: 10px;
        padding: 26px;
        text-align: center;
        color: #64748B;
        cursor: pointer;
        transition: all 0.15s;
    }

    .mk-empty-part-drop:hover {
        border-color: #091E3A;
        color: #091E3A;
        background: rgba(255, 255, 255, 0.6);
    }

    /* ── RINGKASAN SISTEM CARD (RIGHT NAVY CARD) ── */
    .mk-summary-navy {
        background: #0F2647;
        color: #FFFFFF;
        border-radius: 12px;
        padding: 24px 22px;
        box-shadow: 0 12px 28px rgba(9, 30, 58, 0.16);
    }

    .mk-summary-title {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 13.5px;
        font-weight: 800;
        margin-bottom: 18px;
    }

    .mk-summary-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-size: 12px;
        padding: 9px 0;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .mk-summary-row:last-child { border-bottom: none; }
    .mk-summary-lbl { color: #94A3B8; }
    .mk-summary-val { font-weight: 700; color: #FFFFFF; text-align: right; }

    @media (max-width: 900px) {
        .mk-add-grid { grid-template-columns: 1fr; }
    }
</style>
@endpush

@section('content')

<form action="{{ route('mekanik.repairs.store') }}" method="POST" id="formCreateRepair">
    @csrf

    <div class="mk-add-grid">
        {{-- ── LEFT COLUMN ── --}}
        <div>
            <div class="mk-info-card">
                <div class="mk-sec-bar-title">INFORMASI PERBAIKAN</div>

                @if(isset($openChecklists) && $openChecklists->isNotEmpty())
                    <div class="form-group">
                        <label class="form-label">Tindak Lanjut Temuan Checklist (Opsional — Otomatis Isi Armada & Kendala)</label>
                        <select name="checklist_id" class="form-control" onchange="fillFromChecklist(this)">
                            <option value="">— Input Manual Tanpa Rujukan Checklist —</option>
                            @foreach($openChecklists as $chk)
                                @php
                                    $temuan = $chk->items->pluck('item')->join(', ');
                                @endphp
                                <option value="{{ $chk->id }}"
                                        data-vehicle="{{ $chk->vehicle_id }}"
                                        data-driver="{{ $chk->driver->name ?? '' }}"
                                        data-temuan="{{ $temuan }}">
                                    {{ strtoupper($chk->vehicle->plat_nomor ?? '-') }} · {{ $chk->tanggal->format('d/m/Y') }} ({{ \Illuminate\Support\Str::limit($temuan ?: 'Kendala Checklist', 45) }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                    <div class="form-group">
                        <label class="form-label">Tanggal Perbaikan <span class="required">*</span></label>
                        <input type="date"
                               name="tanggal_maintenance"
                               value="{{ now()->format('Y-m-d') }}"
                               required
                               class="form-control">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Pilih Armada <span class="required">*</span></label>
                        <select name="vehicle_id" id="crVehicleSelect" required class="form-control" onchange="autoFillDriver(this)">
                            <option value="">— Pilih Armada —</option>
                            @foreach($vehicles as $v)
                                <option value="{{ $v->id }}"
                                        data-driver="{{ $v->assignedDriver->name ?? '' }}">
                                    {{ strtoupper($v->plat_nomor) }} — {{ $v->jenis_kendaraan }} ({{ ucfirst($v->status) }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px">
                    <div class="form-group">
                        <label class="form-label">Nama Driver</label>
                        <input type="text"
                               name="nama_driver"
                               id="crNamaDriver"
                               placeholder="Contoh: Budi Santoso"
                               class="form-control">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Kategori Kendala</label>
                        <select name="kategori_kendala" class="form-control">
                            <option value="Kelistrikan">Kelistrikan</option>
                            <option value="Kaki-kaki">Kaki-kaki & Pengereman</option>
                            <option value="Ban">Roda & Ban</option>
                            <option value="Mesin">Mesin & Fluida</option>
                            <option value="Service Ringan">Service Ringan</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Estimasi Downtime</label>
                        <input type="text"
                               name="estimasi_downtime"
                               value="2.5 Jam"
                               oninput="document.getElementById('sumDowntime').textContent = this.value || '-'"
                               class="form-control">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Status Pengerjaan (Sinkron Status Armada di Database)</label>
                    <select name="status_maintenance" class="form-control">
                        <option value="completed" selected>Selesai Dikerjakan (Armada Otomatis Siap Jalan)</option>
                        <option value="in_progress">Sedang Dikerjakan (Armada Berstatus Sedang Diperbaiki)</option>
                        <option value="pending">Pending / Menunggu Suku Cadang (Armada Tidak Layak Jalan)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Deskripsi Masalah <span class="required">*</span></label>
                    <textarea name="deskripsi_masalah"
                              id="crDeskripsiMasalah"
                              rows="3"
                              required
                              placeholder="Jelaskan detail kerusakan yang ditemukan..."
                              class="form-control"></textarea>
                </div>

                <div class="form-group" style="margin-bottom:0">
                    <label class="form-label">Solusi / Tindakan Perbaikan</label>
                    <textarea name="solusi"
                              rows="3"
                              placeholder="Langkah-langkah yang diambil untuk memperbaiki masalah..."
                              class="form-control"></textarea>
                </div>
            </div>

            {{-- Card 2: Suku Cadang Digunakan --}}
            <div class="mk-parts-box">
                <div class="mk-parts-header">
                    <div class="mk-sec-bar-title" style="margin-bottom:0">SUKU CADANG DIGUNAKAN</div>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="openModal('modalSelectSparePart')">
                        <i class="fa-solid fa-plus"></i>
                        Pilih suku cadang
                    </button>
                </div>

                <div id="selectedPartsContainer"></div>

                <div class="mk-empty-part-drop" onclick="openModal('modalSelectSparePart')">
                    <i class="fa-regular fa-clipboard" style="font-size:20px;margin-bottom:6px;display:block"></i>
                    <span style="font-size:12px;font-weight:700">Klik untuk memilih & menambahkan suku cadang dari database inventory</span>
                </div>
            </div>
        </div>

        {{-- ── RIGHT COLUMN: RINGKASAN SISTEM ── --}}
        <div>
            <div class="mk-summary-navy">
                <div class="mk-summary-title">
                    <i class="fa-solid fa-circle-info" style="color:#FDE047"></i>
                    Ringkasan Sistem
                </div>

                <div class="mk-summary-row">
                    <span class="mk-summary-lbl">ID Transaksi</span>
                    <span class="mk-summary-val">{{ $nextLogNumber }}</span>
                </div>

                <div class="mk-summary-row">
                    <span class="mk-summary-lbl">Estimasi Downtime</span>
                    <span class="mk-summary-val" id="sumDowntime">2.5 Jam</span>
                </div>

                <div class="mk-summary-row">
                    <span class="mk-summary-lbl">Item Dipilih</span>
                    <span class="mk-summary-val" id="sumPartCount">0 Item</span>
                </div>

                <div class="mk-summary-row">
                    <span class="mk-summary-lbl">Status Inventory</span>
                    <span class="mk-summary-val">Sinkron Otomatis</span>
                </div>
            </div>
        </div>
    </div>

    <div style="display:flex;align-items:center;justify-content:flex-end;gap:14px;padding-top:12px;border-top:1px solid var(--border)">
        <a href="{{ route('mekanik.repairs') }}" class="btn btn-secondary">Batalkan</a>
        <button type="submit" class="btn btn-primary" style="background:#0F2647;padding:11px 24px">
            <i class="fa-regular fa-floppy-disk"></i>
            Simpan Log Perbaikan
        </button>
    </div>
</form>

@endsection

@section('modals')
{{-- ── MODAL SELECT SPARE PART ── --}}
<div class="modal-overlay" id="modalSelectSparePart">
    <div class="modal-box modal-lg">
        <div class="modal-header">
            <div>
                <div class="modal-title">Select Spare Part</div>
                <div style="font-size:12px;color:var(--text-secondary)">Add components to Fleet Repair ID: #{{ $nextLogNumber }}</div>
            </div>
            <button type="button" class="modal-close" onclick="closeModal('modalSelectSparePart')">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div class="modal-body">
            <div style="margin-bottom:14px;position:relative">
                <i class="fa-solid fa-magnifying-glass" style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#94A3B8;font-size:13px"></i>
                <input type="text"
                       id="spPickerSearch"
                       oninput="filterSpPicker(this.value)"
                       placeholder="Search by name, ID, or specification..."
                       class="form-control"
                       style="padding-left:38px">
            </div>

            <div style="max-height:360px;overflow-y:auto;border:1px solid var(--border);border-radius:10px">
                <table class="tc-table">
                    <thead>
                        <tr>
                            <th>PART NAME & ID</th>
                            <th style="text-align:center">CATEGORY</th>
                            <th>STOCK STATUS</th>
                            <th style="text-align:right">ACTION</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($spareparts as $sp)
                            @php
                                $abc   = strtoupper($sp->kategori_abc ?: 'A');
                                $isLow = $sp->stok <= $sp->minimal_stok;
                                $isOut = $sp->stok <= 0;
                            @endphp
                            <tr class="sp-picker-row"
                                data-search="{{ strtolower($sp->nama_sparepart . ' ' . $sp->kode_sparepart . ' ' . $sp->kategori) }}">
                                <td>
                                    <div style="font-weight:700;color:#091E3A">{{ $sp->nama_sparepart }}</div>
                                    <div style="font-size:11px;color:var(--text-muted)">ID: {{ $sp->kode_sparepart }}</div>
                                </td>
                                <td style="text-align:center">
                                    <span class="badge badge-neutral">CATEGORY {{ $abc }}</span>
                                </td>
                                <td style="font-weight:700;color:{{ $isOut ? '#94A3B8' : ($isLow ? '#DC2626' : '#334155') }}">
                                    @if($isOut)
                                        Out of Stock
                                    @elseif($isLow)
                                        • Low Stock ({{ $sp->stok }} {{ $sp->satuan }})
                                    @else
                                        {{ $sp->stok }} {{ $sp->satuan }} in bin
                                    @endif
                                </td>
                                <td style="text-align:right">
                                    @if($isOut)
                                        <button type="button" disabled class="btn btn-secondary btn-sm">OUT OF STOCK</button>
                                    @else
                                        <button type="button"
                                                id="btnPickSp_{{ $sp->id }}"
                                                class="btn btn-primary btn-sm"
                                                style="background:#0F2647"
                                                onclick="addPartToLog({{ $sp->id }}, '{{ addslashes($sp->nama_sparepart) }}', '{{ $abc }}', '{{ $sp->satuan }}')">
                                            ADD TO LOG
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-primary" style="background:#0F2647" onclick="closeModal('modalSelectSparePart')">Done</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    var selectedParts = {};

    function fillFromChecklist(selectEl) {
        var opt = selectEl.options[selectEl.selectedIndex];
        if (!opt || !opt.value) return;
        var vehId  = opt.getAttribute('data-vehicle') || '';
        var drv    = opt.getAttribute('data-driver') || '';
        var temuan = opt.getAttribute('data-temuan') || '';

        if (vehId) document.getElementById('crVehicleSelect').value = vehId;
        if (drv)   document.getElementById('crNamaDriver').value = drv;
        if (temuan) document.getElementById('crDeskripsiMasalah').value = 'Temuan Checklist: ' + temuan;
    }

    function autoFillDriver(selectEl) {
        var opt = selectEl.options[selectEl.selectedIndex];
        var drv = opt ? (opt.getAttribute('data-driver') || '') : '';
        var input = document.getElementById('crNamaDriver');
        if (drv) {
            input.value = drv;
        }
    }

    function filterSpPicker(q) {
        var needle = (q || '').toLowerCase().trim();
        document.querySelectorAll('.sp-picker-row').forEach(function (row) {
            var hay = row.getAttribute('data-search') || '';
            row.style.display = (!needle || hay.indexOf(needle) !== -1) ? '' : 'none';
        });
    }

    function addPartToLog(id, nama, abc, satuan) {
        if (selectedParts[id]) {
            selectedParts[id].qty += 1;
        } else {
            selectedParts[id] = { id: id, nama: nama, abc: abc, satuan: satuan, qty: 1 };
        }
        renderSelectedParts();

        var btn = document.getElementById('btnPickSp_' + id);
        if (btn) {
            btn.textContent = 'ADDED (' + selectedParts[id].qty + ')';
            btn.style.background = '#16A34A';
        }
    }

    function removePartFromLog(id) {
        delete selectedParts[id];
        renderSelectedParts();

        var btn = document.getElementById('btnPickSp_' + id);
        if (btn) {
            btn.textContent = 'ADD TO LOG';
            btn.style.background = '#0F2647';
        }
    }

    function updatePartQty(id, val) {
        var q = parseInt(val, 10);
        if (selectedParts[id]) {
            selectedParts[id].qty = (isNaN(q) || q < 1) ? 1 : q;
        }
    }

    function renderSelectedParts() {
        var container = document.getElementById('selectedPartsContainer');
        container.innerHTML = '';

        var keys = Object.keys(selectedParts);
        var countEl = document.getElementById('sumPartCount');
        if (countEl) countEl.textContent = keys.length + ' Item';

        keys.forEach(function (k, idx) {
            var item = selectedParts[k];
            var div = document.createElement('div');
            div.className = 'mk-selected-part-item';
            div.innerHTML =
                '<input type="hidden" name="spareparts[' + idx + '][id]" value="' + item.id + '">' +
                '<div>' +
                    '<div style="font-size:13px;font-weight:700;color:#091E3A">' + item.nama + '</div>' +
                    '<div style="font-size:11px;color:#64748B">Criteria ' + item.abc + '</div>' +
                '</div>' +
                '<div style="display:flex;align-items:center;gap:16px">' +
                    '<div style="text-align:right">' +
                        '<div style="font-size:9px;font-weight:800;color:#94A3B8;text-transform:uppercase">JUMLAH</div>' +
                        '<div style="display:flex;align-items:center;gap:6px">' +
                            '<input type="number" name="spareparts[' + idx + '][qty]" value="' + item.qty + '" min="1" ' +
                                   'onchange="updatePartQty(' + item.id + ', this.value)" ' +
                                   'style="width:58px;padding:4px 6px;border:1px solid #CBD5E1;border-radius:6px;font-size:12.5px;font-weight:700;color:#091E3A;text-align:center">' +
                            '<span style="font-size:12px;font-weight:700;color:#091E3A">' + item.satuan + '</span>' +
                        '</div>' +
                    '</div>' +
                    '<button type="button" class="btn btn-danger btn-sm btn-icon" onclick="removePartFromLog(' + item.id + ')" title="Hapus">' +
                        '<i class="fa-regular fa-trash-can"></i>' +
                    '</button>' +
                '</div>';
            container.appendChild(div);
        });
    }
</script>
@endpush

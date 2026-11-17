@extends('layouts.app')

@section('title', 'Detail Transaksi Baru')
@section('page-title', 'Detail Transaksi Baru')
@section('page-subtitle', 'Pastikan data yang dimasukkan sesuai dengan invoice fisik.')

@section('page-actions')
    <span style="background:#F1F5F9;border:1px solid #CBD5E1;color:#334155;font-size:10.5px;font-weight:800;padding:5px 12px;border-radius:4px;letter-spacing:.06em">
        MANUAL ENTRY
    </span>
@endsection

@push('styles')
<style>
    .dt-card {
        background: #FFFFFF;
        border: 1px solid #E2E8F0;
        border-radius: 10px;
        padding: 28px;
        max-width: 920px;
        box-shadow: 0 2px 10px rgba(15,23,42,0.03);
    }
    .dt-label {
        display: block;
        font-size: 10.5px;
        font-weight: 800;
        color: #475569;
        letter-spacing:.05em;
        text-transform: uppercase;
        margin-bottom: 6px;
    }
    .dt-dropzone {
        border: 2px dashed #CBD5E1;
        border-radius: 10px;
        padding: 34px 20px;
        text-align: center;
        background: #FAFCFF;
        transition: border-color .15s, background .15s;
        cursor: pointer;
    }
    .dt-dropzone:hover {
        border-color: #2563EB;
        background: #EFF6FF;
    }
</style>
@endpush

@section('content')

<div class="dt-card">
    <form method="POST" action="{{ route('pengadaan.reports.store') }}" enctype="multipart/form-data" id="formAddHistory">
        @csrf
        @if($prefillRequest)
            <input type="hidden" name="sparepart_request_id" value="{{ $prefillRequest->id }}">
        @endif

        {{-- 1. DAFTAR SUKU CADANG (MULTI-ROW DINAMIS FIGMA 1262:163 & 1262:779) --}}
        <div style="font-size:13.5px;font-weight:800;color:#0F172A;padding-bottom:10px;border-bottom:1px solid #E2E8F0;margin-bottom:14px">
            Daftar Suku Cadang
        </div>

        <datalist id="sparepartCatalogList">
            @foreach($spareparts as $sp)
                <option value="{{ $sp->nama_sparepart }}" data-sku="{{ $sp->kode_sparepart }}">{{ $sp->kode_sparepart }} (Stok: {{ $sp->stok }})</option>
            @endforeach
        </datalist>

        <div class="table-wrap" style="margin-bottom:12px">
            <table style="width:100%;border-collapse:collapse" id="itemsTable">
                <thead>
                    <tr style="border-bottom:1px solid #E2E8F0">
                        <th style="width:44px;font-size:10px;font-weight:800;color:#64748B;text-align:left;padding:8px 6px">NO</th>
                        <th style="font-size:10px;font-weight:800;color:#64748B;text-align:left;padding:8px 6px">NAMA SUKU CADANG</th>
                        <th style="width:220px;font-size:10px;font-weight:800;color:#64748B;text-align:left;padding:8px 6px">SKU/KODE BARANG</th>
                        <th style="width:120px;font-size:10px;font-weight:800;color:#64748B;text-align:left;padding:8px 6px">KUANTITAS</th>
                        <th style="width:44px"></th>
                    </tr>
                </thead>
                <tbody id="itemsTbody">
                    <tr class="item-row">
                        <td class="row-num" style="padding:8px 6px;font-weight:700;color:#475569">1</td>
                        <td style="padding:8px 6px">
                            <input type="text"
                                   name="items[0][nama_sparepart]"
                                   list="sparepartCatalogList"
                                   value="{{ old('items.0.nama_sparepart', $prefillRequest?->nama_sparepart ?? '') }}"
                                   class="form-control"
                                   placeholder="Nama Suku Cadang"
                                   oninput="autoFillSku(this)"
                                   required>
                        </td>
                        <td style="padding:8px 6px">
                            <input type="text"
                                   name="items[0][kode_barang]"
                                   value="{{ old('items.0.kode_barang', $prefillRequest?->sparepart?->kode_sparepart ?? '') }}"
                                   class="form-control sku-input"
                                   placeholder="Kode barang">
                        </td>
                        <td style="padding:8px 6px">
                            <input type="number"
                                   name="items[0][qty]"
                                   value="{{ old('items.0.qty', $prefillRequest?->qty ?? 1) }}"
                                   min="1"
                                   class="form-control"
                                   required>
                        </td>
                        <td style="padding:8px 6px;text-align:center">
                            <button type="button" class="btn btn-sm btn-icon" style="background:transparent;color:#EF4444;display:none" onclick="removeRow(this)" title="Hapus Baris">
                                <i class="fa-regular fa-trash-can"></i>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div style="margin-bottom:24px">
            <button type="button"
                    onclick="addSparepartRow()"
                    style="background:none;border:none;color:#0F172A;font-size:11.5px;font-weight:800;cursor:pointer;letter-spacing:.04em">
                + TAMBAH BARIS
            </button>
        </div>

        {{-- 2. METADATA TRANSAKSI GRID (2x2) --}}
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:18px;margin-bottom:22px">
            <div>
                <label class="dt-label" for="vendor_id">NAMA VENDOR</label>
                <select id="vendor_id" name="vendor_id" class="form-control">
                    <option value="">Pilih Vendor</option>
                    @foreach($vendors as $v)
                        <option value="{{ $v->id }}"
                            {{ ($prefillRequest && $prefillRequest->vendor_name === $v->nama_vendor) ? 'selected' : '' }}>
                            {{ $v->nama_vendor }} ({{ $v->keahlian ?: 'Umum' }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="dt-label" for="total_harga">JUMLAH TOTAL TRANSAKSI <span class="required">*</span></label>
                <div style="position:relative">
                    <span style="position:absolute;left:12px;top:50%;transform:translateY(-50%);font-size:13px;font-weight:700;color:#64748B">Rp</span>
                    <input type="number"
                           id="total_harga"
                           name="total_harga"
                           value="{{ old('total_harga', 0) }}"
                           min="0"
                           step="any"
                           class="form-control"
                           style="padding-left:36px"
                           placeholder="0.000"
                           required>
                </div>
            </div>

            <div>
                <label class="dt-label" for="tanggal_pembelian">TANGGAL PEMBELIAN <span class="required">*</span></label>
                <input type="date"
                       id="tanggal_pembelian"
                       name="tanggal_pembelian"
                       value="{{ old('tanggal_pembelian', now()->toDateString()) }}"
                       class="form-control"
                       required>
            </div>

            <div>
                <label class="dt-label" for="waktu_pembelian">WAKTU PEMBELIAN</label>
                <input type="time"
                       id="waktu_pembelian"
                       name="waktu_pembelian"
                       value="{{ old('waktu_pembelian', now()->format('H:i')) }}"
                       class="form-control">
            </div>
        </div>

        {{-- 3. UPLOAD BUKTI TRANSFER (TRANSFER PROOF) --}}
        <div style="margin-bottom:26px">
            <label class="dt-label">UPLOAD BUKTI TRANSFER (TRANSFER PROOF)</label>
            <div class="dt-dropzone" onclick="document.getElementById('bukti_pembayaran').click()">
                <div style="width:42px;height:42px;border-radius:8px;background:#DBEAFE;color:#2563EB;display:inline-flex;align-items:center;justify-content:center;font-size:18px;margin-bottom:10px">
                    <i class="fa-solid fa-cloud-arrow-up"></i>
                </div>
                <div style="font-size:13px;font-weight:700;color:#0F172A" id="dropzoneTitle">Drag &amp; drop berkas di sini</div>
                <div style="font-size:11.5px;color:#64748B;margin-top:3px;margin-bottom:14px">
                    Mendukung Format JPG, PNG, atau PDF (Max. 5MB)
                </div>
                <button type="button" class="btn btn-sm" style="background:#090D16;color:#FFFFFF;font-weight:800;font-size:11px;padding:7px 18px;border-radius:4px">
                    PILIH BERKAS
                </button>
                <input type="file"
                       id="bukti_pembayaran"
                       name="bukti_pembayaran"
                       accept=".jpg,.jpeg,.png,.webp,.pdf"
                       style="display:none"
                       onchange="handleFileProofSelected(this)">
            </div>
            @error('bukti_pembayaran')<div class="invalid-feedback" style="display:block">{{ $message }}</div>@enderror
        </div>

        {{-- 4. TOMBOL AKSI BAWAH --}}
        <div style="display:flex;justify-content:flex-end;gap:12px">
            <a href="{{ route('pengadaan.reports') }}"
               class="btn"
               style="background:#FFFFFF;border:1px solid #CBD5E1;color:#0F172A;font-weight:700;padding:10px 26px">
                Batal
            </a>
            <button type="submit"
                    class="btn"
                    style="background:#090D16;color:#FFFFFF;font-weight:700;padding:10px 28px">
                Simpan Data
            </button>
        </div>
    </form>
</div>

@endsection

@push('scripts')
<script>
    let rowIdx = 1;

    function addSparepartRow() {
        const tbody = document.getElementById('itemsTbody');
        const tr = document.createElement('tr');
        tr.className = 'item-row';
        tr.innerHTML = `
            <td class="row-num" style="padding:8px 6px;font-weight:700;color:#475569"></td>
            <td style="padding:8px 6px">
                <input type="text" name="items[${rowIdx}][nama_sparepart]" list="sparepartCatalogList" class="form-control" placeholder="Nama Suku Cadang" oninput="autoFillSku(this)" required>
            </td>
            <td style="padding:8px 6px">
                <input type="text" name="items[${rowIdx}][kode_barang]" class="form-control sku-input" placeholder="SKU/Part Number">
            </td>
            <td style="padding:8px 6px">
                <input type="number" name="items[${rowIdx}][qty]" value="1" min="1" class="form-control" required>
            </td>
            <td style="padding:8px 6px;text-align:center">
                <button type="button" class="btn btn-sm btn-icon" style="background:transparent;color:#EF4444" onclick="removeRow(this)" title="Hapus Baris">
                    <i class="fa-regular fa-trash-can"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
        rowIdx++;
        renumberRows();
    }

    function removeRow(btn) {
        const rows = document.querySelectorAll('#itemsTbody .item-row');
        if (rows.length <= 1) return;
        btn.closest('tr').remove();
        renumberRows();
    }

    function renumberRows() {
        const rows = document.querySelectorAll('#itemsTbody .item-row');
        rows.forEach((tr, idx) => {
            tr.querySelector('.row-num').textContent = idx + 1;
            const delBtn = tr.querySelector('button');
            if (delBtn) {
                delBtn.style.display = rows.length > 1 ? 'inline-flex' : 'none';
            }
        });
    }

    function autoFillSku(input) {
        const val = input.value;
        const options = document.querySelectorAll('#sparepartCatalogList option');
        const skuInput = input.closest('tr').querySelector('.sku-input');
        if (!skuInput) return;
        options.forEach(opt => {
            if (opt.value === val && opt.dataset.sku) {
                skuInput.value = opt.dataset.sku;
            }
        });
    }

    function handleFileProofSelected(input) {
        const titleEl = document.getElementById('dropzoneTitle');
        if (input.files && input.files[0]) {
            titleEl.innerHTML = `<span style="color:#16A34A"><i class="fa-solid fa-circle-check"></i> Berkas terpilih: ${input.files[0].name}</span>`;
        }
    }
</script>
@endpush

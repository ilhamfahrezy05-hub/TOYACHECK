@extends('layouts.app')

@section('title', 'Catatan Perbaikan')
@section('page-title', 'Catatan Perbaikan')
@section('page-subtitle', 'Monitoring riwayat servis armada dan penggunaan suku cadang oleh tim mekanik')

@section('page-actions')
    <div class="d-flex gap-8 align-center">
        <button type="button" class="btn btn-secondary" onclick="openProcurementModal()">
            <i class="fa-solid fa-paper-plane"></i>
            Ajukan ke Pengadaan
        </button>
        <a href="{{ route('maintenances.export-pdf', ['status' => $status]) }}" target="_blank" class="btn btn-primary" id="btnExportPdfMaintenance">
            <i class="fa-solid fa-file-pdf"></i>
            Export PDF
        </a>
    </div>
@endsection

{{-- ===================== CONTENT ===================== --}}
@section('content')

{{-- ── STAT CARDS ── --}}
<div class="stats-grid" style="grid-template-columns: repeat(4, 1fr); margin-bottom: 24px;">

    <div class="stat-card">
        <div class="stat-icon blue"><i class="fa-solid fa-clipboard-list"></i></div>
        <div class="stat-info">
            <div class="stat-label">Total Log Perbaikan</div>
            <div class="stat-value">{{ $stats['total'] }}</div>
            <div class="stat-change">Riwayat perbaikan unit</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon amber"><i class="fa-solid fa-clock"></i></div>
        <div class="stat-info">
            <div class="stat-label">Menunggu (Pending)</div>
            <div class="stat-value">{{ $stats['pending'] }}</div>
            <div class="stat-change">Unit antre diservis</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon violet"><i class="fa-solid fa-screwdriver-wrench"></i></div>
        <div class="stat-info">
            <div class="stat-label">Sedang Dikerjakan</div>
            <div class="stat-value">{{ $stats['in_progress'] }}</div>
            <div class="stat-change">Proses penanganan mekanik</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon green"><i class="fa-solid fa-circle-check"></i></div>
        <div class="stat-info">
            <div class="stat-label">Selesai</div>
            <div class="stat-value">{{ $stats['completed'] }}</div>
            <div class="stat-change">Unit siap beroperasi kembali</div>
        </div>
    </div>

</div>

{{-- ── TABLE CARD ── --}}
<div class="card">
    <div class="card-header">
        <div>
            <div class="card-title">Daftar Perbaikan Armada</div>
            <div class="card-subtitle">Total {{ $maintenances->total() }} catatan perbaikan</div>
        </div>

        <div class="d-flex gap-8 align-center">
            <div style="position:relative">
                <i class="fa-solid fa-magnifying-glass"
                   style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:var(--text-muted);font-size:13px;pointer-events:none"></i>
                <input
                    type="text"
                    id="maintenanceSearch"
                    placeholder="Cari armada / mekanik..."
                    class="form-control"
                    style="padding-left:32px;width:230px"
                    oninput="applyMaintenanceFilter()">
            </div>

            <select id="maintenanceStatusFilter" class="form-control" style="width:160px" onchange="applyMaintenanceFilter()">
                <option value="">Semua Status</option>
                <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="in_progress" {{ $status === 'in_progress' ? 'selected' : '' }}>Sedang Dikerjakan</option>
                <option value="completed" {{ $status === 'completed' ? 'selected' : '' }}>Selesai</option>
            </select>
        </div>
    </div>

    <div class="card-body-p0">
        <div class="table-wrap">
            <table class="tc-table" id="maintenanceTable">
                <thead>
                    <tr>
                        <th style="width:52px">#</th>
                        <th>Tanggal</th>
                        <th>Armada</th>
                        <th>Mekanik</th>
                        <th>Kerusakan / Masalah</th>
                        <th>Sparepart &amp; Pengajuan</th>
                        <th>Status</th>
                        <th style="width:140px;text-align:center">Aksi</th>
                    </tr>
                </thead>
                <tbody id="maintenanceTableBody">
                    @forelse ($maintenances as $index => $item)
                    @php
                        $statusMeta = match($item->status_maintenance) {
                            'pending'     => ['class' => 'badge-warning', 'icon' => 'fa-clock',              'label' => 'Pending'],
                            'in_progress' => ['class' => 'badge-info',    'icon' => 'fa-screwdriver-wrench', 'label' => 'Sedang Dikerjakan'],
                            'completed'   => ['class' => 'badge-success', 'icon' => 'fa-circle-check',       'label' => 'Selesai'],
                            default       => ['class' => 'badge-neutral', 'icon' => 'fa-circle-question',    'label' => $item->status_maintenance],
                        };
                    @endphp
                    <tr
                        data-armada="{{ strtolower(($item->vehicle->plat_nomor ?? '') . ' ' . ($item->vehicle->merk ?? '')) }}"
                        data-mekanik="{{ strtolower($item->mekanik->name ?? '') }}"
                        data-status="{{ $item->status_maintenance }}">

                        <td style="color:var(--text-muted);font-size:12px">
                            {{ $maintenances->firstItem() + $index }}
                        </td>

                        {{-- Tanggal --}}
                        <td style="white-space:nowrap">
                            <div style="font-weight:600;font-size:13px">
                                {{ $item->tanggal_maintenance->translatedFormat('d M Y') }}
                            </div>
                            <div style="font-size:11px;color:var(--text-muted)">
                                {{ $item->tanggal_maintenance->format('H:i') }} WIB
                            </div>
                        </td>

                        {{-- Armada --}}
                        <td>
                            <div class="d-flex align-center gap-8">
                                <div style="width:32px;height:32px;border-radius:8px;background:var(--primary-subtle);display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:13px;color:var(--primary)">
                                    <i class="fa-solid fa-truck"></i>
                                </div>
                                <div>
                                    <div style="font-weight:600;font-family:monospace;font-size:13px">{{ $item->vehicle->merk ?? '—' }}</div>
                            <div style="font-weight:600;font-family:monospace;font-size:13px">{{ $item->vehicle->jenis_kendaraan ?? '—' }}</div>
                                    <span style="background:#0F1E36;color:#fff;padding:1px 7px;border-radius:4px;font-size:10px;font-weight:700;font-family:monospace">
                                        {{ strtoupper($item->vehicle->plat_nomor ?? '-') }}
                                    </span>
                                </div>
                            </div>
                        </td>

                        {{-- Mekanik --}}
                        <td>
                            <div class="d-flex align-center gap-8">
                                <div class="user-avatar" style="width:28px;height:28px;font-size:11px;flex-shrink:0">
                                    {{ strtoupper(substr($item->mekanik->name ?? 'M', 0, 1)) }}
                                </div>
                                <span style="font-weight:500">{{ $item->mekanik->name ?? '—' }}</span>
                            </div>
                        </td>

                        {{-- Deskripsi Masalah --}}
                        <td style="max-width:260px">
                            <div style="font-weight:500;color:var(--text-primary);line-height:1.4">
                                {{ \Illuminate\Support\Str::limit($item->deskripsi_masalah, 65) }}
                            </div>
                            @if($item->solusi)
                                <div style="font-size:11px;color:var(--success);margin-top:2px">
                                    <i class="fa-solid fa-check"></i> {{ \Illuminate\Support\Str::limit($item->solusi, 45) }}
                                </div>
                            @endif
                        </td>

                        {{-- Sparepart Terpakai & Pengajuan ke Pengadaan --}}
                        <td>
                            @if($item->details->isNotEmpty())
                                <div class="d-flex gap-4" style="flex-wrap:wrap">
                                    @foreach($item->details->take(2) as $det)
                                        <span class="badge badge-neutral" style="font-size:11px" title="{{ $det->nama_sparepart }}">
                                            {{ \Illuminate\Support\Str::limit($det->nama_sparepart, 16) }} ({{ $det->qty }})
                                        </span>
                                    @endforeach
                                    @if($item->details->count() > 2)
                                        <span class="badge badge-neutral" style="font-size:11px">+{{ $item->details->count() - 2 }}</span>
                                    @endif
                                </div>
                            @elseif($item->sparepartRequests->isEmpty())
                                <span style="color:var(--text-muted);font-size:12px">—</span>
                            @endif

                            @if($item->sparepartRequests->isNotEmpty())
                                <div class="d-flex gap-4" style="flex-wrap:wrap;margin-top:4px">
                                    @foreach($item->sparepartRequests->take(2) as $req)
                                        <span class="badge {{ $req->status === 'approved' ? 'badge-success' : ($req->status === 'rejected' ? 'badge-danger' : 'badge-warning') }}" style="font-size:10.5px" title="Pengajuan: {{ $req->nama_sparepart }}">
                                            <i class="fa-solid fa-paper-plane"></i>
                                            {{ \Illuminate\Support\Str::limit($req->nama_sparepart, 15) }} ({{ $req->qty }})
                                        </span>
                                    @endforeach
                                    @if($item->sparepartRequests->count() > 2)
                                        <span class="badge badge-info" style="font-size:10.5px">+{{ $item->sparepartRequests->count() - 2 }} req</span>
                                    @endif
                                </div>
                            @endif
                        </td>

                        {{-- Status --}}
                        <td>
                            <span class="badge {{ $statusMeta['class'] }}">
                                <i class="fa-solid {{ $statusMeta['icon'] }}"></i>
                                {{ $statusMeta['label'] }}
                            </span>
                        </td>

                        {{-- Aksi (Monitoring, Edit Catatan/Permintaan, & Ajukan ke Pengadaan) --}}
                        <td>
                            <div class="d-flex gap-6 align-center" style="justify-content:center">
                                <a href="{{ route('maintenances.show', $item->id) }}"
                                   class="btn btn-secondary btn-sm btn-icon"
                                   title="Lihat Detail &amp; Edit Permintaan Suku Cadang">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                                <button type="button"
                                        class="btn btn-warning btn-sm btn-icon"
                                        title="Edit Catatan Perbaikan"
                                        data-id="{{ $item->id }}"
                                        data-armada="{{ ($item->vehicle->plat_nomor ?? '-') . ' (' . ($item->vehicle->merk ?? '-') . ')' }}"
                                        data-status="{{ $item->status_maintenance }}"
                                        data-kategori="{{ $item->kategori_kendala ?? '' }}"
                                        data-driver="{{ $item->nama_driver ?? '' }}"
                                        data-downtime="{{ $item->estimasi_downtime ?? '' }}"
                                        data-deskripsi="{{ $item->deskripsi_masalah ?? '' }}"
                                        data-solusi="{{ $item->solusi ?? '' }}"
                                        data-catatan="{{ $item->catatan ?? '' }}"
                                        onclick="openEditMaintenanceModal(this)">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                <button type="button"
                                        class="btn btn-primary btn-sm btn-icon"
                                        title="Ajukan Suku Cadang ke Pengadaan"
                                        onclick="openProcurementModal({{ $item->id }})">
                                    <i class="fa-solid fa-paper-plane"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8">
                            <div class="empty-state">
                                <div class="empty-icon"><i class="fa-solid fa-screwdriver-wrench"></i></div>
                                <div class="empty-title">Belum Ada Catatan Perbaikan</div>
                                <div class="empty-text">Mekanik atau Admin dapat mencatat perbaikan unit melalui tombol "Catat Perbaikan".</div>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($maintenances->hasPages())
    <div class="card-footer">
        {{ $maintenances->links('pagination::bootstrap-4') }}
    </div>
    @endif
</div>

@endsection

{{-- ===================== MODALS ===================== --}}
@section('modals')

{{-- Modal Edit Catatan Perbaikan dari Mekanik --}}
<div class="modal-overlay" id="modalEditMaintenance">
    <div class="modal-box" style="max-width:600px">
        <div class="modal-header">
            <div class="modal-title">
                <i class="fa-solid fa-pen-to-square" style="color:var(--warning);margin-right:8px"></i>
                Edit Catatan Perbaikan &mdash; <span id="em_armada_title"></span>
            </div>
            <button class="modal-close" type="button" onclick="closeModal('modalEditMaintenance')">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form id="formEditMaintenance" method="POST" action="">
            @csrf
            @method('PUT')
            <div class="modal-body" style="max-height:72vh;overflow-y:auto;">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="em_status">Status Perbaikan <span class="required">*</span></label>
                        <select id="em_status" name="status_maintenance" class="form-control" required>
                            <option value="pending">Pending</option>
                            <option value="in_progress">Sedang Dikerjakan</option>
                            <option value="completed">Selesai</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="em_kategori">Kategori Kendala</label>
                        <input type="text" id="em_kategori" name="kategori_kendala" class="form-control" placeholder="cth. Mesin, Rem, Kelistrikan">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="em_driver">Driver Pelapor</label>
                        <input type="text" id="em_driver" name="nama_driver" class="form-control">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="em_downtime">Estimasi Downtime</label>
                        <input type="text" id="em_downtime" name="estimasi_downtime" class="form-control" placeholder="cth. 2 hari / 4 jam">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="em_deskripsi">Deskripsi Masalah / Kerusakan</label>
                    <textarea id="em_deskripsi" name="deskripsi_masalah" rows="3" class="form-control"></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label" for="em_solusi">Tindakan / Solusi Mekanik</label>
                    <textarea id="em_solusi" name="solusi" rows="2" class="form-control"></textarea>
                </div>

                <div class="form-group" style="margin-bottom:0">
                    <label class="form-label" for="em_catatan">Catatan Tambahan Admin / Mekanik</label>
                    <textarea id="em_catatan" name="catatan" rows="2" class="form-control"></textarea>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalEditMaintenance')">Batal</button>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Ajukan Permintaan Suku Cadang ke Pengadaan --}}
<div class="modal-overlay" id="modalAjukanPengadaan">
    <div class="modal-box" style="max-width:580px">
        <div class="modal-header">
            <div class="modal-title">
                <i class="fa-solid fa-paper-plane" style="color:var(--primary);margin-right:8px"></i>
                Ajukan Pembelian Suku Cadang ke Pengadaan
            </div>
            <button class="modal-close" type="button" onclick="closeModal('modalAjukanPengadaan')">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form method="POST" action="{{ route('sparepart-requests.store') }}">
            @csrf
            <div class="modal-body" style="max-height:72vh;overflow-y:auto;">
                <div class="form-group">
                    <label class="form-label" for="ap_maintenance_id">Terkait Catatan Perbaikan Armada (Opsional)</label>
                    <select id="ap_maintenance_id" name="maintenance_id" class="form-control">
                        <option value="">-- Pengajuan Umum / Stok Gudang --</option>
                        @foreach($maintenances as $mOpt)
                            <option value="{{ $mOpt->id }}">
                                #{{ $mOpt->id }} &bull; {{ $mOpt->vehicle->plat_nomor ?? '-' }} ({{ $mOpt->vehicle->merk ?? '-' }}) &mdash; {{ \Illuminate\Support\Str::limit($mOpt->deskripsi_masalah, 40) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="ap_sparepart_id">Pilih dari Katalog Sparepart</label>
                    <select id="ap_sparepart_id" name="sparepart_id" class="form-control" onchange="syncCustomSparepartName()">
                        <option value="">-- Ketik Nama Sparepart Baru di Bawah --</option>
                        @foreach($spareparts as $sp)
                            <option value="{{ $sp->id }}" data-name="{{ $sp->nama_sparepart }}">
                                {{ $sp->nama_sparepart }} (Stok: {{ $sp->stok }} {{ $sp->satuan }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="ap_nama_sparepart">Nama Sparepart <span class="required">*</span></label>
                        <input type="text" id="ap_nama_sparepart" name="nama_sparepart" class="form-control" placeholder="cth. Kampas Rem Depan" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="ap_qty">Jumlah (Qty) <span class="required">*</span></label>
                        <input type="number" id="ap_qty" name="qty" min="1" value="1" class="form-control" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="ap_priority">Prioritas</label>
                        <select id="ap_priority" name="priority_level" class="form-control">
                            <option value="Normal" selected>Normal</option>
                            <option value="Rendah">Rendah</option>
                            <option value="Tinggi">Tinggi</option>
                            <option value="Urgent">Urgent</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="ap_delivery_date">Target Estimasi Tiba</label>
                        <input type="date" id="ap_delivery_date" name="expected_delivery_date" class="form-control">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="ap_vendor_name">Rekomendasi Vendor (Opsional)</label>
                    <select id="ap_vendor_name" name="vendor_name" class="form-control">
                        <option value="">-- Biarkan Pengadaan Menentukan --</option>
                        @foreach($vendors as $v)
                            <option value="{{ $v->nama_vendor }}">{{ $v->nama_vendor }}{{ $v->keahlian ? ' (' . $v->keahlian . ')' : '' }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group" style="margin-bottom:0">
                    <label class="form-label" for="ap_alasan">Alasan / Kebutuhan ke Pengadaan</label>
                    <textarea id="ap_alasan" name="alasan" rows="2" class="form-control" placeholder="Jelaskan urgensi atau detail spesifikasi suku cadang..."></textarea>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalAjukanPengadaan')">Batal</button>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-paper-plane"></i> Kirim ke Pengadaan
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

{{-- ===================== PAGE SCRIPTS ===================== --}}
@push('scripts')
<script>
(function () {
    'use strict';

    window.openEditMaintenanceModal = function (btn) {
        var id = btn.getAttribute('data-id');
        document.getElementById('formEditMaintenance').action = '/maintenances/' + id;
        document.getElementById('em_armada_title').textContent = btn.getAttribute('data-armada') || '';
        document.getElementById('em_status').value = btn.getAttribute('data-status') || 'pending';
        document.getElementById('em_kategori').value = btn.getAttribute('data-kategori') || '';
        document.getElementById('em_driver').value = btn.getAttribute('data-driver') || '';
        document.getElementById('em_downtime').value = btn.getAttribute('data-downtime') || '';
        document.getElementById('em_deskripsi').value = btn.getAttribute('data-deskripsi') || '';
        document.getElementById('em_solusi').value = btn.getAttribute('data-solusi') || '';
        document.getElementById('em_catatan').value = btn.getAttribute('data-catatan') || '';
        openModal('modalEditMaintenance');
    };

    window.openProcurementModal = function (maintenanceId) {
        var mSel = document.getElementById('ap_maintenance_id');
        mSel.value = maintenanceId ? String(maintenanceId) : '';
        document.getElementById('ap_sparepart_id').value = '';
        document.getElementById('ap_nama_sparepart').value = '';
        document.getElementById('ap_nama_sparepart').readOnly = false;
        document.getElementById('ap_qty').value = 1;
        document.getElementById('ap_priority').value = 'Normal';
        document.getElementById('ap_delivery_date').value = '';
        document.getElementById('ap_vendor_name').value = '';
        document.getElementById('ap_alasan').value = '';
        openModal('modalAjukanPengadaan');
    };

    window.syncCustomSparepartName = function () {
        var sel = document.getElementById('ap_sparepart_id');
        var input = document.getElementById('ap_nama_sparepart');
        var opt = sel.options[sel.selectedIndex];
        if (sel.value && opt && opt.getAttribute('data-name')) {
            input.value = opt.getAttribute('data-name');
            input.readOnly = true;
        } else {
            input.readOnly = false;
            if (!sel.value) input.value = '';
        }
    };

    window.applyMaintenanceFilter = function () {
        var query     = (document.getElementById('maintenanceSearch').value || '').toLowerCase().trim();
        var statusVal = (document.getElementById('maintenanceStatusFilter').value || '').toLowerCase();
        var btnPdf    = document.getElementById('btnExportPdfMaintenance');

        if (btnPdf) {
            var pdfUrl = new URL("{{ route('maintenances.export-pdf') }}", window.location.origin);
            if (statusVal) pdfUrl.searchParams.set('status', statusVal);
            if (query) pdfUrl.searchParams.set('q', query);
            btnPdf.href = pdfUrl.toString();
        }

        var rows      = document.querySelectorAll('#maintenanceTableBody tr[data-armada]');

        rows.forEach(function (row) {
            var haystack = (row.getAttribute('data-armada') || '') + ' ' + (row.getAttribute('data-mekanik') || '');
            var textMatch   = !query || haystack.includes(query);
            var statusMatch = !statusVal || row.getAttribute('data-status') === statusVal;

            row.style.display = (textMatch && statusMatch) ? '' : 'none';
        });
    };

})();
</script>
@endpush

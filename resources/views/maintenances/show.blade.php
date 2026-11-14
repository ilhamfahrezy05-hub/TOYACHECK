@extends('layouts.app')

@section('title', 'Detail Perbaikan — ' . ($maintenance->vehicle->kode_armada ?? 'Armada'))
@section('page-title', 'Detail Catatan Perbaikan')
@section('page-subtitle', 'Lembar kerja servis unit ' . ($maintenance->vehicle->kode_armada ?? 'armada') . ' (' . ($maintenance->vehicle->plat_nomor ?? '-') . ')')

{{-- ===================== PAGE ACTIONS ===================== --}}
@section('page-actions')
    <div class="d-flex gap-8 align-center">
        <a href="{{ route('maintenances.index') }}" class="btn btn-secondary">
            <i class="fa-solid fa-arrow-left"></i>
            Kembali
        </a>

        <button type="button" class="btn btn-warning" onclick="openModal('modalShowEditMaintenance')">
            <i class="fa-solid fa-pen-to-square"></i>
            Edit Catatan Perbaikan
        </button>

        <button type="button" class="btn btn-primary" onclick="openModal('modalShowAjukanPengadaan')">
            <i class="fa-solid fa-paper-plane"></i>
            Ajukan ke Pengadaan
        </button>

        <button type="button" class="btn btn-secondary" onclick="window.print()" title="Cetak Dokumen">
            <i class="fa-solid fa-print"></i>
            Cetak
        </button>
    </div>
@endsection

@push('styles')
<style>
    .mnt-detail-grid {
        display: grid;
        grid-template-columns: 360px 1fr;
        gap: 20px;
        align-items: start;
    }
    @media (max-width: 992px) {
        .mnt-detail-grid { grid-template-columns: 1fr; }
    }

    .mnt-meta-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        padding: 12px 0;
        border-bottom: 1px dashed var(--border);
        font-size: 13px;
    }
    .mnt-meta-row:last-child { border-bottom: 0; }
    .mnt-meta-key { color: var(--text-secondary); }
    .mnt-meta-val { font-weight: 600; color: var(--text-primary); text-align: right; }

    .mnt-card-section {
        background: #F8FAFC;
        border: 1px solid var(--border);
        border-radius: 10px;
        padding: 16px;
        margin-bottom: 16px;
    }
    .mnt-card-section:last-child { margin-bottom: 0; }
    .mnt-section-title {
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: var(--text-secondary);
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    /* Print styling */
    @media print {
        .sidebar, .topbar, .page-header-actions, .modal-overlay, #btn-add-vehicle, .sidebar-backdrop {
            display: none !important;
        }
        .main-wrapper { margin-left: 0 !important; width: 100% !important; }
        .page-content { padding: 0 !important; }
        .mnt-detail-grid { grid-template-columns: 1fr !important; }
        .card { box-shadow: none !important; border: 1px solid #ccc !important; }
    }
</style>
@endpush

{{-- ===================== CONTENT ===================== --}}
@section('content')

@php
    $statusMeta = match($maintenance->status_maintenance) {
        'pending'     => [
            'class'  => 'badge-warning',
            'bg'     => 'var(--warning-bg)',
            'color'  => 'var(--warning)',
            'icon'   => 'fa-clock',
            'label'  => 'Menunggu Perbaikan (Pending)',
            'desc'   => 'Unit telah didaftarkan antre servis dan menunggu pengerjaan oleh tim mekanik.'
        ],
        'in_progress' => [
            'class'  => 'badge-info',
            'bg'     => 'var(--info-bg)',
            'color'  => 'var(--info)',
            'icon'   => 'fa-screwdriver-wrench',
            'label'  => 'Sedang Dikerjakan (In Progress)',
            'desc'   => 'Mekanik sedang melakukan perbaikan, pembongkaran, atau penggantian suku cadang pada unit.'
        ],
        'completed'   => [
            'class'  => 'badge-success',
            'bg'     => 'var(--success-bg)',
            'color'  => 'var(--success)',
            'icon'   => 'fa-circle-check',
            'label'  => 'Perbaikan Selesai (Completed)',
            'desc'   => 'Seluruh perbaikan telah rampung. Status unit kendaraan telah disinkronkan kembali menjadi Siap Jalan.'
        ],
        default       => [
            'class'  => 'badge-neutral',
            'bg'     => '#F1F5F9',
            'color'  => 'var(--text-secondary)',
            'icon'   => 'fa-circle-question',
            'label'  => ucfirst($maintenance->status_maintenance),
            'desc'   => 'Status catatan perbaikan.'
        ],
    };

    // Hitung total estimasi biaya sparepart
    $totalBiaya = 0;
    foreach ($maintenance->details as $det) {
        $harga = $det->sparepart->harga ?? 0;
        $totalBiaya += ($harga * $det->qty);
    }
@endphp

{{-- ── BANNER STATUS ── --}}
<div style="
    display:flex;align-items:center;gap:16px;padding:16px 20px;border-radius:12px;margin-bottom:20px;
    background:{{ $statusMeta['bg'] }};
    color:{{ $statusMeta['color'] }}">
    <div style="font-size:30px">
        <i class="fa-solid {{ $statusMeta['icon'] }}"></i>
    </div>
    <div style="flex:1">
        <div style="font-size:16px;font-weight:700">
            {{ $statusMeta['label'] }}
        </div>
        <div style="font-size:13px;opacity:.9;margin-top:2px">
            {{ $statusMeta['desc'] }}
        </div>
    </div>
</div>

<div class="mnt-detail-grid">

    {{-- ── KOLOM KIRI: IDENTITAS PERBAIKAN & UNIT ── --}}
    <div class="card">
        <div class="card-header">
            <div>
                <div class="card-title">Informasi Servis</div>
                <div class="card-subtitle">Tiket #MNT-{{ str_pad($maintenance->id, 5, '0', STR_PAD_LEFT) }}</div>
            </div>
            <span class="badge {{ $statusMeta['class'] }}">
                <i class="fa-solid {{ $statusMeta['icon'] }}"></i>
                {{ ucfirst($maintenance->status_maintenance) }}
            </span>
        </div>

        <div style="padding:6px 20px 16px">

            <div class="mnt-meta-row">
                <span class="mnt-meta-key">Kode Armada</span>
                <span class="mnt-meta-val" style="font-family:monospace;font-size:14px">
                    {{ $maintenance->vehicle->kode_armada ?? '—' }}
                </span>
            </div>

            <div class="mnt-meta-row">
                <span class="mnt-meta-key">Plat Nomor</span>
                <span class="mnt-meta-val">
                    <span style="background:#0F1E36;color:#fff;padding:2px 8px;border-radius:4px;font-size:11px;font-weight:700;letter-spacing:.08em;font-family:monospace">
                        {{ strtoupper($maintenance->vehicle->plat_nomor ?? '-') }}
                    </span>
                </span>
            </div>

            <div class="mnt-meta-row">
                <span class="mnt-meta-key">Jenis / Merk Unit</span>
                <span class="mnt-meta-val">
                    {{ $maintenance->vehicle->merk ?? '—' }}
                    <div style="font-size:11px;color:var(--text-muted);font-weight:500">
                        {{ $maintenance->vehicle->jenis_kendaraan ?? '' }}
                    </div>
                </span>
            </div>

            <div class="mnt-meta-row">
                <span class="mnt-meta-key">Status Unit Saat Ini</span>
                <span class="mnt-meta-val">
                    @php
                        $vehStatus = $maintenance->vehicle->status ?? 'siap_jalan';
                        $vehMeta = match($vehStatus) {
                            'siap_jalan'  => ['class' => 'badge-success', 'icon' => 'fa-circle-check', 'label' => 'Siap Jalan'],
                            'maintenance' => ['class' => 'badge-info',    'icon' => 'fa-wrench',       'label' => 'Maintenance'],
                            'rusak'       => ['class' => 'badge-danger',  'icon' => 'fa-triangle-exclamation', 'label' => 'Rusak'],
                            default       => ['class' => 'badge-neutral', 'icon' => 'fa-circle-xmark', 'label' => ucfirst($vehStatus)],
                        };
                    @endphp
                    <span class="badge {{ $vehMeta['class'] }}">
                        <i class="fa-solid {{ $vehMeta['icon'] }}"></i>
                        {{ $vehMeta['label'] }}
                    </span>
                </span>
            </div>

            <div class="mnt-meta-row">
                <span class="mnt-meta-key">Tanggal Servis</span>
                <span class="mnt-meta-val">{{ $maintenance->tanggal_maintenance->translatedFormat('d F Y') }}</span>
            </div>

            <div class="mnt-meta-row">
                <span class="mnt-meta-key">Waktu Mulai</span>
                <span class="mnt-meta-val">{{ $maintenance->tanggal_maintenance->format('H:i') }} WIB</span>
            </div>

            <div class="mnt-meta-row">
                <span class="mnt-meta-key">Mekanik Pelaksana</span>
                <span class="mnt-meta-val">
                    <div class="d-flex align-center gap-8" style="justify-content:flex-end">
                        <div class="user-avatar" style="width:24px;height:24px;font-size:10px">
                            {{ strtoupper(substr($maintenance->mekanik->name ?? 'M', 0, 1)) }}
                        </div>
                        <span>{{ $maintenance->mekanik->name ?? '—' }}</span>
                    </div>
                </span>
            </div>

            @if($maintenance->checklist_id)
            <div class="mnt-meta-row">
                <span class="mnt-meta-key">Referensi Checklist</span>
                <span class="mnt-meta-val">
                    <a href="{{ route('checklists.show', $maintenance->checklist_id) }}"
                       class="btn btn-secondary btn-sm"
                       style="font-size:11px;padding:3px 8px"
                       target="_blank">
                        <i class="fa-solid fa-clipboard-check"></i>
                        Lihat Checklist #{{ str_pad($maintenance->checklist_id, 4, '0', STR_PAD_LEFT) }}
                    </a>
                </span>
            </div>
            @endif

            <div class="mnt-meta-row" style="background:var(--primary-subtle);margin:8px -20px -16px;padding:14px 20px;border-radius:0 0 12px 12px">
                <span class="mnt-meta-key" style="color:var(--primary);font-weight:600">Estimasi Biaya Sparepart</span>
                <span class="mnt-meta-val" style="color:var(--primary);font-size:15px;font-weight:700">
                    Rp {{ number_format($totalBiaya, 0, ',', '.') }}
                </span>
            </div>

        </div>
    </div>

    {{-- ── KOLOM KANAN: MASALAH, TINDAKAN & SUKU CADANG ── --}}
    <div class="d-flex" style="flex-direction:column;gap:20px">

        {{-- Card Masalah & Tindakan Perbaikan --}}
        <div class="card">
            <div class="card-header">
                <div>
                    <div class="card-title">Laporan Masalah & Tindakan Mekanik</div>
                    <div class="card-subtitle">Rincian analisa teknis dan solusi perbaikan</div>
                </div>
            </div>

            <div class="card-body">

                {{-- Deskripsi Masalah --}}
                <div class="mnt-card-section" style="border-left:4px solid var(--danger)">
                    <div class="mnt-section-title" style="color:var(--danger)">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                        Keluhan / Kerusakan Teridentifikasi
                    </div>
                    <div style="font-size:13px;line-height:1.6;color:var(--text-primary);white-space:pre-line">
                        {{ $maintenance->deskripsi_masalah }}
                    </div>
                </div>

                {{-- Solusi / Tindakan Perbaikan --}}
                <div class="mnt-card-section" style="border-left:4px solid var(--success)">
                    <div class="mnt-section-title" style="color:var(--success)">
                        <i class="fa-solid fa-wrench"></i>
                        Tindakan Perbaikan / Solusi
                    </div>
                    @if($maintenance->solusi)
                        <div style="font-size:13px;line-height:1.6;color:var(--text-primary);white-space:pre-line">
                            {{ $maintenance->solusi }}
                        </div>
                    @else
                        <div style="font-size:13px;color:var(--text-muted);font-style:italic">
                            Belum ada tindakan perbaikan yang dicatat oleh mekanik.
                        </div>
                    @endif
                </div>

                {{-- Catatan Tambahan --}}
                @if($maintenance->catatan)
                <div class="mnt-card-section" style="border-left:4px solid var(--primary)">
                    <div class="mnt-section-title" style="color:var(--primary)">
                        <i class="fa-regular fa-note-sticky"></i>
                        Catatan Tambahan
                    </div>
                    <div style="font-size:13px;line-height:1.6;color:var(--text-primary);white-space:pre-line">
                        {{ $maintenance->catatan }}
                    </div>
                </div>
                @endif

            </div>
        </div>

        {{-- Card Suku Cadang Terpakai --}}
        <div class="card">
            <div class="card-header">
                <div>
                    <div class="card-title">Suku Cadang (Sparepart) Terpakai</div>
                    <div class="card-subtitle">{{ $maintenance->details->count() }} komponen diganti / dipasang</div>
                </div>
            </div>

            <div class="card-body-p0">
                <div class="table-wrap">
                    <table class="tc-table">
                        <thead>
                            <tr>
                                <th style="width:48px">#</th>
                                <th>Kode</th>
                                <th>Nama Sparepart</th>
                                <th>Kategori</th>
                                <th style="text-align:center">Qty</th>
                                <th style="text-align:right">Harga Satuan</th>
                                <th style="text-align:right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($maintenance->details as $index => $detail)
                            @php
                                $hargaSatuan = $detail->sparepart->harga ?? 0;
                                $subtotal    = $hargaSatuan * $detail->qty;
                            @endphp
                            <tr>
                                <td style="color:var(--text-muted);font-size:12px">{{ $index + 1 }}</td>
                                <td style="font-family:monospace;font-weight:600;font-size:12px;color:var(--text-secondary)">
                                    {{ $detail->sparepart->kode_sparepart ?? '—' }}
                                </td>
                                <td style="font-weight:600;color:var(--text-primary)">
                                    {{ $detail->nama_sparepart }}
                                </td>
                                <td>
                                    <span class="badge badge-neutral" style="font-size:11px">
                                        {{ ucfirst($detail->sparepart->kategori ?? 'Umum') }}
                                    </span>
                                </td>
                                <td style="text-align:center;font-weight:600">
                                    {{ $detail->qty }} {{ $detail->sparepart->satuan ?? 'pcs' }}
                                </td>
                                <td style="text-align:right;font-size:13px">
                                    Rp {{ number_format($hargaSatuan, 0, ',', '.') }}
                                </td>
                                <td style="text-align:right;font-weight:600;font-size:13px;color:var(--primary)">
                                    Rp {{ number_format($subtotal, 0, ',', '.') }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7">
                                    <div class="empty-state" style="padding:24px">
                                        <div class="empty-icon" style="font-size:24px"><i class="fa-solid fa-boxes-stacked"></i></div>
                                        <div class="empty-title" style="font-size:14px">Tidak Ada Penggantian Sparepart</div>
                                        <div class="empty-text">Perbaikan ini hanya berupa penyesuaian/jasa servis tanpa pemakaian suku cadang bengkel.</div>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                        @if($maintenance->details->isNotEmpty())
                        <tfoot>
                            <tr style="background:#F8FAFC;font-weight:700">
                                <td colspan="6" style="text-align:right;padding:12px 16px;font-size:13px">
                                    Total Biaya Sparepart:
                                </td>
                                <td style="text-align:right;padding:12px 16px;font-size:14px;color:var(--primary)">
                                    Rp {{ number_format($totalBiaya, 0, ',', '.') }}
                                </td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        {{-- CARD 4: PERMINTAAN SUKU CADANG & PENGAJUAN KE PENGADAAN --}}
        <div class="card">
            <div class="card-header" style="justify-content:space-between;display:flex;align-items:center;gap:12px;">
                <div class="card-title">
                    <i class="fa-solid fa-paper-plane" style="color:var(--primary);margin-right:8px"></i>
                    Permintaan Suku Cadang &amp; Pengajuan ke Pengadaan ({{ $maintenance->sparepartRequests->count() }})
                </div>
                <button type="button" class="btn btn-primary btn-sm" onclick="openModal('modalShowAjukanPengadaan')">
                    <i class="fa-solid fa-plus"></i> Ajukan ke Pengadaan
                </button>
            </div>
            <div class="card-body" style="padding:0">
                <div class="table-wrap">
                    <table class="tc-table">
                        <thead>
                            <tr>
                                <th>Suku Cadang</th>
                                <th style="text-align:center">Qty</th>
                                <th>Prioritas &amp; Vendor</th>
                                <th>Pengaju</th>
                                <th>Status</th>
                                <th style="width:90px;text-align:center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($maintenance->sparepartRequests as $req)
                            @php
                                $reqBadge = match($req->status) {
                                    'approved' => ['badge-success', 'Disetujui'],
                                    'rejected' => ['badge-danger',  'Ditolak'],
                                    default    => ['badge-warning', 'Pending'],
                                };
                            @endphp
                            <tr>
                                <td>
                                    <div style="font-weight:600;color:var(--text-primary)">{{ $req->nama_sparepart }}</div>
                                    @if($req->alasan)
                                        <div style="font-size:11.5px;color:var(--text-muted);margin-top:2px">{{ \Illuminate\Support\Str::limit($req->alasan, 70) }}</div>
                                    @endif
                                </td>
                                <td style="text-align:center;font-weight:700">{{ $req->qty }}</td>
                                <td style="font-size:12px">
                                    <span class="badge badge-info">{{ $req->priority_level ?? 'Normal' }}</span>
                                    @if($req->vendor_name)
                                        <div style="margin-top:3px;color:var(--text-secondary)">Vendor: {{ $req->vendor_name }}</div>
                                    @endif
                                    @if($req->expected_delivery_date)
                                        <div style="font-size:11px;color:var(--text-muted)">Target: {{ $req->expected_delivery_date->format('d/m/Y') }}</div>
                                    @endif
                                </td>
                                <td style="font-size:12.5px">
                                    {{ $req->requester->name ?? '-' }}
                                    <div style="font-size:11px;color:var(--text-muted)">{{ $req->created_at?->format('d/m/Y H:i') }}</div>
                                </td>
                                <td>
                                    <span class="badge {{ $reqBadge[0] }}">{{ $reqBadge[1] }}</span>
                                </td>
                                <td style="text-align:center">
                                    <button type="button"
                                            class="btn btn-warning btn-sm btn-icon"
                                            title="Edit Permintaan Suku Cadang"
                                            data-id="{{ $req->id }}"
                                            data-sparepart-id="{{ $req->sparepart_id ?? '' }}"
                                            data-nama="{{ $req->nama_sparepart }}"
                                            data-qty="{{ $req->qty }}"
                                            data-priority="{{ $req->priority_level ?? 'Normal' }}"
                                            data-delivery="{{ $req->expected_delivery_date?->format('Y-m-d') ?? '' }}"
                                            data-vendor="{{ $req->vendor_name ?? '' }}"
                                            data-alasan="{{ $req->alasan ?? '' }}"
                                            data-catatan-admin="{{ $req->catatan_admin ?? '' }}"
                                            onclick="openShowEditRequestModal(this)">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6">
                                    <div class="empty-state" style="padding:20px">
                                        <div class="empty-title" style="font-size:13.5px">Belum Ada Pengajuan Suku Cadang</div>
                                        <div class="empty-text">Klik tombol &ldquo;Ajukan ke Pengadaan&rdquo; jika perbaikan ini membutuhkan pembelian suku cadang.</div>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

</div>

@endsection

@section('modals')

{{-- Modal Edit Catatan Perbaikan --}}
<div class="modal-overlay" id="modalShowEditMaintenance">
    <div class="modal-box" style="max-width:600px">
        <div class="modal-header">
            <div class="modal-title">
                <i class="fa-solid fa-pen-to-square" style="color:var(--warning);margin-right:8px"></i>
                Edit Catatan Perbaikan
            </div>
            <button class="modal-close" type="button" onclick="closeModal('modalShowEditMaintenance')">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form method="POST" action="{{ route('maintenances.update', $maintenance->id) }}">
            @csrf
            @method('PUT')
            <div class="modal-body" style="max-height:72vh;overflow-y:auto;">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="sem_status">Status Perbaikan <span class="required">*</span></label>
                        <select id="sem_status" name="status_maintenance" class="form-control" required>
                            <option value="pending" {{ $maintenance->status_maintenance === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="in_progress" {{ $maintenance->status_maintenance === 'in_progress' ? 'selected' : '' }}>Sedang Dikerjakan</option>
                            <option value="completed" {{ $maintenance->status_maintenance === 'completed' ? 'selected' : '' }}>Selesai</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="sem_kategori">Kategori Kendala</label>
                        <input type="text" id="sem_kategori" name="kategori_kendala" value="{{ $maintenance->kategori_kendala }}" class="form-control">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="sem_driver">Driver Pelapor</label>
                        <input type="text" id="sem_driver" name="nama_driver" value="{{ $maintenance->nama_driver }}" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="sem_downtime">Estimasi Downtime</label>
                        <input type="text" id="sem_downtime" name="estimasi_downtime" value="{{ $maintenance->estimasi_downtime }}" class="form-control">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="sem_deskripsi">Deskripsi Masalah / Kerusakan</label>
                    <textarea id="sem_deskripsi" name="deskripsi_masalah" rows="3" class="form-control">{{ $maintenance->deskripsi_masalah }}</textarea>
                </div>

                <div class="form-group">
                    <label class="form-label" for="sem_solusi">Tindakan / Solusi Mekanik</label>
                    <textarea id="sem_solusi" name="solusi" rows="2" class="form-control">{{ $maintenance->solusi }}</textarea>
                </div>

                <div class="form-group" style="margin-bottom:0">
                    <label class="form-label" for="sem_catatan">Catatan Tambahan</label>
                    <textarea id="sem_catatan" name="catatan" rows="2" class="form-control">{{ $maintenance->catatan }}</textarea>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalShowEditMaintenance')">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Ajukan ke Pengadaan --}}
<div class="modal-overlay" id="modalShowAjukanPengadaan">
    <div class="modal-box" style="max-width:560px">
        <div class="modal-header">
            <div class="modal-title">
                <i class="fa-solid fa-paper-plane" style="color:var(--primary);margin-right:8px"></i>
                Ajukan Suku Cadang ke Pengadaan
            </div>
            <button class="modal-close" type="button" onclick="closeModal('modalShowAjukanPengadaan')">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form method="POST" action="{{ route('sparepart-requests.store') }}">
            @csrf
            <input type="hidden" name="maintenance_id" value="{{ $maintenance->id }}">
            <div class="modal-body" style="max-height:72vh;overflow-y:auto;">
                <div class="form-group">
                    <label class="form-label" for="sap_sparepart_id">Pilih dari Katalog Sparepart</label>
                    <select id="sap_sparepart_id" name="sparepart_id" class="form-control" onchange="syncShowSparepartName('sap_')">
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
                        <label class="form-label" for="sap_nama_sparepart">Nama Sparepart <span class="required">*</span></label>
                        <input type="text" id="sap_nama_sparepart" name="nama_sparepart" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="sap_qty">Qty <span class="required">*</span></label>
                        <input type="number" id="sap_qty" name="qty" min="1" value="1" class="form-control" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="sap_priority">Prioritas</label>
                        <select id="sap_priority" name="priority_level" class="form-control">
                            <option value="Normal" selected>Normal</option>
                            <option value="Rendah">Rendah</option>
                            <option value="Tinggi">Tinggi</option>
                            <option value="Urgent">Urgent</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="sap_delivery">Target Estimasi Tiba</label>
                        <input type="date" id="sap_delivery" name="expected_delivery_date" class="form-control">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="sap_vendor">Rekomendasi Vendor</label>
                    <select id="sap_vendor" name="vendor_name" class="form-control">
                        <option value="">-- Biarkan Pengadaan Menentukan --</option>
                        @foreach($vendors as $v)
                            <option value="{{ $v->nama_vendor }}">{{ $v->nama_vendor }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group" style="margin-bottom:0">
                    <label class="form-label" for="sap_alasan">Alasan / Keterangan</label>
                    <textarea id="sap_alasan" name="alasan" rows="2" class="form-control"></textarea>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalShowAjukanPengadaan')">Batal</button>
                <button type="submit" class="btn btn-primary">Kirim ke Pengadaan</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Edit Permintaan Suku Cadang (Mekanik / Admin) --}}
<div class="modal-overlay" id="modalShowEditRequest">
    <div class="modal-box" style="max-width:560px">
        <div class="modal-header">
            <div class="modal-title">
                <i class="fa-solid fa-pen-to-square" style="color:var(--warning);margin-right:8px"></i>
                Edit Permintaan Suku Cadang
            </div>
            <button class="modal-close" type="button" onclick="closeModal('modalShowEditRequest')">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form id="formShowEditRequest" method="POST" action="">
            @csrf
            @method('PUT')
            <input type="hidden" name="maintenance_id" value="{{ $maintenance->id }}">
            <div class="modal-body" style="max-height:72vh;overflow-y:auto;">
                <div class="form-group">
                    <label class="form-label" for="ser_sparepart_id">Pilih dari Katalog Sparepart</label>
                    <select id="ser_sparepart_id" name="sparepart_id" class="form-control" onchange="syncShowSparepartName('ser_')">
                        <option value="">-- Nama Manual / Non-Katalog --</option>
                        @foreach($spareparts as $sp)
                            <option value="{{ $sp->id }}" data-name="{{ $sp->nama_sparepart }}">
                                {{ $sp->nama_sparepart }} (Stok: {{ $sp->stok }} {{ $sp->satuan }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="ser_nama_sparepart">Nama Sparepart <span class="required">*</span></label>
                        <input type="text" id="ser_nama_sparepart" name="nama_sparepart" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="ser_qty">Qty <span class="required">*</span></label>
                        <input type="number" id="ser_qty" name="qty" min="1" class="form-control" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="ser_priority">Prioritas</label>
                        <select id="ser_priority" name="priority_level" class="form-control">
                            <option value="Rendah">Rendah</option>
                            <option value="Normal">Normal</option>
                            <option value="Tinggi">Tinggi</option>
                            <option value="Urgent">Urgent</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="ser_delivery">Target Estimasi Tiba</label>
                        <input type="date" id="ser_delivery" name="expected_delivery_date" class="form-control">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="ser_vendor">Vendor</label>
                    <select id="ser_vendor" name="vendor_name" class="form-control">
                        <option value="">-- Belum Ditentukan --</option>
                        @foreach($vendors as $v)
                            <option value="{{ $v->nama_vendor }}">{{ $v->nama_vendor }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="ser_alasan">Alasan / Keterangan</label>
                    <textarea id="ser_alasan" name="alasan" rows="2" class="form-control"></textarea>
                </div>

                <div class="form-group" style="margin-bottom:0">
                    <label class="form-label" for="ser_catatan_admin">Catatan Admin</label>
                    <textarea id="ser_catatan_admin" name="catatan_admin" rows="2" class="form-control"></textarea>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalShowEditRequest')">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
(function () {
    'use strict';

    window.syncShowSparepartName = function (prefix) {
        var sel = document.getElementById(prefix + 'sparepart_id');
        var input = document.getElementById(prefix + 'nama_sparepart');
        var opt = sel.options[sel.selectedIndex];
        if (sel.value && opt && opt.getAttribute('data-name')) {
            input.value = opt.getAttribute('data-name');
            input.readOnly = true;
        } else {
            input.readOnly = false;
        }
    };

    window.openShowEditRequestModal = function (btn) {
        var id = btn.getAttribute('data-id');
        document.getElementById('formShowEditRequest').action = '/sparepart-requests/' + id;
        document.getElementById('ser_sparepart_id').value = btn.getAttribute('data-sparepart-id') || '';
        document.getElementById('ser_nama_sparepart').value = btn.getAttribute('data-nama') || '';
        document.getElementById('ser_qty').value = btn.getAttribute('data-qty') || 1;
        document.getElementById('ser_priority').value = btn.getAttribute('data-priority') || 'Normal';
        document.getElementById('ser_delivery').value = btn.getAttribute('data-delivery') || '';
        document.getElementById('ser_vendor').value = btn.getAttribute('data-vendor') || '';
        document.getElementById('ser_alasan').value = btn.getAttribute('data-alasan') || '';
        document.getElementById('ser_catatan_admin').value = btn.getAttribute('data-catatan-admin') || '';
        syncShowSparepartName('ser_');
        openModal('modalShowEditRequest');
    };
})();
</script>
@endpush

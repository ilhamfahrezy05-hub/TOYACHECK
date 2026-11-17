@extends('layouts.app')

@section('title', 'Lokasi Vendor')
@section('page-title', 'Lokasi Vendor')
@section('page-subtitle', 'Manage supplier locations and part specialization')

@section('page-actions')
    <form method="GET" action="{{ route('pengadaan.vendors') }}" class="d-flex align-center gap-12">
        <div style="position:relative">
            <input type="text"
                   name="q"
                   value="{{ $search }}"
                   class="form-control"
                   style="width:240px;height:36px;padding-right:32px;border:1.5px solid #334155;border-radius:4px;font-size:13px"
                   placeholder="Search">
            <button type="submit" style="position:absolute;right:8px;top:50%;transform:translateY(-50%);background:none;border:none;color:#334155;cursor:pointer">
                <i class="fa-solid fa-magnifying-glass"></i>
            </button>
        </div>

        <a href="{{ route('pengadaan.vendors.create') }}"
           class="btn btn-sm"
           style="background:#FFFFFF;border:1.5px solid #334155;color:#0F172A;font-weight:700;height:36px;padding:0 14px;border-radius:4px">
            + Tambah lokasi
        </a>
    </form>
@endsection

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    .vn-kpi-wrap {
        display: flex;
        justify-content: center;
        gap: 28px;
        margin-bottom: 28px;
        flex-wrap: wrap;
    }
    .vn-kpi-card {
        width: 260px;
        border-radius: 4px;
        padding: 20px 24px;
        text-align: center;
        border: 1px solid rgba(15,23,42,0.15);
        box-shadow: 0 2px 6px rgba(15,23,42,0.05);
    }
    .vn-kpi-purple {
        background: #B975F9;
        color: #0F172A;
    }
    .vn-kpi-red {
        background: #FA4B4B;
        color: #0F172A;
    }
    .vn-split-grid {
        display: grid;
        grid-template-columns: 1.45fr 1fr;
        gap: 22px;
        align-items: stretch;
    }
    @media (max-width: 1024px) {
        .vn-split-grid { grid-template-columns: 1fr; }
    }
    .vn-row {
        cursor: pointer;
        transition: background .15s;
    }
    .vn-row:hover {
        background: #F8FAFC;
    }
</style>
@endpush

@section('content')

{{-- 1. DUA KARTU KPI BERWARNA DI TENGAH (FIGMA 918:36897) --}}
<div class="vn-kpi-wrap">
    <div class="vn-kpi-card vn-kpi-purple">
        <div style="font-size:12px;font-weight:700;margin-bottom:10px;display:flex;align-items:center;justify-content:center;gap:6px">
            <span>Total Vendors</span>
            <i class="fa-solid fa-users-Rectangle" style="font-size:13px"></i>
        </div>
        <div style="font-size:28px;font-weight:900;line-height:1.1">
            {{ $totalVendors }}
        </div>
    </div>

    <div class="vn-kpi-card vn-kpi-red">
        <div style="font-size:11.5px;font-weight:700;margin-bottom:12px">
            Jenis Kriteria Vendor
        </div>
        <div style="font-size:18px;font-weight:900;line-height:1.2">
            {{ $totalKriteria }} Kriteria
        </div>
    </div>
</div>

{{-- 2. SPLIT 2 KOLOM: TABEL LIST VENDOR & MAP LOGISTIK --}}
<div class="vn-split-grid">

    {{-- Kiri: List Vendor --}}
    <div class="card" style="display:flex;flex-direction:column">
        <div style="padding:18px 22px;border-bottom:1px solid #E2E8F0;display:flex;justify-content:space-between;align-items:center">
            <div style="font-size:14.5px;font-weight:800;color:#0F172A">List vendor</div>
            <span style="font-size:12px;color:#64748B">{{ $vendors->count() }} vendor terdaftar</span>
        </div>

        <div class="table-wrap" style="max-height:480px;overflow-y:auto">
            <table class="tc-table">
                <thead>
                    <tr>
                        <th>VENDOR NAME</th>
                        <th>CATEGORY</th>
                        <th>LOCATION</th>
                        <th>EMAIL</th>
                        <th style="width:76px;text-align:center">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($vendors as $v)
                        @php
                            $vndCode = 'VND-' . str_pad($v->id + 1000, 4, '0', STR_PAD_LEFT);
                            $lat = $v->latitude ?: '-6.7320';
                            $lng = $v->longitude ?: '108.5523';
                        @endphp
                        <tr class="vn-row" onclick="focusVendorOnMap({{ (float) $lat }}, {{ (float) $lng }}, {{ \Illuminate\Support\Js::from($v->nama_vendor) }})">
                            <td>
                                <div style="font-weight:800;color:#0F172A;font-size:13px">{{ $v->nama_vendor }}</div>
                                <div style="font-size:10.5px;color:#94A3B8;font-family:monospace">ID: {{ $vndCode }}</div>
                            </td>
                            <td style="font-size:12.5px;color:#334155;font-weight:600">
                                {{ $v->keahlian ?: 'General Parts' }}
                            </td>
                            <td style="font-size:12px;color:#475569;max-width:190px">
                                {{ $v->alamat ?: '—' }}
                                @if($v->kota)
                                    <div style="font-size:10.5px;color:#94A3B8">{{ $v->kota }}{{ $v->provinsi ? ', ' . $v->provinsi : '' }}</div>
                                @endif
                            </td>
                            <td style="font-size:12px;color:#475569">
                                {{ $v->email ?: '—' }}
                            </td>
                            <td onclick="event.stopPropagation()" style="text-align:center">
                                <div class="d-flex align-center gap-8" style="justify-content:center">
                                    <button type="button" class="btn btn-secondary btn-sm btn-icon" title="Edit Vendor"
                                            onclick='openEditVendorModal(@json($v))'>
                                        <i class="fa-solid fa-pen" style="font-size:11px"></i>
                                    </button>
                                    <form method="POST" action="{{ route('pengadaan.vendors.destroy', $v->id) }}"
                                          onsubmit="return confirm('Hapus vendor {{ addslashes($v->nama_vendor) }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm btn-icon" title="Hapus Vendor">
                                            <i class="fa-regular fa-trash-can" style="font-size:11px"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align:center;padding:32px;color:#64748B">
                                Tidak ada vendor yang cocok dengan pencarian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Kanan: Map Logistik --}}
    <div class="card" style="padding:18px;display:flex;flex-direction:column">
        <div style="font-size:14.5px;font-weight:800;color:#0F172A;margin-bottom:12px">
            Map logistik
        </div>
        <div id="logisticsMap" style="flex:1;min-height:420px;border-radius:8px;border:1px solid #E2E8F0;z-index:1"></div>
    </div>

</div>

@endsection

@section('modals')
{{-- Modal Edit Vendor --}}
<div class="modal-overlay" id="modalEditVendorPengadaan">
    <div class="modal-box" style="max-width:560px">
        <div class="modal-header">
            <div class="modal-title">Edit Lokasi &amp; Informasi Vendor</div>
            <button type="button" class="modal-close" onclick="closeModal('modalEditVendorPengadaan')">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <form method="POST" id="formEditVendorPengadaan" action="">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
                    <div class="form-group">
                        <label class="form-label">Nama Vendor <span class="required">*</span></label>
                        <input type="text" name="nama_vendor" id="ev_nama" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Spesialis / Kategori</label>
                        <input type="text" name="keahlian" id="ev_keahlian" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Nama Kontak</label>
                        <input type="text" name="kontak_person" id="ev_kontak" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Telepon</label>
                        <input type="text" name="telepon" id="ev_telepon" class="form-control">
                    </div>
                    <div class="form-group" style="grid-column:span 2">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" id="ev_email" class="form-control">
                    </div>
                    <div class="form-group" style="grid-column:span 2">
                        <label class="form-label">Alamat Lengkap <span class="required">*</span></label>
                        <input type="text" name="alamat" id="ev_alamat" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Kota</label>
                        <input type="text" name="kota" id="ev_kota" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Provinsi</label>
                        <input type="text" name="provinsi" id="ev_provinsi" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Latitude</label>
                        <input type="text" name="latitude" id="ev_lat" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Longitude</label>
                        <input type="text" name="longitude" id="ev_lng" class="form-control">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalEditVendorPengadaan')">Batal</button>
                <button type="submit" class="btn btn-primary" style="background:#0F172A">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    const vendorsData = @json($vendors);
    let mapInstance = null;

    document.addEventListener('DOMContentLoaded', function () {
        mapInstance = L.map('logisticsMap').setView([-6.7320, 108.5523], 11);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 18,
            attribution: '&copy; OpenStreetMap'
        }).addTo(mapInstance);

        vendorsData.forEach(function (v) {
            const lat = parseFloat(v.latitude) || -6.7320;
            const lng = parseFloat(v.longitude) || 108.5523;
            const marker = L.marker([lat, lng]).addTo(mapInstance);
            marker.bindPopup(`<strong>${v.nama_vendor}</strong><br><small>${v.keahlian || 'Supplier'}</small><br><small>${v.alamat || ''}</small>`);
        });
    });

    function focusVendorOnMap(lat, lng, title) {
        if (!mapInstance) return;
        mapInstance.setView([lat, lng], 14, { animate: true });
        L.popup()
            .setLatLng([lat, lng])
            .setContent(`<strong>${title}</strong>`)
            .openOn(mapInstance);
    }

    function openEditVendorModal(v) {
        document.getElementById('formEditVendorPengadaan').action = '/pengadaan/vendors/' + v.id;
        document.getElementById('ev_nama').value = v.nama_vendor || '';
        document.getElementById('ev_keahlian').value = v.keahlian || '';
        document.getElementById('ev_kontak').value = v.kontak_person || '';
        document.getElementById('ev_telepon').value = v.telepon || '';
        document.getElementById('ev_email').value = v.email || '';
        document.getElementById('ev_alamat').value = v.alamat || '';
        document.getElementById('ev_kota').value = v.kota || '';
        document.getElementById('ev_provinsi').value = v.provinsi || '';
        document.getElementById('ev_lat').value = v.latitude || '';
        document.getElementById('ev_lng').value = v.longitude || '';
        openModal('modalEditVendorPengadaan');
    }
</script>
@endpush

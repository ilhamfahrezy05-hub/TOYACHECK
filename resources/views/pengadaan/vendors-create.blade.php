@extends('layouts.app')

@section('title', 'Pendaftaran Vendor')
@section('page-title', 'Pendaftaran Vendor')
@section('page-subtitle', 'Manage supplier locations and part specialization')

@section('page-actions')
    <a href="{{ route('pengadaan.vendors') }}" class="btn btn-secondary btn-sm">
        <i class="fa-solid fa-arrow-left"></i> Kembali ke Lokasi Vendor
    </a>
@endsection

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    .vc-grid {
        display: grid;
        grid-template-columns: 1.35fr 1fr;
        gap: 24px;
        align-items: start;
    }
    @media (max-width: 1024px) {
        .vc-grid { grid-template-columns: 1fr; }
    }
    .vc-card {
        background: #FFFFFF;
        border: 1px solid #E2E8F0;
        border-radius: 10px;
        padding: 24px;
        box-shadow: 0 1px 4px rgba(15,23,42,0.03);
    }
    .vc-label {
        display: block;
        font-size: 10.5px;
        font-weight: 800;
        color: #475569;
        letter-spacing: .05em;
        text-transform: uppercase;
        margin-bottom: 6px;
    }
</style>
@endpush

@section('content')

<form method="POST" action="{{ route('pengadaan.vendors.store') }}" id="formCreateVendor">
    @csrf

    <div class="vc-grid">

        {{-- KOLOM KIRI: LOCATION DETAILS + INTERACTIVE MAP + LAT/LNG (FIGMA 1265:5181 & 1270:5412) --}}
        <div class="vc-card">
            <div style="display:flex;align-items:center;gap:8px;font-size:14px;font-weight:800;color:#0F172A;margin-bottom:18px">
                <i class="fa-solid fa-location-dot" style="color:#0F172A"></i>
                <span>Location Details</span>
            </div>

            <div class="form-group">
                <label class="vc-label" for="alamat">FULL ADDRESS <span class="required">*</span></label>
                <input type="text"
                       id="alamat"
                       name="alamat"
                       value="{{ old('alamat') }}"
                       class="form-control"
                       placeholder="Street name, building number..."
                       required>
                @error('alamat')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:18px">
                <div>
                    <label class="vc-label" for="kota">KOTA</label>
                    <input type="text"
                           id="kota"
                           name="kota"
                           value="{{ old('kota', 'Cirebon') }}"
                           class="form-control"
                           placeholder="e.g. Cirebon / Jakarta">
                </div>
                <div>
                    <label class="vc-label" for="provinsi">PROVINSI</label>
                    <input type="text"
                           id="provinsi"
                           name="provinsi"
                           value="{{ old('provinsi', 'Jawa Barat') }}"
                           class="form-control"
                           placeholder="e.g. Jawa Barat">
                </div>
            </div>

            {{-- Interactive Map Pin Picker --}}
            <div style="margin-bottom:16px">
                <div id="pickerMap" style="height:260px;border-radius:8px;border:1px solid #CBD5E1;z-index:1"></div>
                <div style="font-size:11px;color:#64748B;margin-top:6px">
                    <i class="fa-solid fa-hand-pointer"></i> Klik pada peta untuk memindahkan pin lokasi vendor secara presisi.
                </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
                <div>
                    <label class="vc-label" for="latitude">LATITUDE</label>
                    <input type="text"
                           id="latitude"
                           name="latitude"
                           value="{{ old('latitude', '-6.7320') }}"
                           class="form-control"
                           placeholder="-6.2088">
                </div>
                <div>
                    <label class="vc-label" for="longitude">LONGITUDE</label>
                    <input type="text"
                           id="longitude"
                           name="longitude"
                           value="{{ old('longitude', '108.5523') }}"
                           class="form-control"
                           placeholder="106.8456">
                </div>
            </div>
        </div>

        {{-- KOLOM KANAN: INFORMASI GENERAL --}}
        <div class="vc-card">
            <div style="display:flex;align-items:center;gap:8px;font-size:14px;font-weight:800;color:#0F172A;margin-bottom:18px">
                <i class="fa-solid fa-circle-info" style="color:#0F172A"></i>
                <span>Informasi General</span>
            </div>

            <div class="form-group">
                <label class="vc-label" for="nama_vendor">NAMA VENDOR <span class="required">*</span></label>
                <input type="text"
                       id="nama_vendor"
                       name="nama_vendor"
                       value="{{ old('nama_vendor') }}"
                       class="form-control"
                       placeholder="Masukan nama vendor"
                       required>
                @error('nama_vendor')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label class="vc-label" for="keahlian">SPESIALIS</label>
                <select id="keahlian" name="keahlian" class="form-control">
                    <option value="">Pilih Spesialisasi Komponen</option>
                    @foreach($spesialisOptions as $opt)
                        <option value="{{ $opt }}" {{ old('keahlian') === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label class="vc-label" for="kontak_person">NAMA KONTAK</label>
                <input type="text"
                       id="kontak_person"
                       name="kontak_person"
                       value="{{ old('kontak_person') }}"
                       class="form-control"
                       placeholder="Nama lengkap perorangan">
            </div>

            <div class="form-group">
                <label class="vc-label" for="email">ALAMAT EMAIL</label>
                <input type="email"
                       id="email"
                       name="email"
                       value="{{ old('email') }}"
                       class="form-control"
                       placeholder="vendor@perusahaan.com">
            </div>

            <div class="form-group" style="margin-bottom:0">
                <label class="vc-label" for="telepon">NOMOR TELEPON</label>
                <input type="text"
                       id="telepon"
                       name="telepon"
                       value="{{ old('telepon') }}"
                       class="form-control"
                       placeholder="0812xxxxxxxx">
            </div>
        </div>

    </div>

    {{-- Bottom Action Bar (Figma Right-aligned) --}}
    <div style="display:flex;justify-content:flex-end;gap:12px;margin-top:24px">
        <a href="{{ route('pengadaan.vendors') }}"
           class="btn"
           style="background:#FFFFFF;border:1px solid #CBD5E1;color:#0F172A;font-weight:700;padding:10px 24px">
            Batal
        </a>
        <button type="submit"
                class="btn"
                style="background:#0F1E36;color:#FFFFFF;font-weight:700;padding:10px 24px">
            <i class="fa-regular fa-floppy-disk"></i> Simpan Lokasi
        </button>
    </div>

</form>

@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const latInput = document.getElementById('latitude');
        const lngInput = document.getElementById('longitude');

        const startLat = parseFloat(latInput.value) || -6.7320;
        const startLng = parseFloat(lngInput.value) || 108.5523;

        const map = L.map('pickerMap').setView([startLat, startLng], 12);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 18,
            attribution: '&copy; OpenStreetMap'
        }).addTo(map);

        let marker = L.marker([startLat, startLng], { draggable: true }).addTo(map);

        map.on('click', function (e) {
            marker.setLatLng(e.latlng);
            latInput.value = e.latlng.lat.toFixed(6);
            lngInput.value = e.latlng.lng.toFixed(6);
        });

        marker.on('dragend', function (e) {
            const pos = e.target.getLatLng();
            latInput.value = pos.lat.toFixed(6);
            lngInput.value = pos.lng.toFixed(6);
        });
    });
</script>
@endpush

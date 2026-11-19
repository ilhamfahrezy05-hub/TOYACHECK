@extends('layouts.app')

@section('title', 'Data Armada')
@section('page-title', 'Data Armada')
@section('page-subtitle', 'Kelola seluruh kendaraan armada distribusi')

{{-- ===================== PAGE ACTIONS ===================== --}}
@section('page-actions')
    <div class="d-flex gap-8 align-center">
        <button type="button" class="btn btn-secondary" onclick="openUniversalQr()" title="Tampilkan & Cetak QR Code Universal untuk akses aplikasi mobile TOYACHECK">
            <i class="fa-solid fa-qrcode"></i>
            QR Code Akses Mobile
        </button>
        <button class="btn btn-primary" onclick="openModal('modalCreateVehicle')">
            <i class="fa-solid fa-plus"></i>
            Tambah Armada
        </button>
    </div>
@endsection

{{-- ===================== CONTENT ===================== --}}
@section('content')

{{-- ── STAT CARDS ── --}}
<div class="stats-grid" style="grid-template-columns: repeat(5, 1fr)">

    <div class="stat-card">
        <div class="stat-icon blue">
            <i class="fa-solid fa-truck"></i>
        </div>
        <div class="stat-info">
            <div class="stat-label">Total Armada</div>
            <div class="stat-value">{{ $stats['total'] }}</div>
            <div class="stat-change">Kendaraan terdaftar</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon green">
            <i class="fa-solid fa-circle-check"></i>
        </div>
        <div class="stat-info">
            <div class="stat-label">Siap Jalan</div>
            <div class="stat-value">{{ $stats['siap_jalan'] }}</div>
            <div class="stat-change">Siap beroperasi</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon amber">
            <i class="fa-solid fa-wrench"></i>
        </div>
        <div class="stat-info">
            <div class="stat-label">Maintenance</div>
            <div class="stat-value">{{ $stats['maintenance'] }}</div>
            <div class="stat-change">Sedang diservis</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon red">
            <i class="fa-solid fa-triangle-exclamation"></i>
        </div>
        <div class="stat-info">
            <div class="stat-label">Rusak</div>
            <div class="stat-value">{{ $stats['rusak'] }}</div>
            <div class="stat-change">Perlu perbaikan</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon neutral" style="background:#F1F5F9;color:#64748B">
            <i class="fa-solid fa-ban"></i>
        </div>
        <div class="stat-info">
            <div class="stat-label">Nonaktif</div>
            <div class="stat-value">{{ $stats['nonaktif'] }}</div>
            <div class="stat-change">Tidak beroperasi</div>
        </div>
    </div>

</div>

{{-- ── TABLE CARD ── --}}
<div class="card">
    <div class="card-header">
        <div>
            <div class="card-title">Daftar Armada</div>
            <div class="card-subtitle">Total {{ $vehicles->total() }} kendaraan terdaftar</div>
        </div>

        {{-- Search --}}
        <div class="d-flex gap-8 align-center">
            <div style="position:relative">
                <i class="fa-solid fa-magnifying-glass"
                   style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:var(--text-muted);font-size:13px;pointer-events:none"></i>
                <input
                    type="text"
                    id="vehicleSearch"
                    placeholder="Cari plat nomor / merk / jenis..."
                    class="form-control"
                    style="padding-left:32px;width:240px"
                    oninput="filterVehicleTable(this.value)">
            </div>

            {{-- Filter Status --}}
            <select id="statusFilter" class="form-control" style="width:145px" onchange="filterByStatus(this.value)">
                <option value="">Semua Status</option>
                <option value="siap_jalan">Siap Jalan</option>
                <option value="maintenance">Maintenance</option>
                <option value="rusak">Rusak</option>
                <option value="nonaktif">Nonaktif</option>
            </select>
        </div>
    </div>

    <div class="card-body-p0">
        <div class="table-wrap">
            <table class="tc-table" id="vehicleTable">
                <thead>
                    <tr>
                        <th style="width:52px">#</th>
                        <th>Plat Nomor</th>
                        <th>Jenis &amp; Merk</th>
                        <th>Kepemilikan</th>
                        <th>Tahun</th>
                        <th>Ukuran / Dimensi Box</th>
                        <th>STNK &amp; KIR</th>
                        <th>Status</th>
                        <th>Ditambahkan</th>
                        <th style="width:110px;text-align:center">Aksi</th>
                    </tr>
                </thead>
                <tbody id="vehicleTableBody">
                    @forelse ($vehicles as $index => $vehicle)
                    <tr
                        data-plat="{{ strtolower($vehicle->plat_nomor) }}"
                        data-merk="{{ strtolower($vehicle->merk) }}"
                        data-jenis="{{ strtolower($vehicle->jenis_kendaraan) }}"
                        data-status="{{ $vehicle->status }}">

                        <td style="color:var(--text-muted);font-size:12px">
                            {{ $vehicles->firstItem() + $index }}
                        </td>

                        {{-- Plat Nomor --}}
                        <td>
                            <div class="d-flex align-center gap-8">
                                <div style="
                                    width:32px;height:32px;border-radius:8px;
                                    background:var(--primary-subtle);
                                    display:flex;align-items:center;justify-content:center;
                                    flex-shrink:0;font-size:13px;color:var(--primary)">
                                    <i class="fa-solid fa-truck-front"></i>
                                </div>
                                <span style="
                                    background:#0F1E36;color:#fff;
                                    padding:3px 10px;border-radius:4px;
                                    font-size:12px;font-weight:700;
                                    letter-spacing:0.08em;font-family:monospace">
                                    {{ strtoupper($vehicle->plat_nomor) }}
                                </span>
                            </div>
                        </td>

                        {{-- Jenis & Merk --}}
                        <td>
                            <div style="font-weight:600;color:var(--text-primary)">{{ $vehicle->jenis_kendaraan }}</div>
                            <div style="font-size:12px;color:var(--text-secondary)">{{ $vehicle->merk }}</div>
                        </td>

                        {{-- Kepemilikan --}}
                        <td>
                            @if(($vehicle->kepemilikan ?? 'internal') === 'eksternal')
                                <span class="badge badge-info">
                                    <i class="fa-solid fa-building-user"></i>
                                    Eksternal
                                </span>
                            @else
                                <span class="badge badge-primary" style="background:var(--primary-subtle);color:var(--primary)">
                                    <i class="fa-solid fa-building"></i>
                                    Internal
                                </span>
                            @endif
                        </td>

                        {{-- Tahun --}}
                        <td style="color:var(--text-secondary);font-weight:600">
                            {{ $vehicle->tahun ?? '—' }}
                        </td>

                        {{-- Dimensi Box / Ukuran Truck --}}
                        <td style="font-size:12.5px;color:var(--text-primary)">
                            @if($vehicle->dimensi_box)
                                <span style="background:#F1F5F9;border:1px solid var(--border);padding:3px 8px;border-radius:6px;font-weight:600;display:inline-block">
                                    <i class="fa-solid fa-ruler-combined" style="color:var(--primary);margin-right:4px;font-size:11px"></i>
                                    {{ $vehicle->dimensi_box }}
                                </span>
                            @else
                                <span style="color:var(--text-muted)">—</span>
                            @endif
                        </td>

                        {{-- Tahun STNK & KIR --}}
                        <td style="font-size:12px;line-height:1.5;white-space:nowrap">
                            <div>
                                <span style="color:var(--text-muted)">STNK:</span>
                                <strong style="color:var(--text-primary)">{{ $vehicle->tahun_stnk ?: '—' }}</strong>
                            </div>
                            <div>
                                <span style="color:var(--text-muted)">KIR:</span>
                                <strong style="color:var(--text-primary)">{{ $vehicle->tahun_kir ?: '—' }}</strong>
                            </div>
                        </td>

                        {{-- Status --}}
                        <td>
                            @php
                                $statusMap = [
                                    'siap_jalan'  => ['class' => 'badge-success', 'icon' => 'fa-circle-check',          'label' => 'Siap Jalan'],
                                    'maintenance' => ['class' => 'badge-warning', 'icon' => 'fa-wrench',                'label' => 'Maintenance'],
                                    'rusak'       => ['class' => 'badge-danger',  'icon' => 'fa-triangle-exclamation', 'label' => 'Rusak'],
                                    'nonaktif'    => ['class' => 'badge-neutral', 'icon' => 'fa-circle-xmark',          'label' => 'Nonaktif'],
                                ];
                                $s = $statusMap[$vehicle->status] ?? $statusMap['nonaktif'];
                            @endphp
                            <span class="badge {{ $s['class'] }}">
                                <i class="fa-solid {{ $s['icon'] }}"></i>
                                {{ $s['label'] }}
                            </span>
                        </td>

                        {{-- Tanggal --}}
                        <td style="color:var(--text-secondary);font-size:13px;white-space:nowrap">
                            <i class="fa-regular fa-calendar" style="margin-right:4px"></i>
                            {{ $vehicle->created_at->format('d M Y') }}
                        </td>

                        {{-- Aksi --}}
                        <td>
                            <div class="d-flex gap-8 align-center" style="justify-content:center">
                                <button
                                    type="button"
                                    class="btn btn-warning btn-sm btn-icon"
                                    title="Edit Armada"
                                    data-id="{{ $vehicle->id }}"
                                    data-plat="{{ $vehicle->plat_nomor }}"
                                    data-merk="{{ $vehicle->merk }}"
                                    data-jenis="{{ $vehicle->jenis_kendaraan }}"
                                    data-kepemilikan="{{ $vehicle->kepemilikan ?? 'internal' }}"
                                    data-tahun="{{ $vehicle->tahun ?? '' }}"
                                    data-dimensi="{{ $vehicle->dimensi_box ?? '' }}"
                                    data-stnk="{{ $vehicle->tahun_stnk ?? '' }}"
                                    data-kir="{{ $vehicle->tahun_kir ?? '' }}"
                                    data-status="{{ $vehicle->status }}"
                                    onclick="openEditVehicleFromBtn(this)">
                                    <i class="fa-solid fa-pen"></i>
                                </button>

                                <button
                                    class="btn btn-danger btn-sm btn-icon"
                                    title="Hapus Armada"
                                    onclick="openDeleteVehicle({{ $vehicle->id }}, '{{ addslashes($vehicle->plat_nomor) }}')">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                        </td>

                    </tr>
                    @empty
                    <tr id="emptyVehicleRow">
                        <td colspan="10">
                            <div class="empty-state">
                                <div class="empty-icon"><i class="fa-solid fa-truck-ramp-box"></i></div>
                                <div class="empty-title">Belum Ada Data Armada</div>
                                <div class="empty-text">Tambahkan kendaraan pertama dengan klik tombol "Tambah Armada"</div>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($vehicles->hasPages())
    <div class="card-footer">
        {{ $vehicles->links('pagination::bootstrap-4') }}
    </div>
    @endif
</div>

@endsection

{{-- ===================== MODALS ===================== --}}
@section('modals')

{{-- ===== MODAL CREATE ===== --}}
<div class="modal-overlay" id="modalCreateVehicle">
    <div class="modal-box modal-lg">
        <div class="modal-header">
            <div class="modal-title">
                <i class="fa-solid fa-truck-front" style="color:var(--primary);margin-right:8px"></i>
                Tambah Armada Baru
            </div>
            <button class="modal-close" onclick="closeModal('modalCreateVehicle')" type="button">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form action="{{ route('vehicles.store') }}" method="POST" id="formCreateVehicle">
            @csrf
            <input type="hidden" name="_action" value="create">

            <div class="modal-body">

                {{-- Row 1: Plat Nomor + Merk --}}
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                    <div class="form-group" style="margin-bottom:0">
                        <label class="form-label" for="c_plat_nomor">
                            Plat Nomor <span class="required">*</span>
                        </label>
                        <input
                            type="text"
                            id="c_plat_nomor"
                            name="plat_nomor"
                            class="form-control {{ $errors->has('plat_nomor') && old('_action') === 'create' ? 'is-invalid' : '' }}"
                            value="{{ old('_action') === 'create' ? old('plat_nomor') : '' }}"
                            placeholder="Contoh: E 1234 AB"
                            style="text-transform:uppercase;font-family:monospace;font-weight:600"
                            required>
                        @if($errors->has('plat_nomor') && old('_action') === 'create')
                            <div class="invalid-feedback">
                                <i class="fa-solid fa-circle-exclamation"></i>
                                {{ $errors->first('plat_nomor') }}
                            </div>
                        @endif
                    </div>

                    <div class="form-group" style="margin-bottom:0">
                        <label class="form-label" for="c_merk">
                            Merk <span class="required">*</span>
                        </label>
                        <input
                            type="text"
                            id="c_merk"
                            name="merk"
                            class="form-control {{ $errors->has('merk') && old('_action') === 'create' ? 'is-invalid' : '' }}"
                            value="{{ old('_action') === 'create' ? old('merk') : '' }}"
                            placeholder="Contoh: Hino, Mitsubishi"
                            required>
                        @if($errors->has('merk') && old('_action') === 'create')
                            <div class="invalid-feedback">
                                <i class="fa-solid fa-circle-exclamation"></i>
                                {{ $errors->first('merk') }}
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Row 2: Jenis + Tahun --}}
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-top:16px">
                    <div class="form-group" style="margin-bottom:0">
                        <label class="form-label" for="c_jenis_kendaraan">
                            Jenis Kendaraan <span class="required">*</span>
                        </label>
                        <select
                            id="c_jenis_kendaraan"
                            name="jenis_kendaraan"
                            class="form-control {{ $errors->has('jenis_kendaraan') && old('_action') === 'create' ? 'is-invalid' : '' }}"
                            required>
                            <option value="">— Pilih Jenis —</option>
                            <option value="Truk Engkel"      {{ old('_action') === 'create' && old('jenis_kendaraan') === 'Truk Engkel'      ? 'selected' : '' }}>Truk Engkel</option>
                            <option value="Truk Double"      {{ old('_action') === 'create' && old('jenis_kendaraan') === 'Truk Double'      ? 'selected' : '' }}>Truk Double</option>
                            <option value="Pickup Box"       {{ old('_action') === 'create' && old('jenis_kendaraan') === 'Pickup Box'       ? 'selected' : '' }}>Pickup Box</option>
                            <option value="Engkel Box"       {{ old('_action') === 'create' && old('jenis_kendaraan') === 'Engkel Box'       ? 'selected' : '' }}>Engkel Box</option>
                            <option value="Box to Box"       {{ old('_action') === 'create' && old('jenis_kendaraan') === 'Box to Box'       ? 'selected' : '' }}>Box to Box</option>
                            <option value="CDD Box"          {{ old('_action') === 'create' && old('jenis_kendaraan') === 'CDD Box'          ? 'selected' : '' }}>CDD Box</option>
                            <option value="FUSO Box"         {{ old('_action') === 'create' && old('jenis_kendaraan') === 'FUSO Box'         ? 'selected' : '' }}>FUSO Box</option>
                            <option value="Tronton"          {{ old('_action') === 'create' && old('jenis_kendaraan') === 'Tronton'          ? 'selected' : '' }}>Tronton</option>
                            <option value="Lainnya"          {{ old('_action') === 'create' && old('jenis_kendaraan') === 'Lainnya'          ? 'selected' : '' }}>Lainnya</option>
                        </select>
                        @if($errors->has('jenis_kendaraan') && old('_action') === 'create')
                            <div class="invalid-feedback">
                                <i class="fa-solid fa-circle-exclamation"></i>
                                {{ $errors->first('jenis_kendaraan') }}
                            </div>
                        @endif
                    </div>

                    <div class="form-group" style="margin-bottom:0">
                        <label class="form-label" for="c_tahun">Tahun</label>
                        <input
                            type="number"
                            id="c_tahun"
                            name="tahun"
                            class="form-control"
                            value="{{ old('_action') === 'create' ? old('tahun') : '' }}"
                            placeholder="Contoh: 2022"
                            min="1990"
                            max="{{ date('Y') + 1 }}">
                    </div>
                </div>

                {{-- Row 3: Dimensi Box / Ukuran Truck + STNK + KIR --}}
                <div style="display:grid;grid-template-columns:1.4fr 1fr 1fr;gap:14px;margin-top:16px">
                    <div class="form-group" style="margin-bottom:0">
                        <label class="form-label" for="c_dimensi_box">Ukuran Truck / Dimensi Box</label>
                        <input
                            type="text"
                            id="c_dimensi_box"
                            name="dimensi_box"
                            class="form-control"
                            value="{{ old('_action') === 'create' ? old('dimensi_box') : '' }}"
                            placeholder="Contoh: 420 x 200 x 200 cm">
                    </div>

                    <div class="form-group" style="margin-bottom:0">
                        <label class="form-label" for="c_tahun_stnk">STNK (DD/MM/YY)</label>
                        <input
                            type="text"
                            id="c_tahun_stnk"
                            name="tahun_stnk"
                            class="form-control"
                            value="{{ old('_action') === 'create' ? old('tahun_stnk') : '' }}"
                            placeholder="Contoh: 11/09/28">
                    </div>

                    <div class="form-group" style="margin-bottom:0">
                        <label class="form-label" for="c_tahun_kir">KIR (DD/MM/YY)</label>
                        <input
                            type="text"
                            id="c_tahun_kir"
                            name="tahun_kir"
                            class="form-control"
                            value="{{ old('_action') === 'create' ? old('tahun_kir') : '' }}"
                            placeholder="Contoh: 15/03/27">
                    </div>
                </div>

                {{-- Row 4: Kepemilikan + Status --}}
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-top:16px">
                    <div class="form-group" style="margin-bottom:0">
                        <label class="form-label" for="c_kepemilikan">
                            Kepemilikan <span class="required">*</span>
                        </label>
                        <select
                            id="c_kepemilikan"
                            name="kepemilikan"
                            class="form-control"
                            required>
                            <option value="internal"  {{ old('_action') === 'create' && (old('kepemilikan') === 'internal' || !old('kepemilikan')) ? 'selected' : '' }}>Internal</option>
                            <option value="eksternal" {{ old('_action') === 'create' && old('kepemilikan') === 'eksternal' ? 'selected' : '' }}>Eksternal</option>
                        </select>
                    </div>

                    <div class="form-group" style="margin-bottom:0">
                        <label class="form-label" for="c_status">
                            Status <span class="required">*</span>
                        </label>
                        <select
                            id="c_status"
                            name="status"
                            class="form-control"
                            required>
                            <option value="siap_jalan"  {{ old('_action') === 'create' && (old('status') === 'siap_jalan' || !old('status')) ? 'selected' : '' }}>Siap Jalan</option>
                            <option value="maintenance" {{ old('_action') === 'create' && old('status') === 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                            <option value="rusak"       {{ old('_action') === 'create' && old('status') === 'rusak' ? 'selected' : '' }}>Rusak</option>
                            <option value="nonaktif"    {{ old('_action') === 'create' && old('status') === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                        </select>
                    </div>
                </div>

            </div>{{-- /.modal-body --}}

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalCreateVehicle')">
                    Batal
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-floppy-disk"></i>
                    Simpan Armada
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ===== MODAL EDIT ===== --}}
<div class="modal-overlay" id="modalEditVehicle">
    <div class="modal-box modal-lg">
        <div class="modal-header">
            <div class="modal-title">
                <i class="fa-solid fa-pen-to-square" style="color:var(--warning);margin-right:8px"></i>
                Edit Data Armada
            </div>
            <button class="modal-close" onclick="closeModal('modalEditVehicle')" type="button">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form id="formEditVehicle" action="" method="POST">
            @csrf
            @method('PUT')
            <input type="hidden" name="_action" value="edit">
            <input type="hidden" name="_vehicle_id" id="e_vehicle_id_hidden">

            <div class="modal-body">

                {{-- Row 1: Plat Nomor + Merk --}}
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                    <div class="form-group" style="margin-bottom:0">
                        <label class="form-label" for="e_plat_nomor">
                            Plat Nomor <span class="required">*</span>
                        </label>
                        <input
                            type="text"
                            id="e_plat_nomor"
                            name="plat_nomor"
                            class="form-control {{ $errors->has('plat_nomor') && old('_action') === 'edit' ? 'is-invalid' : '' }}"
                            placeholder="Contoh: E 1234 AB"
                            style="text-transform:uppercase;font-family:monospace;font-weight:600"
                            required>
                        @if($errors->has('plat_nomor') && old('_action') === 'edit')
                            <div class="invalid-feedback">
                                <i class="fa-solid fa-circle-exclamation"></i>
                                {{ $errors->first('plat_nomor') }}
                            </div>
                        @endif
                    </div>

                    <div class="form-group" style="margin-bottom:0">
                        <label class="form-label" for="e_merk">
                            Merk <span class="required">*</span>
                        </label>
                        <input
                            type="text"
                            id="e_merk"
                            name="merk"
                            class="form-control {{ $errors->has('merk') && old('_action') === 'edit' ? 'is-invalid' : '' }}"
                            placeholder="Contoh: Hino"
                            required>
                        @if($errors->has('merk') && old('_action') === 'edit')
                            <div class="invalid-feedback">
                                <i class="fa-solid fa-circle-exclamation"></i>
                                {{ $errors->first('merk') }}
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Row 2: Jenis + Tahun --}}
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-top:16px">
                    <div class="form-group" style="margin-bottom:0">
                        <label class="form-label" for="e_jenis_kendaraan">
                            Jenis Kendaraan <span class="required">*</span>
                        </label>
                        <select
                            id="e_jenis_kendaraan"
                            name="jenis_kendaraan"
                            class="form-control {{ $errors->has('jenis_kendaraan') && old('_action') === 'edit' ? 'is-invalid' : '' }}"
                            required>
                            <option value="Truk Engkel">Truk Engkel</option>
                            <option value="Truk Double">Truk Double</option>
                            <option value="Pickup Box">Pickup Box</option>
                            <option value="Engkel Box">Engkel Box</option>
                            <option value="Box to Box">Box to Box</option>
                            <option value="CDD Box">CDD Box</option>
                            <option value="FUSO Box">FUSO Box</option>
                            <option value="Tronton">Tronton</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                        @if($errors->has('jenis_kendaraan') && old('_action') === 'edit')
                            <div class="invalid-feedback">
                                <i class="fa-solid fa-circle-exclamation"></i>
                                {{ $errors->first('jenis_kendaraan') }}
                            </div>
                        @endif
                    </div>

                    <div class="form-group" style="margin-bottom:0">
                        <label class="form-label" for="e_tahun">Tahun</label>
                        <input
                            type="number"
                            id="e_tahun"
                            name="tahun"
                            class="form-control"
                            placeholder="Contoh: 2022"
                            min="1990"
                            max="{{ date('Y') + 1 }}">
                    </div>
                </div>

                {{-- Row 3: Dimensi Box / Ukuran Truck + STNK + KIR --}}
                <div style="display:grid;grid-template-columns:1.4fr 1fr 1fr;gap:14px;margin-top:16px">
                    <div class="form-group" style="margin-bottom:0">
                        <label class="form-label" for="e_dimensi_box">Ukuran Truck / Dimensi Box</label>
                        <input
                            type="text"
                            id="e_dimensi_box"
                            name="dimensi_box"
                            class="form-control"
                            placeholder="Contoh: 420 x 200 x 200 cm">
                    </div>

                    <div class="form-group" style="margin-bottom:0">
                        <label class="form-label" for="e_tahun_stnk">STNK (DD/MM/YY)</label>
                        <input
                            type="text"
                            id="e_tahun_stnk"
                            name="tahun_stnk"
                            class="form-control"
                            placeholder="Contoh: 11/09/28">
                    </div>

                    <div class="form-group" style="margin-bottom:0">
                        <label class="form-label" for="e_tahun_kir">KIR (DD/MM/YY)</label>
                        <input
                            type="text"
                            id="e_tahun_kir"
                            name="tahun_kir"
                            class="form-control"
                            placeholder="Contoh: 15/03/27">
                    </div>
                </div>

                {{-- Row 4: Kepemilikan + Status --}}
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-top:16px">
                    <div class="form-group" style="margin-bottom:0">
                        <label class="form-label" for="e_kepemilikan">
                            Kepemilikan <span class="required">*</span>
                        </label>
                        <select id="e_kepemilikan" name="kepemilikan" class="form-control" required>
                            <option value="internal">Internal</option>
                            <option value="eksternal">Eksternal</option>
                        </select>
                    </div>

                    <div class="form-group" style="margin-bottom:0">
                        <label class="form-label" for="e_status">
                            Status <span class="required">*</span>
                        </label>
                        <select id="e_status" name="status" class="form-control" required>
                            <option value="siap_jalan">Siap Jalan</option>
                            <option value="maintenance">Maintenance</option>
                            <option value="rusak">Rusak</option>
                            <option value="nonaktif">Nonaktif</option>
                        </select>
                    </div>
                </div>

            </div>{{-- /.modal-body --}}

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalEditVehicle')">
                    Batal
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-floppy-disk"></i>
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ===== MODAL DELETE ===== --}}
<div class="modal-overlay" id="modalDeleteVehicle">
    <div class="modal-box" style="max-width:440px">
        <div class="modal-header">
            <div class="modal-title" style="color:var(--danger)">
                <i class="fa-solid fa-triangle-exclamation" style="margin-right:8px"></i>
                Konfirmasi Hapus
            </div>
            <button class="modal-close" onclick="closeModal('modalDeleteVehicle')" type="button">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div class="modal-body" style="text-align:center;padding:32px 24px">
            <div style="
                width:64px;height:64px;border-radius:50%;
                background:var(--danger-bg);
                display:flex;align-items:center;justify-content:center;
                margin:0 auto 16px;font-size:28px;color:var(--danger)">
                <i class="fa-solid fa-truck"></i>
            </div>
            <div style="font-size:15px;font-weight:600;color:var(--text-primary);margin-bottom:6px">
                Hapus Data Armada?
            </div>
            <div style="font-size:13px;color:var(--text-secondary);line-height:1.7">
                Armada dengan plat nomor
                <strong id="deleteVehiclePlat" style="color:var(--text-primary)"></strong>
                akan dihapus secara permanen beserta seluruh riwayat terkait.<br>
                <span style="color:var(--danger);font-size:12px">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    Tindakan ini tidak dapat dibatalkan.
                </span>
            </div>
        </div>

        <div class="modal-footer" style="justify-content:center;gap:12px">
            <button type="button" class="btn btn-secondary" onclick="closeModal('modalDeleteVehicle')" style="min-width:120px">
                Batal
            </button>
            <form id="formDeleteVehicle" action="" method="POST" style="display:inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger" style="min-width:120px">
                    <i class="fa-solid fa-trash"></i>
                    Ya, Hapus
                </button>
            </form>
        </div>
    </div>
</div>

{{-- ===== MODAL QR CODE UNIVERSAL AKSES MOBILE (Gerbang Masuk Mobile TOYACHECK) ===== --}}
<div class="modal-overlay" id="modalQrVehicle">
    <div class="modal-box" style="max-width:420px">
        <div class="modal-header">
            <div class="modal-title">
                <i class="fa-solid fa-qrcode" style="color:var(--primary);margin-right:8px"></i>
                QR Code Akses Mobile TOYACHECK
            </div>
            <button class="modal-close" onclick="closeModal('modalQrVehicle')" type="button">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div class="modal-body" style="text-align:center;padding:24px">
            <div id="qrStickerCard" style="border:2px dashed #CBD5E1;border-radius:16px;padding:20px;background:#FFFFFF">
                <div style="font-size:18px;font-weight:800;letter-spacing:0.04em;color:#0F1E36;font-style:italic;margin-bottom:4px">
                    TOYACHECK <span style="display:inline-block;width:6px;height:6px;border-radius:50%;background:#2563EB"></span>
                </div>
                <div style="font-size:11.5px;color:#64748B;margin-bottom:14px">
                    Scan untuk Masuk ke Aplikasi Mobile Driver &amp; HSE
                </div>

                @php
                    $universalMobileUrl = route('mobile.login');
                    $universalQrImg     = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&margin=10&data=' . urlencode($universalMobileUrl);
                @endphp

                <div style="display:flex;justify-content:center;margin:10px 0 14px">
                    <img id="qrVehicleImg" src="{{ $universalQrImg }}" alt="QR Code TOYACHECK Mobile" style="width:210px;height:210px;border:1px solid #E2E8F0;border-radius:12px;padding:8px;background:#fff">
                </div>

                <div style="display:inline-block;background:#0F1E36;color:#FFFFFF;padding:5px 16px;border-radius:6px;font-size:12px;font-weight:700;letter-spacing:0.05em;margin-bottom:6px">
                    AKSES MOBILE UNIVERSAL
                </div>
                <div style="font-size:12px;color:#475569;line-height:1.5">
                    Tempelkan QR Code ini pada armada truk. Driver cukup scan untuk membuka halaman Login Mobile lalu memilih kendaraannya.
                </div>
            </div>

            <div style="margin-top:12px;font-size:11.5px;color:var(--text-secondary)">
                URL Gerbang Mobile:<br>
                <a href="{{ $universalMobileUrl }}" target="_blank" style="color:var(--primary);font-weight:600;word-break:break-all">
                    {{ $universalMobileUrl }}
                </a>
            </div>
        </div>

        <div class="modal-footer" style="justify-content:center;gap:8px;flex-wrap:wrap">
            <a href="{{ $universalQrImg }}" download="QR-TOYACHECK-Mobile.png" target="_blank" class="btn btn-secondary">
                <i class="fa-solid fa-download"></i>
                Unduh Gambar QR
            </a>
            <a href="{{ route('vehicles.qr.print') }}" target="_blank" class="btn btn-primary">
                <i class="fa-solid fa-file-pdf"></i>
                Cetak Stiker / PDF
            </a>
        </div>
    </div>
</div>

@endsection

{{-- ===================== PAGE SCRIPTS ===================== --}}
@push('scripts')
<script>
(function () {
    'use strict';

    /* ─────────────────────────────────────────
       Re-open modal on validation error
    ───────────────────────────────────────── */
    @if($errors->any())
        @if(old('_action') === 'create')
            document.addEventListener('DOMContentLoaded', function () {
                window.openModal('modalCreateVehicle');
            });
        @endif

        @if(old('_action') === 'edit' && old('_vehicle_id'))
            document.addEventListener('DOMContentLoaded', function () {
                var id = '{{ old('_vehicle_id') }}';
                document.getElementById('formEditVehicle').action = '/vehicles/' + id;
                document.getElementById('e_vehicle_id_hidden').value = id;
                document.getElementById('e_plat_nomor').value    = '{{ addslashes(old('plat_nomor')) }}';
                document.getElementById('e_merk').value          = '{{ addslashes(old('merk')) }}';
                document.getElementById('e_jenis_kendaraan').value = '{{ addslashes(old('jenis_kendaraan')) }}';
                document.getElementById('e_kepemilikan').value   = '{{ addslashes(old('kepemilikan', 'internal')) }}';
                document.getElementById('e_tahun').value         = '{{ old('tahun') }}';
                document.getElementById('e_dimensi_box').value   = '{{ addslashes(old('dimensi_box')) }}';
                document.getElementById('e_tahun_stnk').value    = '{{ addslashes(old('tahun_stnk')) }}';
                document.getElementById('e_tahun_kir').value     = '{{ addslashes(old('tahun_kir')) }}';
                document.getElementById('e_status').value        = '{{ old('status') }}';
                window.openModal('modalEditVehicle');
            });
        @endif
    @endif

    /* ─────────────────────────────────────────
       openEditVehicleFromBtn(btn)
    ───────────────────────────────────────── */
    window.openEditVehicleFromBtn = function (btn) {
        var id = btn.getAttribute('data-id');
        var form = document.getElementById('formEditVehicle');
        form.action = '/vehicles/' + id;

        document.getElementById('e_vehicle_id_hidden').value    = id;
        document.getElementById('e_plat_nomor').value           = btn.getAttribute('data-plat') || '';
        document.getElementById('e_merk').value                 = btn.getAttribute('data-merk') || '';
        document.getElementById('e_jenis_kendaraan').value      = btn.getAttribute('data-jenis') || '';
        document.getElementById('e_kepemilikan').value          = btn.getAttribute('data-kepemilikan') || 'internal';
        document.getElementById('e_tahun').value                = btn.getAttribute('data-tahun') || '';
        document.getElementById('e_dimensi_box').value          = btn.getAttribute('data-dimensi') || '';
        document.getElementById('e_tahun_stnk').value           = btn.getAttribute('data-stnk') || '';
        document.getElementById('e_tahun_kir').value            = btn.getAttribute('data-kir') || '';
        document.getElementById('e_status').value               = btn.getAttribute('data-status') || 'siap_jalan';

        window.openModal('modalEditVehicle');
    };

    /* ─────────────────────────────────────────
       openDeleteVehicle(id, plat)
    ───────────────────────────────────────── */
    window.openDeleteVehicle = function (id, plat) {
        document.getElementById('deleteVehiclePlat').textContent = plat;
        document.getElementById('formDeleteVehicle').action = '/vehicles/' + id;
        window.openModal('modalDeleteVehicle');
    };

    /* ─────────────────────────────────────────
       openUniversalQr()
    ───────────────────────────────────────── */
    window.openUniversalQr = function () {
        window.openModal('modalQrVehicle');
    };

    /* ─────────────────────────────────────────
       filterVehicleTable — live search
    ───────────────────────────────────────── */
    window.filterVehicleTable = function (query) {
        applyVehicleFilter();
    };

    window.filterByStatus = function (status) {
        applyVehicleFilter();
    };

    function applyVehicleFilter() {
        var query      = (document.getElementById('vehicleSearch').value || '').toLowerCase().trim();
        var statusVal  = (document.getElementById('statusFilter').value || '').toLowerCase();
        var rows       = document.querySelectorAll('#vehicleTableBody tr[data-plat]');
        var visible    = 0;

        rows.forEach(function (row) {
            var plat  = row.getAttribute('data-plat')  || '';
            var merk  = row.getAttribute('data-merk')  || '';
            var jenis = row.getAttribute('data-jenis') || '';
            var st    = row.getAttribute('data-status')|| '';

            var textMatch   = !query || (plat + merk + jenis).includes(query);
            var statusMatch = !statusVal || st === statusVal;

            var show = textMatch && statusMatch;
            row.style.display = show ? '' : 'none';
            if (show) visible++;
        });

        // Empty no-result placeholder
        var noResult = document.getElementById('noVehicleResult');
        var hasQuery = query || statusVal;

        if (hasQuery && visible === 0) {
            if (!noResult) {
                noResult = document.createElement('tr');
                noResult.id = 'noVehicleResult';
                noResult.innerHTML =
                    '<td colspan="9">' +
                    '<div class="empty-state">' +
                    '<div class="empty-icon"><i class="fa-solid fa-magnifying-glass"></i></div>' +
                    '<div class="empty-title">Tidak Ditemukan</div>' +
                    '<div class="empty-text">Tidak ada armada yang cocok dengan filter yang dipilih.</div>' +
                    '</div></td>';
                document.getElementById('vehicleTableBody').appendChild(noResult);
            }
        } else if (noResult) {
            noResult.remove();
        }
    }

    /* ─────────────────────────────────────────
       Auto-uppercase Plat on forms
    ───────────────────────────────────────── */
    ['c_plat_nomor', 'e_plat_nomor'].forEach(function (id) {
        var el = document.getElementById(id);
        if (el) {
            el.addEventListener('input', function () {
                var pos = this.selectionStart;
                this.value = this.value.toUpperCase();
                this.setSelectionRange(pos, pos);
            });
        }
    });

})();
</script>
@endpush

@extends('layouts.app')

@section('title', 'Vendor')
@section('page-title', 'Vendor')
@section('page-subtitle', 'Direktori supplier suku cadang dan bidang keahliannya')

@section('page-actions')
    <button class="btn btn-primary" onclick="openCreateVendor()" id="btn-add-vendor-modal">
        <i class="fa-solid fa-plus"></i>
        Tambah Vendor
    </button>
@endsection

@section('content')

{{-- ── STAT CARDS ── --}}
<div class="stats-grid" style="grid-template-columns: repeat(3, 1fr); margin-bottom: 24px;">

    <div class="stat-card">
        <div class="stat-icon blue"><i class="fa-solid fa-store"></i></div>
        <div class="stat-info">
            <div class="stat-label">Total Vendor</div>
            <div class="stat-value">{{ $stats['total'] }}</div>
            <div class="stat-change">Supplier terdaftar</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon green"><i class="fa-solid fa-circle-check"></i></div>
        <div class="stat-info">
            <div class="stat-label">Aktif</div>
            <div class="stat-value">{{ $stats['aktif'] }}</div>
            <div class="stat-change">Bisa dipakai pengadaan</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon red"><i class="fa-solid fa-ban"></i></div>
        <div class="stat-info">
            <div class="stat-label">Nonaktif</div>
            <div class="stat-value">{{ $stats['nonaktif'] }}</div>
            <div class="stat-change">Tidak digunakan</div>
        </div>
    </div>

</div>

{{-- ── TABEL ── --}}
<div class="card">
    <div class="card-header">
        <div>
            <div class="card-title">Daftar Vendor</div>
            <div class="card-subtitle">Total {{ $vendors->total() }} vendor</div>
        </div>

        <form method="GET" action="{{ route('vendors.index') }}" class="d-flex gap-8 align-center">
            <div style="position:relative">
                <i class="fa-solid fa-magnifying-glass"
                   style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:var(--text-muted);font-size:13px;pointer-events:none"></i>
                <input type="text" name="q" value="{{ $search }}" class="form-control"
                       placeholder="Cari nama / kontak / keahlian..." style="padding-left:32px;width:260px">
            </div>
            <select name="status" class="form-control" style="width:140px" onchange="this.form.submit()">
                <option value="">Semua Status</option>
                <option value="aktif" {{ $status === 'aktif' ? 'selected' : '' }}>Aktif</option>
                <option value="nonaktif" {{ $status === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
            </select>
            <button type="submit" class="btn btn-secondary">Cari</button>
        </form>
    </div>

    <div class="card-body-p0">
        <div class="table-wrap">
            <table class="tc-table">
                <thead>
                    <tr>
                        <th style="width:52px">#</th>
                        <th>Vendor</th>
                        <th>Kontak</th>
                        <th>Keahlian</th>
                        <th>Status</th>
                        <th style="width:110px;text-align:center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($vendors as $index => $vendor)
                    <tr>
                        <td style="color:var(--text-muted);font-size:12px">{{ $vendors->firstItem() + $index }}</td>

                        <td>
                            <div style="font-weight:600">{{ $vendor->nama_vendor }}</div>
                            @if($vendor->alamat)
                                <div style="font-size:11px;color:var(--text-muted)">{{ \Illuminate\Support\Str::limit($vendor->alamat, 50) }}</div>
                            @endif
                        </td>

                        <td>
                            <div style="font-weight:500">{{ $vendor->kontak_person ?: '—' }}</div>
                            <div style="font-size:12px;color:var(--text-secondary)">
                                {{ $vendor->telepon ?: '' }}{{ $vendor->telepon && $vendor->email ? ' · ' : '' }}{{ $vendor->email ?: '' }}
                            </div>
                        </td>

                        <td>
                            @if($vendor->keahlian)
                                <span class="badge badge-neutral">{{ $vendor->keahlian }}</span>
                            @else
                                <span style="color:var(--text-muted)">—</span>
                            @endif
                        </td>

                        <td>
                            <span class="badge {{ $vendor->is_active ? 'badge-success' : 'badge-neutral' }}">
                                {{ $vendor->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>

                        <td>
                            <div class="d-flex gap-8" style="justify-content:center">
                                <button class="btn btn-warning btn-sm btn-icon" title="Edit"
                                        onclick="openEditVendor({{ \Illuminate\Support\Js::from($vendor) }})">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                <button class="btn btn-danger btn-sm btn-icon" title="Hapus"
                                        onclick="openDeleteVendor({{ $vendor->id }}, {{ \Illuminate\Support\Js::from($vendor->nama_vendor) }})">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6">
                            <div class="empty-state">
                                <div class="empty-icon"><i class="fa-solid fa-store"></i></div>
                                <div class="empty-title">Belum Ada Vendor</div>
                                <div class="empty-text">Tambahkan supplier suku cadang lewat tombol "Tambah Vendor".</div>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($vendors->hasPages())
    <div class="card-footer">
        {{ $vendors->links('pagination::bootstrap-4') }}
    </div>
    @endif
</div>

@endsection

@section('modals')

{{-- Field form dipakai bersama oleh modal tambah & edit --}}
@php
    $fields = function (string $p) {
        return [
            ['nama_vendor', 'Nama Vendor', 'text', true],
            ['kontak_person', 'Kontak Person', 'text', false],
            ['telepon', 'Telepon', 'text', false],
            ['email', 'Email', 'email', false],
            ['keahlian', 'Keahlian (mis. Ban, Aki, Oli)', 'text', false],
        ];
    };
@endphp

@foreach(['create' => 'Tambah Vendor', 'edit' => 'Edit Vendor'] as $mode => $title)
<div class="modal-overlay" id="modal{{ ucfirst($mode) }}Vendor">
    <div class="modal-box">
        <div class="modal-header">
            <div class="modal-title">
                <i class="fa-solid {{ $mode === 'create' ? 'fa-plus' : 'fa-pen-to-square' }}"
                   style="color:{{ $mode === 'create' ? 'var(--primary)' : 'var(--warning)' }};margin-right:8px"></i>
                {{ $title }}
            </div>
            <button class="modal-close" type="button" onclick="closeModal('modal{{ ucfirst($mode) }}Vendor')">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form id="form{{ ucfirst($mode) }}Vendor" method="POST"
              action="{{ $mode === 'create' ? route('vendors.store') : '' }}">
            @csrf
            @if($mode === 'edit') @method('PUT') @endif
            <input type="hidden" name="_form" value="{{ $mode }}">
            <input type="hidden" name="_id" id="{{ $mode }}_id" value="">

            <div class="modal-body">

                @if($errors->any() && old('_form') === $mode)
                    <div class="invalid-feedback" style="margin-bottom:14px;font-size:13px">
                        <i class="fa-solid fa-circle-exclamation"></i> {{ $errors->first() }}
                    </div>
                @endif

                @foreach($fields($mode) as [$name, $label, $type, $required])
                <div class="form-group">
                    <label class="form-label" for="{{ $mode }}_{{ $name }}">
                        {{ $label }} @if($required)<span class="required">*</span>@endif
                    </label>
                    <input type="{{ $type }}" id="{{ $mode }}_{{ $name }}" name="{{ $name }}"
                           class="form-control" @if($required) required @endif>
                </div>
                @endforeach

                <div class="form-group">
                    <label class="form-label" for="{{ $mode }}_alamat">Alamat</label>
                    <textarea id="{{ $mode }}_alamat" name="alamat" rows="2" class="form-control"></textarea>
                </div>

                <div class="form-group" style="margin-bottom:0">
                    <label class="form-label" for="{{ $mode }}_is_active">Status <span class="required">*</span></label>
                    <select id="{{ $mode }}_is_active" name="is_active" class="form-control" required>
                        <option value="1">Aktif</option>
                        <option value="0">Nonaktif</option>
                    </select>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modal{{ ucfirst($mode) }}Vendor')">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Simpan</button>
            </div>
        </form>
    </div>
</div>
@endforeach

{{-- Modal Hapus --}}
<div class="modal-overlay" id="modalDeleteVendor">
    <div class="modal-box" style="max-width:440px">
        <div class="modal-header">
            <div class="modal-title" style="color:var(--danger)">
                <i class="fa-solid fa-triangle-exclamation" style="margin-right:8px"></i>
                Hapus Vendor
            </div>
            <button class="modal-close" type="button" onclick="closeModal('modalDeleteVendor')">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <div class="modal-body" style="text-align:center;padding:28px 20px">
            Yakin ingin menghapus vendor <strong id="del_vendor_name"></strong>?
        </div>
        <div class="modal-footer" style="justify-content:center">
            <button type="button" class="btn btn-secondary" onclick="closeModal('modalDeleteVendor')">Batal</button>
            <form id="formDeleteVendor" method="POST" action="" style="display:inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger"><i class="fa-solid fa-trash"></i> Ya, Hapus</button>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
(function () {
    'use strict';

    var fieldNames = ['nama_vendor', 'kontak_person', 'telepon', 'email', 'keahlian', 'alamat', 'is_active'];

    function fill(mode, data) {
        fieldNames.forEach(function (name) {
            var el = document.getElementById(mode + '_' + name);
            if (!el) return;
            var value = data[name];
            if (name === 'is_active') value = (value === true || value === 1 || value === '1') ? '1' : '0';
            el.value = (value === null || value === undefined) ? '' : value;
        });
    }

    window.openCreateVendor = function () {
        fill('create', { is_active: 1 });
        openModal('modalCreateVendor');
    };

    window.openEditVendor = function (vendor) {
        document.getElementById('formEditVendor').action = '/vendors/' + vendor.id;
        document.getElementById('edit_id').value = vendor.id;
        fill('edit', vendor);
        openModal('modalEditVendor');
    };

    window.openDeleteVendor = function (id, name) {
        document.getElementById('del_vendor_name').textContent = name;
        document.getElementById('formDeleteVendor').action = '/vendors/' + id;
        openModal('modalDeleteVendor');
    };

    // Jika validasi gagal, buka kembali modal terakhir dengan input lama
    @if($errors->any() && old('_form'))
    (function () {
        var mode = @json(old('_form'));
        var old  = @json(\Illuminate\Support\Arr::except(old(), ['_token', '_method']));
        if (mode === 'edit') {
            document.getElementById('formEditVendor').action = '/vendors/' + old._id;
            document.getElementById('edit_id').value = old._id;
        }
        fill(mode, old);
        openModal(mode === 'edit' ? 'modalEditVendor' : 'modalCreateVendor');
    })();
    @endif
})();
</script>
@endpush

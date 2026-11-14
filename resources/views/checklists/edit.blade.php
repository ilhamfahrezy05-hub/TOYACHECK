@extends('layouts.app')

@section('title', 'Edit Hasil Checklist — ' . ($checklist->vehicle->plat_nomor ?? 'Armada'))
@section('page-title', 'Edit Hasil Checklist ' . ($checklist->checklist_type === 'k3' ? 'K3 & Kebersihan' : 'Fisik Kendaraan'))
@section('page-subtitle', 'Sesuaikan kondisi pemeriksaan ' . strtoupper($checklist->vehicle->plat_nomor ?? '-') . ' (' . ($checklist->vehicle->merk ?? '') . ')')

@section('page-actions')
    <a href="{{ route('checklists.show', $checklist->id) }}" class="btn btn-secondary">
        <i class="fa-solid fa-arrow-left"></i>
        Kembali ke Detail
    </a>
@endsection

@push('styles')
<style>
    .ck-edit-grid { display: grid; grid-template-columns: 330px 1fr; gap: 20px; align-items: start; }
    @media (max-width: 992px) { .ck-edit-grid { grid-template-columns: 1fr; } }

    .ck-row {
        padding: 14px 18px;
        border-bottom: 1px solid #F1F5F9;
        transition: background .15s;
    }
    .ck-row:last-child { border-bottom: 0; }
    .ck-row.is-bad { background: #FEF2F2; }

    .ck-row-main {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
    }

    .ck-toggle { display: inline-flex; background: #F1F5F9; border-radius: 8px; padding: 3px; flex-shrink: 0; }
    .ck-toggle input { display: none; }
    .ck-toggle label {
        margin: 0;
        padding: 6px 14px;
        font-size: 12px;
        font-weight: 600;
        border-radius: 6px;
        cursor: pointer;
        color: var(--text-secondary);
        transition: all .15s;
        user-select: none;
    }
    .ck-toggle input.ok:checked + label  { background: var(--success); color: #fff; box-shadow: 0 1px 4px rgba(22,163,74,.35); }
    .ck-toggle input.bad:checked + label { background: var(--danger);  color: #fff; box-shadow: 0 1px 4px rgba(220,38,38,.35); }

    .ck-meta-row {
        display: flex; justify-content: space-between; align-items: center; gap: 12px;
        padding: 10px 0; border-bottom: 1px dashed var(--border); font-size: 13px;
    }
    .ck-meta-row:last-child { border-bottom: 0; }
    .ck-meta-key { color: var(--text-secondary); }
    .ck-meta-val { font-weight: 600; color: var(--text-primary); text-align: right; }
</style>
@endpush

@section('content')

<form action="{{ route('checklists.update', $checklist->id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="ck-edit-grid">

        {{-- Kolom Kiri: Info & Catatan --}}
        <div class="card">
            <div class="card-header">
                <div>
                    <div class="card-title">Informasi Pemeriksaan</div>
                    <div class="card-subtitle">#{{ str_pad($checklist->id, 5, '0', STR_PAD_LEFT) }}</div>
                </div>
                @if($checklist->checklist_type === 'k3')
                    <span class="badge badge-info"><i class="fa-solid fa-shield-halved"></i> K3</span>
                @else
                    <span class="badge badge-primary"><i class="fa-solid fa-truck-ramp-box"></i> Fisik</span>
                @endif
            </div>

            <div class="card-body">
                <div class="ck-meta-row">
                    <span class="ck-meta-key">Plat Nomor</span>
                    <span class="ck-meta-val">
                        <span style="background:#0F1E36;color:#fff;padding:2px 9px;border-radius:4px;font-size:12px;font-family:monospace">
                            {{ strtoupper($checklist->vehicle->plat_nomor ?? '-') }}
                        </span>
                    </span>
                </div>

                <div class="ck-meta-row">
                    <span class="ck-meta-key">Armada</span>
                    <span class="ck-meta-val">{{ $checklist->vehicle->merk ?? '—' }} · {{ $checklist->vehicle->jenis_kendaraan ?? '' }}</span>
                </div>

                <div class="ck-meta-row">
                    <span class="ck-meta-key">Petugas Awal</span>
                    <span class="ck-meta-val">{{ $checklist->driver->name ?? $checklist->user->name ?? '—' }}</span>
                </div>

                <div class="form-group" style="margin-top:14px">
                    <label class="form-label" for="editTanggal">Tanggal &amp; Waktu Inspeksi</label>
                    <input type="datetime-local"
                           id="editTanggal"
                           name="tanggal"
                           class="form-control"
                           value="{{ old('tanggal', $checklist->tanggal->format('Y-m-d\TH:i')) }}">
                </div>

                <div class="form-group" style="margin-bottom:0">
                    <label class="form-label" for="editCatatan">Catatan Pemeriksaan</label>
                    <textarea id="editCatatan"
                              name="catatan"
                              rows="4"
                              class="form-control"
                              placeholder="Tambahkan atau perbarui catatan temuan / perbaikan...">{{ old('catatan', $checklist->catatan) }}</textarea>
                </div>

                <button type="submit" class="btn btn-primary" style="width:100%;margin-top:18px;justify-content:center">
                    <i class="fa-solid fa-floppy-disk"></i>
                    Simpan Perubahan Checklist
                </button>
            </div>
        </div>

        {{-- Kolom Kanan: Item Checklist --}}
        <div class="card">
            <div class="card-header">
                <div>
                    <div class="card-title">Daftar Item {{ $checklist->checklist_type === 'k3' ? 'K3 & Kebersihan' : 'Fisik Kendaraan' }}</div>
                    <div class="card-subtitle">Ubah status masing-masing komponen antara Baik atau Bermasalah</div>
                </div>
                <button type="button" class="btn btn-secondary btn-sm" onclick="markAllEditOk()">
                    <i class="fa-solid fa-check-double"></i> Tandai Semua Baik
                </button>
            </div>

            <div class="card-body-p0">
                @foreach($checklist->items as $item)
                    @php
                        $isOk = (bool) old("items.{$item->id}", $item->kondisi ? '1' : '0');
                    @endphp
                    <div class="ck-row {{ $isOk ? '' : 'is-bad' }}" id="editCkRow_{{ $item->id }}">
                        <div class="ck-row-main">
                            <div style="flex:1">
                                <div style="font-size:13.5px;font-weight:600;color:var(--text-primary)">
                                    {{ $item->item }}
                                </div>
                                @if($item->criteria)
                                    <div style="font-size:11.5px;color:var(--text-muted);margin-top:2px">
                                        {{ $item->criteria }}
                                    </div>
                                @endif
                            </div>

                            <div class="ck-toggle">
                                <input type="radio"
                                       class="ok edit-ck-radio"
                                       name="items[{{ $item->id }}]"
                                       id="edit_ok_{{ $item->id }}"
                                       value="1"
                                       {{ $isOk ? 'checked' : '' }}
                                       onchange="toggleEditCkRow({{ $item->id }}, true)">
                                <label for="edit_ok_{{ $item->id }}">
                                    <i class="fa-solid fa-check"></i> Baik
                                </label>

                                <input type="radio"
                                       class="bad edit-ck-radio"
                                       name="items[{{ $item->id }}]"
                                       id="edit_bad_{{ $item->id }}"
                                       value="0"
                                       {{ !$isOk ? 'checked' : '' }}
                                       onchange="toggleEditCkRow({{ $item->id }}, false)">
                                <label for="edit_bad_{{ $item->id }}">
                                    <i class="fa-solid fa-xmark"></i> Bermasalah
                                </label>
                            </div>
                        </div>

                        <div style="margin-top:10px;display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap">
                            <div style="font-size:11.5px;color:var(--text-secondary)">
                                <label style="cursor:pointer;display:inline-flex;align-items:center;gap:6px">
                                    <i class="fa-solid fa-camera" style="color:var(--primary)"></i>
                                    <span>{{ $item->foto_dokumentasi ? 'Ganti Foto Bukti' : 'Upload Foto Bukti (Opsional)' }}:</span>
                                    <input type="file" name="foto[{{ $item->id }}]" accept="image/*" style="font-size:11px">
                                </label>
                            </div>

                            @if($item->foto_dokumentasi)
                                <a href="{{ asset('storage/' . $item->foto_dokumentasi) }}"
                                   target="_blank"
                                   style="font-size:11.5px;font-weight:600;color:var(--primary);text-decoration:none">
                                    <i class="fa-regular fa-image"></i> Lihat Foto Saat Ini
                                </a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

    </div>
</form>

@endsection

@push('scripts')
<script>
    function toggleEditCkRow(id, isOk) {
        var row = document.getElementById('editCkRow_' + id);
        if (!row) return;
        row.classList.toggle('is-bad', !isOk);
    }

    function markAllEditOk() {
        document.querySelectorAll('input.ok.edit-ck-radio').forEach(function (inp) {
            inp.checked = true;
            var id = inp.id.replace('edit_ok_', '');
            toggleEditCkRow(id, true);
        });
    }
</script>
@endpush


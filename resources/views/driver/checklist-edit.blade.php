@extends('driver.layout')

@section('title', 'Edit Laporan K3 — ' . ($checklist->vehicle->plat_nomor ?? ''))
@section('back_url', route('driver.history.detail', $checklist->id))
@section('header_title')
    Edit laporan kebersihan<br>kendaraan (K3)
@endsection

@push('styles')
<style>
    .m-check-hero {
        padding: 18px 20px 14px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        background: #FFFFFF;
    }

    .m-check-truck {
        width: 54%;
        margin-left: -12px;
    }

    .m-check-actions {
        width: 46%;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 14px;
    }

    .m-btn-submit-green {
        background: #52B838;
        color: #FFFFFF;
        border: none;
        border-radius: 999px;
        padding: 9px 24px;
        font-size: 13px;
        font-weight: 700;
        font-family: inherit;
        cursor: pointer;
        box-shadow: 0 4px 10px rgba(82, 184, 56, 0.32);
    }

    .m-plat-pill {
        background: #F1F2F4;
        color: #111827;
        padding: 6px 16px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 800;
        letter-spacing: 0.03em;
        box-shadow: 0 3px 6px rgba(0, 0, 0, 0.14);
        border: 1px solid #E5E7EB;
    }

    .m-items-container {
        padding: 8px 18px 20px;
        display: flex;
        flex-direction: column;
        gap: 14px;
    }

    .m-item-card {
        background: linear-gradient(180deg, #EBECEE 0%, #D4D6DA 100%);
        border-radius: 14px;
        padding: 13px 15px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.16);
        border: 1.5px solid transparent;
        transition: border-color 0.2s, background 0.2s;
    }

    .m-item-card.status-aman {
        border-color: #4CAF28;
        background: linear-gradient(180deg, #F0FDF4 0%, #DCFCE7 100%);
    }

    .m-item-card.status-kendala {
        border-color: #DC2626;
        background: linear-gradient(180deg, #FEF2F2 0%, #FEE2E2 100%);
    }

    .m-item-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 10px;
    }

    .m-item-title {
        font-size: 12.5px;
        font-weight: 700;
        color: #111827;
        margin-bottom: 2px;
    }

    .m-item-criteria {
        font-size: 10px;
        color: #374151;
        line-height: 1.38;
    }

    .m-condition-selector {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
        margin-top: 8px;
    }

    .m-choice-btn {
        padding: 8px 10px;
        border-radius: 8px;
        border: 1.5px solid #D1D5DB;
        background: #FFFFFF;
        font-size: 11px;
        font-weight: 700;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
    }

    .m-choice-btn.aman.selected {
        background: #22C55E;
        color: #FFFFFF;
        border-color: #16A34A;
    }

    .m-choice-btn.kendala.selected {
        background: #DC2626;
        color: #FFFFFF;
        border-color: #B91C1C;
    }

    .m-btn-camera {
        margin-top: 8px;
        background: #FFFFFF;
        color: #1F2937;
        border: 1px solid #D1D5DB;
        border-radius: 6px;
        padding: 6px 10px;
        font-size: 11px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        cursor: pointer;
    }

    .m-notes-box {
        margin: 4px 18px 24px;
        background: #F9FAFB;
        border: 1px solid #E5E7EB;
        border-radius: 14px;
        padding: 14px;
    }

    .m-notes-textarea {
        width: 100%;
        border: 1px solid #D1D5DB;
        border-radius: 10px;
        padding: 10px 12px;
        font-size: 12px;
        font-family: inherit;
        resize: vertical;
        min-height: 68px;
        outline: none;
    }
</style>
@endpush

@section('content')

<form action="{{ route('driver.history.update', $checklist->id) }}"
      method="POST"
      enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="m-check-hero">
        <div class="m-check-truck">
            @include('driver.partials.truck-illust', ['vehicle' => $checklist->vehicle, 'size' => 'lg'])
        </div>

        <div class="m-check-actions">
            <button type="submit" class="m-btn-submit-green">
                Simpan
            </button>

            <div class="m-plat-pill">
                {{ strtoupper($checklist->vehicle->plat_nomor ?? '-') }}
            </div>
        </div>
    </div>

    <div class="m-items-container">
        @foreach($checklist->items as $cItem)
            @php
                $isAman = (bool) $cItem->kondisi;
            @endphp
            <div class="m-item-card {{ $isAman ? 'status-aman' : 'status-kendala' }}" id="editCard_{{ $cItem->id }}">
                <input type="hidden" name="items[{{ $cItem->id }}]" id="editInput_{{ $cItem->id }}" value="{{ $isAman ? '1' : '0' }}">

                <div class="m-item-header">
                    <div>
                        <div class="m-item-title">{{ $cItem->item }}</div>
                        <div class="m-item-criteria">{{ $cItem->criteria ?? '-' }}</div>
                    </div>
                </div>

                <div class="m-condition-selector">
                    <button type="button"
                            class="m-choice-btn aman {{ $isAman ? 'selected' : '' }}"
                            id="editAman_{{ $cItem->id }}"
                            onclick="setEditCondition({{ $cItem->id }}, '1')">
                        <i class="fa-solid fa-circle-check"></i>
                        Aman / Sesuai
                    </button>

                    <button type="button"
                            class="m-choice-btn kendala {{ !$isAman ? 'selected' : '' }}"
                            id="editKendala_{{ $cItem->id }}"
                            onclick="setEditCondition({{ $cItem->id }}, '0')">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                        Ada Kendala
                    </button>
                </div>

                <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px">
                    <label class="m-btn-camera">
                        <i class="fa-solid fa-camera"></i>
                        <span>{{ $cItem->foto_dokumentasi ? 'Ganti Foto Bukti' : 'Tambah Foto Bukti' }}</span>
                        <input type="file"
                               name="foto[{{ $cItem->id }}]"
                               accept="image/*"
                               capture="environment"
                               style="display:none">
                    </label>

                    @if($cItem->foto_dokumentasi)
                        <a href="{{ asset('storage/' . $cItem->foto_dokumentasi) }}"
                           target="_blank"
                           style="font-size:10.5px;font-weight:700;color:#0B2E8A;text-decoration:underline;margin-top:8px">
                            Lihat Foto Saat Ini
                        </a>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <div class="m-notes-box">
        <label style="display:block;font-size:12px;font-weight:700;color:#111827;margin-bottom:6px">
            <i class="fa-regular fa-comment-dots" style="margin-right:4px;color:#0B2E8A"></i>
            Catatan Laporan K3
        </label>
        <textarea name="catatan"
                  class="m-notes-textarea"
                  placeholder="Tuliskan catatan penggantian laporan atau kondisi kebersihan kendaraan...">{{ old('catatan', $checklist->catatan) }}</textarea>

        <button type="submit" class="m-btn-submit-green" style="width:100%;margin-top:12px;padding:12px">
            <i class="fa-solid fa-floppy-disk" style="margin-right:6px"></i>
            Perbarui Laporan K3
        </button>
    </div>
</form>

@endsection

@push('scripts')
<script>
    function setEditCondition(id, val) {
        var input   = document.getElementById('editInput_' + id);
        var card    = document.getElementById('editCard_' + id);
        var btnAman = document.getElementById('editAman_' + id);
        var btnKen  = document.getElementById('editKendala_' + id);

        input.value = val;
        card.classList.remove('status-aman', 'status-kendala');
        btnAman.classList.remove('selected');
        btnKen.classList.remove('selected');

        if (val === '1') {
            card.classList.add('status-aman');
            btnAman.classList.add('selected');
        } else {
            card.classList.add('status-kendala');
            btnKen.classList.add('selected');
        }
    }
</script>
@endpush


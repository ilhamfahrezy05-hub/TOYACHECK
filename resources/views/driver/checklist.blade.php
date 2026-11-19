@extends('driver.layout')

@section('title', $pageTitle . ' — ' . $vehicle->plat_nomor)
@section('back_url', route('driver.vehicle.show', $vehicle->id))
@section('header_title')
    {!! $type === 'k3' ? 'Kondisi kebersihan<br>kendaraan' : 'Pemeriksaan fisik<br>kendaraan' !!}
@endsection
@section('right_action')
    <button type="button" class="m-topbar-btn" onclick="openHelpSheet()" title="Panduan Pemeriksaan">
        <i class="fa-regular fa-circle-question"></i>
    </button>
@endsection

@push('styles')
<style>
    /* Bagian Atas: Truk di kiri + Tombol Submit Hijau & Pill Plat Nomor di kanan (persis Figma) */
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
        gap: 16px;
    }

    .m-btn-submit-green {
        background: #4CAF28;
        color: #FFFFFF;
        border: none;
        border-radius: 999px;
        padding: 9px 28px;
        font-size: 14px;
        font-weight: 700;
        font-family: inherit;
        cursor: pointer;
        box-shadow: 0 4px 10px rgba(76, 175, 40, 0.32);
        transition: background 0.15s, transform 0.15s;
        min-width: 112px;
    }

    .m-btn-submit-green:hover {
        background: #3B8C1E;
    }

    .m-btn-submit-green:active {
        transform: scale(0.96);
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

    /* Progress Bar Mini */
    .m-progress-strip {
        margin: 0 20px 12px;
        background: #F3F4F6;
        border-radius: 10px;
        padding: 8px 12px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-size: 11.5px;
        color: #374151;
    }

    .m-btn-check-all {
        background: #0B2E8A;
        color: #FFFFFF;
        border: none;
        border-radius: 6px;
        padding: 4px 10px;
        font-size: 10.5px;
        font-weight: 700;
        cursor: pointer;
    }

    .m-btn-repair-mode {
        background: #D97706;
        color: #FFFFFF;
        border: none;
        border-radius: 6px;
        padding: 5px 11px;
        font-size: 10.5px;
        font-weight: 700;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        transition: all 0.15s;
        box-shadow: 0 2px 6px rgba(217, 119, 6, 0.25);
    }

    .m-btn-repair-mode.active {
        background: #991B1B;
        box-shadow: 0 2px 8px rgba(153, 27, 27, 0.35);
    }

    .m-item-card.locked-repair {
        background: #E5E7EB !important;
        border-color: #CBD5E1 !important;
        opacity: 0.55;
        pointer-events: none;
        user-select: none;
        filter: grayscale(1);
    }

    .m-notes-box.required-active {
        background: #FFFBEB;
        border: 2px solid #D97706;
        box-shadow: 0 4px 12px rgba(217, 119, 6, 0.14);
    }

    .m-repair-banner {
        display: none;
        margin: 0 20px 10px;
        padding: 10px 12px;
        border-radius: 10px;
        background: #FEF3C7;
        border: 1px solid #F59E0B;
        color: #92400E;
        font-size: 11px;
        font-weight: 600;
        line-height: 1.45;
    }

    .m-repair-banner.show {
        display: block;
    }

    /* Daftar Kartu Item Pemeriksaan (Abu-abu dengan bayangan seperti Figma) */
    .m-items-container {
        padding: 4px 18px 24px;
        display: flex;
        flex-direction: column;
        gap: 14px;
    }

    .m-group-divider {
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #4B5563;
        margin-top: 6px;
        padding-left: 4px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .m-item-card {
        background: linear-gradient(180deg, #EBECEE 0%, #D4D6DA 100%);
        border-radius: 14px;
        padding: 13px 15px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.16);
        position: relative;
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
        margin-bottom: 12px;
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
        max-width: 240px;
    }

    .m-item-alert-icon {
        width: 22px;
        height: 22px;
        border-radius: 50%;
        border: 1.5px solid #111827;
        color: #111827;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        font-weight: 800;
        flex-shrink: 0;
        cursor: pointer;
        background: transparent;
    }

    .m-item-card.status-aman .m-item-alert-icon {
        border-color: #166534;
        background: #22C55E;
        color: #FFFFFF;
    }

    .m-item-card.status-kendala .m-item-alert-icon {
        border-color: #991B1B;
        background: #DC2626;
        color: #FFFFFF;
    }

    /* Baris Tombol di Bawah Kartu: "Check Keadaan" + Tombol Kamera */
    .m-item-footer {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .m-btn-check-keadaan {
        background: #FFFFFF;
        color: #1F2937;
        border: 1px solid #D1D5DB;
        border-radius: 6px;
        padding: 5px 11px;
        font-size: 10px;
        font-weight: 600;
        font-family: inherit;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        cursor: pointer;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        transition: all 0.15s;
    }

    .m-btn-camera {
        background: #FFFFFF;
        color: #1F2937;
        border: 1px solid #D1D5DB;
        border-radius: 6px;
        padding: 5px 9px;
        font-size: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        position: relative;
    }

    .m-btn-camera.has-photo {
        background: #0B2E8A;
        color: #FFFFFF;
        border-color: #0B2E8A;
    }

    /* Opsi Pop-up Inline Pilihan Keadaan (Aman / Ada Kendala) */
    .m-condition-selector {
        display: none;
        margin-top: 10px;
        padding-top: 10px;
        border-top: 1px dashed rgba(0,0,0,0.15);
        grid-template-columns: 1fr 1fr;
        gap: 8px;
    }

    .m-condition-selector.open {
        display: grid;
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
        transition: all 0.15s;
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

    .m-photo-preview {
        display: none;
        margin-top: 8px;
        align-items: center;
        gap: 8px;
        font-size: 10.5px;
        color: #1E40AF;
        font-weight: 600;
    }

    .m-photo-preview img {
        width: 38px;
        height: 38px;
        border-radius: 6px;
        object-fit: cover;
        border: 1px solid #93C5FD;
    }

    /* Catatan Tambahan & Tombol Submit Bawah */
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

    /* Modal / Bottom Sheet Panduan (?) */
    .m-sheet-backdrop {
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.5);
        z-index: 110;
        display: none;
        align-items: flex-end;
        justify-content: center;
    }
    .m-sheet-backdrop.open {
        display: flex;
    }
    .m-sheet-box {
        width: 100%;
        max-width: 420px;
        background: #FFFFFF;
        border-radius: 22px 22px 0 0;
        padding: 22px 20px 28px;
        animation: slideUp 0.25s ease;
    }
    @keyframes slideUp {
        from { transform: translateY(100%); }
        to   { transform: translateY(0); }
    }
</style>
@endpush

@section('content')

<form action="{{ route('driver.checklist.store', [$vehicle->id, $type]) }}"
      method="POST"
      enctype="multipart/form-data"
      id="mobileChecklistForm">
    @csrf

    {{-- Hero Kendaraan + Tombol Submit Hijau + Plat Nomor (Persis Desain Figma) --}}
    <div class="m-check-hero">
        <div class="m-check-truck">
            @include('driver.partials.truck-illust', ['vehicle' => $vehicle, 'size' => 'lg'])
        </div>

        <div class="m-check-actions">
            <button type="submit" class="m-btn-submit-green">
                Submit
            </button>

            <div class="m-plat-pill">
                {{ strtoupper($vehicle->plat_nomor) }}
            </div>
        </div>
    </div>

    {{-- Bar Progres & Tombol Mode --}}
    <div class="m-progress-strip">
        <span id="progressCounterWrap">Terisi: <strong id="checkedCounter">0</strong> / {{ $items->count() }} komponen</span>

        @if($type === 'fisik')
            <input type="hidden" name="dalam_perbaikan" id="dalamPerbaikanInput" value="{{ old('dalam_perbaikan', '0') }}">
            <button type="button" class="m-btn-repair-mode" id="btnDalamPerbaikan" onclick="toggleDalamPerbaikan()">
                <i class="fa-solid fa-screwdriver-wrench"></i>
                <span id="btnDalamPerbaikanText">Dalam Perbaikan</span>
            </button>
        @else
            <button type="button" class="m-btn-check-all" onclick="markAllItemsGood()">
                <i class="fa-solid fa-check-double"></i> Tandai Semua Aman
            </button>
        @endif
    </div>

    @if($type === 'fisik')
    <div class="m-repair-banner" id="repairBannerInfo">
        <i class="fa-solid fa-lock" style="margin-right:4px"></i>
        <strong>Mode Dalam Perbaikan Aktif:</strong> Seluruh komponen pemeriksaan dikunci. Wajib mengisi kolom <strong>Catatan Keterangan</strong> di bagian bawah sebelum menekan tombol Submit.
    </div>
    @endif

    {{-- Daftar Kartu Komponen Pemeriksaan sesuai Figma --}}
    <div class="m-items-container">
        @forelse($groups as $group)
            @if($groups->count() > 1)
                <div class="m-group-divider">
                    <i class="fa-solid fa-layer-group" style="font-size:10px;color:#0B2E8A"></i>
                    {{ $group->nama }}
                </div>
            @endif

            @foreach($group->children as $item)
                @php
                    $oldVal = old("items.{$item->id}", '1'); // Default '1' (Aman) atau bisa diubah
                @endphp
                <div class="m-item-card" id="itemCard_{{ $item->id }}" data-item-id="{{ $item->id }}">
                    {{-- Input Hidden Nilai Kondisi (1 = Aman/Baik, 0 = Kendala/Rusak) --}}
                    <input type="hidden" name="items[{{ $item->id }}]" id="itemInput_{{ $item->id }}" value="">

                    <div class="m-item-header">
                        <div>
                            <div class="m-item-title">{{ $item->nama }}</div>
                            <div class="m-item-criteria">
                                {{ $item->criteria ?? 'Dalam keadaan bersih dan berfungsi normal' }}
                            </div>
                        </div>

                        <button type="button"
                                class="m-item-alert-icon"
                                id="itemBadge_{{ $item->id }}"
                                onclick="toggleSelector({{ $item->id }})"
                                title="Status Pemeriksaan">
                            !
                        </button>
                    </div>

                    <div class="m-item-footer">
                        <button type="button"
                                class="m-btn-check-keadaan"
                                id="btnLabel_{{ $item->id }}"
                                onclick="toggleSelector({{ $item->id }})">
                            <i class="fa-solid fa-clipboard-check"></i>
                            <span id="btnText_{{ $item->id }}">Check Keadaan</span>
                        </button>

                        <label class="m-btn-camera" id="cameraLabel_{{ $item->id }}" title="Ambil / Upload Foto Bukti">
                            <i class="fa-solid fa-camera"></i>
                            <input type="file"
                                   name="foto[{{ $item->id }}]"
                                   accept="image/*"
                                   capture="environment"
                                   style="display:none"
                                   onchange="previewItemPhoto({{ $item->id }}, this)">
                        </label>
                    </div>

                    {{-- Panel Pilihan Kondisi Saat "Check Keadaan" Diklik --}}
                    <div class="m-condition-selector" id="selector_{{ $item->id }}">
                        <button type="button"
                                class="m-choice-btn aman"
                                id="choiceAman_{{ $item->id }}"
                                onclick="setItemCondition({{ $item->id }}, '1')">
                            <i class="fa-solid fa-circle-check"></i>
                            Aman / Sesuai
                        </button>

                        <button type="button"
                                class="m-choice-btn kendala"
                                id="choiceKendala_{{ $item->id }}"
                                onclick="setItemCondition({{ $item->id }}, '0')">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                            Ada Kendala
                        </button>
                    </div>

                    {{-- Preview Foto Jika Diupload --}}
                    <div class="m-photo-preview" id="photoPreview_{{ $item->id }}">
                        <img src="" alt="Preview" id="photoImg_{{ $item->id }}">
                        <span id="photoName_{{ $item->id }}">Foto terlampir</span>
                    </div>
                </div>
            @endforeach
        @empty
            <div style="text-align:center;padding:36px 20px;color:#6B7280;font-size:13px">
                Belum ada parameter pemeriksaan aktif untuk kategori ini.
            </div>
        @endforelse
    </div>

    {{-- Catatan Tambahan --}}
    @if($items->count() > 0)
    <div class="m-notes-box" id="notesBoxWrap">
        <label id="notesLabelText" style="display:block;font-size:12px;font-weight:700;color:#111827;margin-bottom:6px">
            <i class="fa-regular fa-comment-dots" style="margin-right:4px;color:#0B2E8A"></i>
            <span id="notesLabelSpan">Catatan Tambahan (Opsional)</span>
        </label>
        <textarea name="catatan"
                  id="catatanTextarea"
                  class="m-notes-textarea"
                  placeholder="Tuliskan keterangan detail jika ditemukan kerusakan atau catatan kebersihan...">{{ old('catatan') }}</textarea>
        <div id="notesRequiredError" style="display:none;color:#DC2626;font-size:11px;font-weight:700;margin-top:6px;">
            <i class="fa-solid fa-circle-exclamation"></i> Kolom catatan keterangan wajib diisi saat kendaraan dalam perbaikan!
        </div>

        <button type="submit" class="m-btn-submit-green" style="width:100%;margin-top:12px;padding:12px">
            <i class="fa-solid fa-paper-plane" style="margin-right:6px"></i>
            Submit Laporan {{ $type === 'k3' ? 'K3 & Kebersihan' : 'Fisik Kendaraan' }}
        </button>
    </div>
    @endif

</form>

{{-- Bottom Sheet Panduan (?) --}}
<div class="m-sheet-backdrop" id="helpSheet" onclick="closeHelpSheet(event)">
    <div class="m-sheet-box" onclick="event.stopPropagation()">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
            <div style="font-size:15px;font-weight:800;color:#111827">
                <i class="fa-solid fa-circle-info" style="color:#0B2E8A;margin-right:6px"></i>
                Panduan Pengisian Checklist
            </div>
            <button type="button" onclick="closeHelpSheet()" style="background:none;border:none;font-size:18px;cursor:pointer">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <div style="font-size:12.5px;color:#4B5563;line-height:1.6">
            <p style="margin-bottom:8px">1. Tekan tombol <strong>Check Keadaan</strong> pada setiap kartu komponen kendaraan.</p>
            <p style="margin-bottom:8px">2. Pilih <strong>Aman / Sesuai</strong> jika kondisi baik, atau <strong>Ada Kendala</strong> jika ditemukan masalah/kerusakan.</p>
            @if($type === 'fisik')
            <p style="margin-bottom:8px">3. Jika kendaraan sedang rusak/diservis dan tidak bisa diperiksa per item, tekan tombol <strong>Dalam Perbaikan</strong> lalu isi <strong>Catatan Keterangan (Wajib)</strong>.</p>
            @endif
            <p>4. Setelah selesai, tekan tombol hijau <strong>Submit</strong> untuk mengirim laporan.</p>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function isRepairModeActive() {
        var inp = document.getElementById('dalamPerbaikanInput');
        return inp && inp.value === '1';
    }

    function toggleSelector(itemId) {
        if (isRepairModeActive()) return;
        var el = document.getElementById('selector_' + itemId);
        if (!el) return;
        el.classList.toggle('open');
    }

    function setItemCondition(itemId, value) {
        if (isRepairModeActive()) return;
        var input   = document.getElementById('itemInput_' + itemId);
        var card    = document.getElementById('itemCard_' + itemId);
        var btnText = document.getElementById('btnText_' + itemId);
        var badge   = document.getElementById('itemBadge_' + itemId);
        var cAman   = document.getElementById('choiceAman_' + itemId);
        var cKendala= document.getElementById('choiceKendala_' + itemId);

        input.value = value;

        card.classList.remove('status-aman', 'status-kendala');
        cAman.classList.remove('selected');
        cKendala.classList.remove('selected');

        if (value === '1') {
            card.classList.add('status-aman');
            cAman.classList.add('selected');
            btnText.textContent = 'Kondisi: Aman';
            badge.innerHTML = '<i class="fa-solid fa-check" style="font-size:10px"></i>';
        } else {
            card.classList.add('status-kendala');
            cKendala.classList.add('selected');
            btnText.textContent = 'Kondisi: Kendala';
            badge.innerHTML = '!';
        }

        updateCheckedCounter();
    }

    function markAllItemsGood() {
        var cards = document.querySelectorAll('.m-item-card[data-item-id]');
        cards.forEach(function (card) {
            var id = card.getAttribute('data-item-id');
            setItemCondition(id, '1');
        });
    }

    function toggleDalamPerbaikan() {
        var inp         = document.getElementById('dalamPerbaikanInput');
        var btn         = document.getElementById('btnDalamPerbaikan');
        var btnText     = document.getElementById('btnDalamPerbaikanText');
        var banner      = document.getElementById('repairBannerInfo');
        var notesBox    = document.getElementById('notesBoxWrap');
        var notesSpan   = document.getElementById('notesLabelSpan');
        var textarea    = document.getElementById('catatanTextarea');
        var errBox      = document.getElementById('notesRequiredError');
        var cards       = document.querySelectorAll('.m-item-card[data-item-id]');

        if (!inp) return;

        var willActivate = inp.value !== '1';
        inp.value = willActivate ? '1' : '0';

        if (willActivate) {
            btn?.classList.add('active');
            if (btnText) btnText.textContent = 'Batal Mode Perbaikan';
            banner?.classList.add('show');

            // Kunci & kosongkan seluruh komponen pemeriksaan menjadi abu-abu
            cards.forEach(function (card) {
                var id = card.getAttribute('data-item-id');
                card.classList.remove('status-aman', 'status-kendala');
                card.classList.add('locked-repair');

                var itemInp = document.getElementById('itemInput_' + id);
                if (itemInp) itemInp.value = '';

                var cAman = document.getElementById('choiceAman_' + id);
                var cKend = document.getElementById('choiceKendala_' + id);
                cAman?.classList.remove('selected');
                cKend?.classList.remove('selected');

                var sel = document.getElementById('selector_' + id);
                sel?.classList.remove('open');

                var bText = document.getElementById('btnText_' + id);
                if (bText) bText.textContent = 'Terkunci (Dalam Perbaikan)';

                var badge = document.getElementById('itemBadge_' + id);
                if (badge) badge.innerHTML = '<i class="fa-solid fa-lock" style="font-size:9px"></i>';
            });

            // Jadikan catatan wajib diisi
            notesBox?.classList.add('required-active');
            if (notesSpan) {
                notesSpan.innerHTML = 'Catatan Keterangan Perbaikan <span style="color:#DC2626">(Wajib Diisi *)</span>';
            }
            if (textarea) {
                textarea.setAttribute('required', 'required');
                textarea.placeholder = 'WAJIB DIISI: Tuliskan keterangan kerusakan / alasan kendaraan sedang dalam perbaikan...';
                setTimeout(function () {
                    notesBox?.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    textarea.focus();
                }, 150);
            }
        } else {
            btn?.classList.remove('active');
            if (btnText) btnText.textContent = 'Dalam Perbaikan';
            banner?.classList.remove('show');

            cards.forEach(function (card) {
                var id = card.getAttribute('data-item-id');
                card.classList.remove('locked-repair');

                var bText = document.getElementById('btnText_' + id);
                if (bText) bText.textContent = 'Check Keadaan';

                var badge = document.getElementById('itemBadge_' + id);
                if (badge) badge.innerHTML = '!';
            });

            notesBox?.classList.remove('required-active');
            if (notesSpan) {
                notesSpan.textContent = 'Catatan Tambahan (Opsional)';
            }
            if (textarea) {
                textarea.removeAttribute('required');
                textarea.placeholder = 'Tuliskan keterangan detail jika ditemukan kerusakan atau catatan kebersihan...';
            }
            if (errBox) errBox.style.display = 'none';
        }

        updateCheckedCounter();
    }

    function updateCheckedCounter() {
        var inputs = document.querySelectorAll('input[id^="itemInput_"]');
        var filled = 0;
        inputs.forEach(function (inp) {
            if (inp.value === '1' || inp.value === '0') filled++;
        });
        var counterEl = document.getElementById('checkedCounter');
        if (counterEl) counterEl.textContent = filled;
    }

    function previewItemPhoto(itemId, fileInput) {
        if (isRepairModeActive()) return;
        var previewWrap = document.getElementById('photoPreview_' + itemId);
        var previewImg  = document.getElementById('photoImg_' + itemId);
        var previewName = document.getElementById('photoName_' + itemId);
        var camLabel    = document.getElementById('cameraLabel_' + itemId);

        if (fileInput.files && fileInput.files[0]) {
            var file = fileInput.files[0];
            var reader = new FileReader();
            reader.onload = function (e) {
                previewImg.src = e.target.result;
                previewName.textContent = file.name;
                previewWrap.style.display = 'flex';
                camLabel.classList.add('has-photo');
            };
            reader.readAsDataURL(file);
        }
    }

    function openHelpSheet() {
        document.getElementById('helpSheet').classList.add('open');
    }

    function closeHelpSheet() {
        document.getElementById('helpSheet').classList.remove('open');
    }

    // Validasi sebelum submit
    document.getElementById('mobileChecklistForm')?.addEventListener('submit', function (e) {
        if (isRepairModeActive()) {
            var textarea = document.getElementById('catatanTextarea');
            var errBox   = document.getElementById('notesRequiredError');
            var val      = (textarea ? textarea.value : '').trim();

            if (val === '') {
                e.preventDefault();
                if (errBox) errBox.style.display = 'block';
                document.getElementById('notesBoxWrap')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
                textarea?.focus();
                return;
            }
            return;
        }

        var inputs = document.querySelectorAll('input[id^="itemInput_"]');
        var emptyIds = [];
        inputs.forEach(function (inp) {
            if (inp.value !== '1' && inp.value !== '0') {
                emptyIds.push(inp.id.replace('itemInput_', ''));
            }
        });

        if (emptyIds.length > 0) {
            e.preventDefault();
            if (confirm('Masih ada ' + emptyIds.length + ' komponen yang belum dipilih keadaannya. Tandai komponen sisanya sebagai "Aman / Sesuai" dan kirim sekarang?')) {
                emptyIds.forEach(function (id) {
                    setItemCondition(id, '1');
                });
                this.submit();
            } else {
                var firstCard = document.getElementById('itemCard_' + emptyIds[0]);
                if (firstCard) {
                    firstCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    document.getElementById('selector_' + emptyIds[0])?.classList.add('open');
                }
            }
        }
    });
</script>
@endpush


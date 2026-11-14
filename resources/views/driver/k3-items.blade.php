@extends('driver.layout')

@section('title', 'Edit Data K3 — TOYACHECK Mobile')
@section('back_url', route('driver.index'))

@push('styles')
<style>
    /* Tab Header "Edit Data K3" sesuai Figma Node 1190:5486 */
    .m-k3-tab-bar {
        padding: 18px 20px 14px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .m-k3-tab-pill {
        width: 100%;
        background: #071843;
        color: #FFFFFF;
        font-size: 13px;
        font-weight: 600;
        text-align: center;
        padding: 9px 16px;
        border-radius: 7px;
        box-shadow: 0 3px 8px rgba(7, 24, 67, 0.22);
        letter-spacing: 0.01em;
    }

    /* Daftar Kartu Parameter K3 sesuai Figma (Bordered White Card dengan garis pemisah tengah) */
    .m-k3-list {
        padding: 2px 20px 90px;
        display: flex;
        flex-direction: column;
        gap: 14px;
    }

    .m-k3-card {
        background: #FFFFFF;
        border: 1.5px solid #374151;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.06);
    }

    .m-k3-row-top {
        padding: 9px 12px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        border-bottom: 1px solid #9CA3AF;
        background: #FFFFFF;
    }

    .m-k3-item-name {
        font-size: 12px;
        font-weight: 600;
        color: #111827;
        line-height: 1.35;
        flex: 1;
    }

    .m-k3-row-bottom {
        padding: 10px 12px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        background: #FFFFFF;
    }

    .m-k3-item-criteria {
        font-size: 11px;
        color: #1F2937;
        line-height: 1.4;
        flex: 1;
    }

    .m-k3-icon-btn {
        width: 28px;
        height: 28px;
        border: none;
        background: transparent;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        border-radius: 6px;
        flex-shrink: 0;
        transition: background 0.15s, transform 0.15s;
    }

    .m-k3-icon-btn:hover {
        background: #F3F4F6;
    }

    .m-k3-icon-btn:active {
        transform: scale(0.92);
    }

    .m-k3-icon-btn.edit {
        color: #002046;
        font-size: 14px;
    }

    .m-k3-icon-btn.delete {
        color: #BA1A1A;
        font-size: 14px;
    }

    /* Tombol Floating "+ Data" Ungu di kanan bawah sesuai Figma (#4318D1) */
    .m-fab-wrap {
        position: sticky;
        bottom: 68px;
        z-index: 35;
        display: flex;
        justify-content: flex-end;
        padding: 0 20px 12px;
        pointer-events: none;
    }

    .m-fab-add {
        pointer-events: auto;
        background: #4318D1;
        color: #FFFFFF;
        border: none;
        border-radius: 999px;
        padding: 9px 18px 9px 14px;
        font-size: 13px;
        font-weight: 700;
        font-family: inherit;
        display: inline-flex;
        align-items: center;
        gap: 7px;
        cursor: pointer;
        box-shadow: 0 6px 16px rgba(67, 24, 209, 0.38);
        transition: transform 0.15s, background 0.15s;
    }

    .m-fab-add:hover {
        background: #3512A8;
    }

    .m-fab-add:active {
        transform: scale(0.95);
    }

    .m-fab-plus-circle {
        width: 18px;
        height: 18px;
        border-radius: 50%;
        border: 1.8px solid #FFFFFF;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 10px;
    }

    /* Bottom Sheet Modal untuk Tambah & Edit Data K3 */
    .m-modal-backdrop {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.55);
        z-index: 120;
        display: none;
        align-items: flex-end;
        justify-content: center;
    }

    .m-modal-backdrop.open {
        display: flex;
    }

    .m-modal-sheet {
        width: 100%;
        max-width: 420px;
        background: #FFFFFF;
        border-radius: 22px 22px 0 0;
        padding: 22px 20px 28px;
        box-shadow: 0 -8px 30px rgba(0, 0, 0, 0.25);
        animation: k3SheetUp 0.24s ease;
    }

    @keyframes k3SheetUp {
        from { transform: translateY(100%); }
        to   { transform: translateY(0); }
    }

    .m-form-group {
        margin-bottom: 14px;
    }

    .m-form-label {
        display: block;
        font-size: 12px;
        font-weight: 700;
        color: #111827;
        margin-bottom: 6px;
    }

    .m-form-input,
    .m-form-textarea {
        width: 100%;
        border: 1.5px solid #D1D5DB;
        border-radius: 10px;
        padding: 10px 12px;
        font-size: 12.5px;
        font-family: inherit;
        color: #111827;
        outline: none;
        transition: border-color 0.15s;
    }

    .m-form-input:focus,
    .m-form-textarea:focus {
        border-color: #071843;
    }

    .m-form-textarea {
        min-height: 76px;
        resize: vertical;
    }

    .m-btn-primary {
        width: 100%;
        background: #071843;
        color: #FFFFFF;
        border: none;
        border-radius: 10px;
        padding: 11px 16px;
        font-size: 13px;
        font-weight: 700;
        font-family: inherit;
        cursor: pointer;
    }
</style>
@endpush

@section('content')

{{-- Tab Header Khusus K3 sesuai Figma (Hanya Edit Data K3) --}}
<div class="m-k3-tab-bar">
    <div class="m-k3-tab-pill">
        Edit Data K3
    </div>
</div>

{{-- Daftar Kartu Item Pemeriksaan K3 sesuai Figma Node 1190:5486 --}}
<div class="m-k3-list">
    @forelse($k3Items as $item)
        <div class="m-k3-card">
            {{-- Baris Atas: Judul Komponen + Ikon Pensil (Edit) --}}
            <div class="m-k3-row-top">
                <div class="m-k3-item-name">{{ $item->nama }}</div>
                <button type="button"
                        class="m-k3-icon-btn edit"
                        title="Edit Item K3"
                        data-id="{{ $item->id }}"
                        data-nama="{{ $item->nama }}"
                        data-criteria="{{ $item->criteria }}"
                        onclick="openEditK3Modal(this)">
                    <i class="fa-solid fa-pen"></i>
                </button>
            </div>

            {{-- Baris Bawah: Standar / Kriteria Kebersihan + Ikon Tempat Sampah Merah (Hapus) --}}
            <div class="m-k3-row-bottom">
                <div class="m-k3-item-criteria">
                    {{ $item->criteria ?: 'Tidak ada standar kriteria tambahan' }}
                </div>

                <form action="{{ route('driver.k3-items.destroy', $item->id) }}"
                      method="POST"
                      onsubmit="return confirm('Hapus item pemeriksaan K3 &quot;{{ addslashes($item->nama) }}&quot;?');"
                      style="margin:0">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="m-k3-icon-btn delete" title="Hapus Item K3">
                        <i class="fa-solid fa-trash-can"></i>
                    </button>
                </form>
            </div>
        </div>
    @empty
        <div style="text-align:center;padding:48px 20px;color:#6B7280;font-size:13px">
            <i class="fa-solid fa-clipboard-list" style="font-size:32px;margin-bottom:10px;opacity:0.4;display:block"></i>
            Belum ada item pemeriksaan K3 & Kebersihan. Klik tombol <strong>+ Data</strong> di bawah untuk menambahkan.
        </div>
    @endforelse
</div>

{{-- Tombol Floating "+ Data" di kanan bawah sesuai Figma --}}
<div class="m-fab-wrap">
    <button type="button" class="m-fab-add" onclick="openAddK3Modal()">
        <span class="m-fab-plus-circle">
            <i class="fa-solid fa-plus"></i>
        </span>
        <span>Data</span>
    </button>
</div>

{{-- Modal Tambah Data K3 --}}
<div class="m-modal-backdrop" id="addK3Modal" onclick="closeAddK3Modal()">
    <div class="m-modal-sheet" onclick="event.stopPropagation()">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
            <div style="font-size:15px;font-weight:800;color:#111827">
                <i class="fa-solid fa-plus-circle" style="color:#4318D1;margin-right:6px"></i>
                Tambah Item Pemeriksaan K3
            </div>
            <button type="button" onclick="closeAddK3Modal()" style="background:none;border:none;font-size:18px;cursor:pointer;color:#6B7280">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form action="{{ route('driver.k3-items.store') }}" method="POST">
            @csrf
            <div class="m-form-group">
                <label class="m-form-label">Nama Bagian / Item K3</label>
                <input type="text"
                       name="nama"
                       class="m-form-input"
                       placeholder="Contoh: Bagian luar / Bak Kendaraan"
                       required>
            </div>

            <div class="m-form-group">
                <label class="m-form-label">Kriteria / Standar Kebersihan K3</label>
                <textarea name="criteria"
                          class="m-form-textarea"
                          placeholder="Contoh: Kondisi bersih dari kotoran (debu, lumpur dan oli)"
                          required></textarea>
            </div>

            <button type="submit" class="m-btn-primary">
                <i class="fa-solid fa-floppy-disk" style="margin-right:6px"></i>
                Simpan Data K3
            </button>
        </form>
    </div>
</div>

{{-- Modal Edit Data K3 --}}
<div class="m-modal-backdrop" id="editK3Modal" onclick="closeEditK3Modal()">
    <div class="m-modal-sheet" onclick="event.stopPropagation()">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
            <div style="font-size:15px;font-weight:800;color:#111827">
                <i class="fa-solid fa-pen-to-square" style="color:#071843;margin-right:6px"></i>
                Edit Item Pemeriksaan K3
            </div>
            <button type="button" onclick="closeEditK3Modal()" style="background:none;border:none;font-size:18px;cursor:pointer;color:#6B7280">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form id="editK3Form" method="POST">
            @csrf
            @method('PUT')
            <div class="m-form-group">
                <label class="m-form-label">Nama Bagian / Item K3</label>
                <input type="text"
                       name="nama"
                       id="editK3Nama"
                       class="m-form-input"
                       required>
            </div>

            <div class="m-form-group">
                <label class="m-form-label">Kriteria / Standar Kebersihan K3</label>
                <textarea name="criteria"
                          id="editK3Criteria"
                          class="m-form-textarea"
                          required></textarea>
            </div>

            <button type="submit" class="m-btn-primary">
                <i class="fa-solid fa-check" style="margin-right:6px"></i>
                Perbarui Data K3
            </button>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function openAddK3Modal() {
        document.getElementById('addK3Modal').classList.add('open');
    }

    function closeAddK3Modal() {
        document.getElementById('addK3Modal').classList.remove('open');
    }

    function openEditK3Modal(btn) {
        var id       = btn.getAttribute('data-id');
        var nama     = btn.getAttribute('data-nama') || '';
        var criteria = btn.getAttribute('data-criteria') || '';

        var form = document.getElementById('editK3Form');
        form.action = "{{ url('driver/k3-items') }}/" + id;

        document.getElementById('editK3Nama').value = nama;
        document.getElementById('editK3Criteria').value = criteria;

        document.getElementById('editK3Modal').classList.add('open');
    }

    function closeEditK3Modal() {
        document.getElementById('editK3Modal').classList.remove('open');
    }
</script>
@endpush


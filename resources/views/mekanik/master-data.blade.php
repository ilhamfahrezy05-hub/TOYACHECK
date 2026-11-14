@extends('layouts.app')

@section('title', 'Master Data Bengkel')
@section('page-title', $tab === 'all' ? 'Master Data: Aset Mekanik' : 'Master Data: Inventaris & Toolkit')
@section('page-subtitle', 'Pusat kontrol distribusi suku cadang, pelacakan toolkit mekanik, dan perlengkapan pendukung armada')

@section('page-actions')
    <button type="button" class="btn btn-primary" style="background:#0F2647" onclick="openAddMasterModal()">
        <i class="fa-solid fa-plus"></i>
        Log baru
    </button>
@endsection

@push('styles')
<style>
    .mk-md-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
        gap: 16px;
        flex-wrap: wrap;
    }

    .mk-md-tabs {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .mk-md-tab {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 18px;
        border-radius: 8px;
        background: #DEE6F2;
        color: #091E3A;
        font-size: 13px;
        font-weight: 700;
        text-decoration: none;
        transition: all 0.18s;
    }

    .mk-md-tab.active {
        background: #0F2647;
        color: #FFFFFF;
        box-shadow: 0 4px 12px rgba(9, 30, 58, 0.16);
    }

    .mk-catalog-box {
        background: #EEF2F8;
        border-radius: 16px;
        padding: 18px 18px 24px;
    }

    .mk-catalog-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 6px 12px 16px;
    }

    .mk-catalog-title {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 13.5px;
        font-weight: 800;
        color: #091E3A;
    }

    .mk-catalog-count {
        background: #FFFFFF;
        padding: 4px 12px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 700;
        color: #475569;
    }

    .mk-kat-pill {
        display: inline-block;
        padding: 3px 11px;
        border-radius: 999px;
        background: #DEE6F2;
        color: #1E3A8A;
        font-size: 11px;
        font-weight: 700;
    }
</style>
@endpush

@section('content')

{{-- ── FILTER TABS & SEARCH ── --}}
<div class="mk-md-toolbar">
    <div class="mk-md-tabs">
        <a href="{{ route('mekanik.master-data', ['tab' => 'all', 'q' => $search]) }}"
           class="mk-md-tab {{ $tab === 'all' ? 'active' : '' }}">
            <i class="fa-solid fa-gears"></i>
            All ({{ $totalCount }})
        </a>

        <a href="{{ route('mekanik.master-data', ['tab' => 'toolkit', 'q' => $search]) }}"
           class="mk-md-tab {{ $tab === 'toolkit' ? 'active' : '' }}">
            <i class="fa-solid fa-screwdriver-wrench"></i>
            Toolkit & Peralatan
        </a>

        <a href="{{ route('mekanik.master-data', ['tab' => 'support', 'q' => $search]) }}"
           class="mk-md-tab {{ $tab === 'support' ? 'active' : '' }}">
            <i class="fa-solid fa-boxes-packing"></i>
            Support Items
        </a>
    </div>

    <form method="GET" action="{{ route('mekanik.master-data') }}" style="position:relative;width:280px">
        <input type="hidden" name="tab" value="{{ $tab }}">
        <i class="fa-solid fa-magnifying-glass" style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#94A3B8;font-size:13px"></i>
        <input type="text"
               name="q"
               value="{{ $search }}"
               class="form-control"
               style="padding-left:38px;border-radius:999px"
               placeholder="Search Master Data...">
    </form>
</div>

{{-- ── CATALOG TABLE BOX ── --}}
@php
    $catalogLabel = match($tab) {
        'toolkit' => 'Active Toolkit & Peralatan',
        'support' => 'Active Support Catalog',
        default   => 'Active Spareparts & Asset Catalog',
    };
@endphp

<div class="mk-catalog-box">
    <div class="mk-catalog-head">
        <div class="mk-catalog-title">
            @if($tab === 'toolkit')
                <i class="fa-solid fa-screwdriver-wrench"></i>
            @else
                <i class="fa-regular fa-clipboard"></i>
            @endif
            {{ $catalogLabel }}
        </div>
        <div class="mk-catalog-count">
            Displaying {{ $items->count() }} of {{ $totalCount }} items
        </div>
    </div>

    <div class="card" style="border-radius:12px">
        <div class="card-body-p0">
            <div class="table-wrap">
                <table class="tc-table">
                    <thead>
                        <tr>
                            <th>ITEM ID</th>
                            <th>NAMA ITEM</th>
                            <th style="text-align:center">KATEGORI</th>
                            <th style="text-align:center">STOK</th>
                            <th>KETERANGAN</th>
                            <th style="text-align:center">AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                            @php
                                $badgeLabel = match($item->tipe_item) {
                                    'toolkit' => 'Tool Kit',
                                    'support' => 'Support',
                                    default   => 'Spare Part',
                                };
                            @endphp
                            <tr>
                                <td style="font-weight:800;color:#091E3A;font-family:monospace">
                                    {{ $item->kode_sparepart }}
                                </td>
                                <td style="font-weight:700;color:#0F172A">
                                    {{ $item->nama_sparepart }}
                                    <div style="font-size:11px;color:var(--text-muted)">Lokasi: {{ $item->lokasi ?? 'Warehouse Alpha' }}</div>
                                </td>
                                <td style="text-align:center">
                                    <span class="mk-kat-pill">{{ $badgeLabel }}</span>
                                </td>
                                <td style="text-align:center;font-weight:700">
                                    {{ $item->stok }} {{ $item->satuan }}
                                </td>
                                <td style="color:#64748B">
                                    {{ $item->keterangan ?: 'Standard operational component' }}
                                </td>
                                <td style="text-align:center;white-space:nowrap">
                                    <div class="d-flex gap-8" style="justify-content:center">
                                        <button type="button"
                                                class="btn btn-secondary btn-sm btn-icon"
                                                title="Edit Item"
                                                onclick="openEditMasterModal({{ json_encode($item) }})">
                                            <i class="fa-solid fa-pen"></i>
                                        </button>

                                        <form action="{{ route('mekanik.master-data.destroy', $item->id) }}"
                                              method="POST"
                                              style="display:inline"
                                              onsubmit="return confirm('Hapus item {{ addslashes($item->nama_sparepart) }} dari database?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm btn-icon" title="Hapus Item">
                                                <i class="fa-regular fa-trash-can"></i>
                                            </button>
                                        </form>

                                        <button type="button"
                                                class="btn btn-primary btn-sm btn-icon"
                                                style="background:#0F2647"
                                                title="Purchase Request"
                                                onclick="openPurchaseModal({{ json_encode($item) }})">
                                            <i class="fa-solid fa-cart-shopping"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <div class="empty-state">
                                        <div class="empty-icon"><i class="fa-solid fa-database"></i></div>
                                        <div class="empty-title">Belum Ada Item</div>
                                        <div class="empty-text">Klik tombol "+ Log baru" untuk menambahkan item ke database.</div>
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

@endsection

@section('modals')
{{-- ── 1. MODAL TAMBAH / EDIT ITEM ── --}}
<div class="modal-overlay" id="modalMasterItem">
    <div class="modal-box">
        <div class="modal-header" style="background:#DEE6F2">
            <div class="modal-title" id="mdModalTitle">Edit item</div>
            <button type="button" class="modal-close" onclick="closeModal('modalMasterItem')">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form id="formMasterItem" method="POST" action="{{ route('mekanik.master-data.store') }}">
            @csrf
            <input type="hidden" name="_method" id="mdFormMethod" value="POST">

            <div class="modal-body">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
                    <div class="form-group">
                        <label class="form-label">ITEM ID <span class="required">*</span></label>
                        <input type="text" name="kode_sparepart" id="md_kode" class="form-control" placeholder="SP-XXXXX / TK-XXXXX" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">NAMA ITEM <span class="required">*</span></label>
                        <input type="text" name="nama_sparepart" id="md_nama" class="form-control" placeholder="Masukkan nama item" required>
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
                    <div class="form-group">
                        <label class="form-label">KATEGORI</label>
                        <select name="tipe_item" id="md_tipe" class="form-control">
                            <option value="sparepart">Sparepart</option>
                            <option value="toolkit">Toolkit & Peralatan</option>
                            <option value="support">Support Items</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">SATUAN <span class="required">*</span></label>
                        <input type="text" name="satuan" id="md_satuan" class="form-control" placeholder="Pcs / Unit / Set" value="Unit" required>
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:14px">
                    <div class="form-group">
                        <label class="form-label">STOK AWAL <span class="required">*</span></label>
                        <input type="number" name="stok" id="md_stok" class="form-control" value="0" min="0" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">BATAS MINIMAL</label>
                        <input type="number" name="minimal_stok" id="md_min" class="form-control" value="3" min="0">
                    </div>
                    <div class="form-group">
                        <label class="form-label">KLASIFIKASI ABC</label>
                        <select name="kategori_abc" id="md_abc" class="form-control">
                            <option value="A">Kategori A</option>
                            <option value="B" selected>Kategori B</option>
                            <option value="C">Kategori C</option>
                        </select>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom:0">
                    <label class="form-label">KETERANGAN</label>
                    <textarea name="keterangan" id="md_ket" rows="3" class="form-control" placeholder="Deskripsi detail item..."></textarea>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalMasterItem')">Batal</button>
                <button type="submit" class="btn btn-primary" style="background:#0F2647">Simpan Data</button>
            </div>
        </form>
    </div>
</div>

{{-- ── 2. MODAL PURCHASE REQUEST (FIGMA PEMBELIAN MATCH) ── --}}
<div class="modal-overlay" id="modalPurchaseRequest">
    <div class="modal-box" style="max-width:460px">
        <div style="background:#0F2647;color:#FFFFFF;padding:22px 24px;position:relative">
            <div style="font-size:10px;font-weight:800;letter-spacing:0.08em;color:#93C5FD;text-transform:uppercase;margin-bottom:4px">
                <i class="fa-solid fa-cart-shopping" style="margin-right:4px"></i>
                NEW PROCUREMENT
            </div>
            <h3 style="font-size:20px;font-weight:800;color:#FFFFFF;margin:0">Purchase Request</h3>
            <button type="button" onclick="closeModal('modalPurchaseRequest')"
                    style="position:absolute;top:18px;right:18px;background:none;border:none;color:#94A3B8;font-size:15px;cursor:pointer">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form method="POST" action="{{ route('mekanik.inventory.request.store') }}">
            @csrf
            <input type="hidden" name="sparepart_id" id="pr_sparepart_id">
            <input type="hidden" name="nama_sparepart" id="pr_nama_input">
            <input type="hidden" name="priority_level" value="normal">

            <div class="modal-body">
                <div style="background:#F4F6FA;border-radius:10px;padding:14px;display:flex;align-items:center;gap:14px;margin-bottom:18px">
                    <div style="width:52px;height:52px;border-radius:8px;background:#1E293B;color:#FFFFFF;display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0">
                        <i class="fa-solid fa-gears"></i>
                    </div>
                    <div style="flex:1;min-width:0">
                        <div style="display:flex;align-items:center;justify-content:space-between;gap:8px">
                            <div id="pr_title" style="font-size:14px;font-weight:800;color:#091E3A">Item</div>
                            <span id="pr_badge" class="badge badge-neutral" style="font-size:9px">TOOL KIT</span>
                        </div>
                        <div id="pr_code" style="font-size:11px;color:#64748B;margin-bottom:6px">PART ID: PM-001</div>
                        <div style="display:flex;gap:18px;font-size:10.5px">
                            <div>
                                <span style="color:#94A3B8;display:block;font-weight:700">IN STOCK</span>
                                <strong id="pr_stock" style="color:#0F172A">0 Units</strong>
                            </div>
                            <div>
                                <span style="color:#94A3B8;display:block;font-weight:700">LOCATION</span>
                                <strong id="pr_loc" style="color:#0F172A">Warehouse Alpha</strong>
                            </div>
                        </div>
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
                    <div class="form-group">
                        <label class="form-label">QUANTITY REQUESTED <span class="required">*</span></label>
                        <input type="number" name="qty" min="1" value="1" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">EXPECTED DELIVERY DATE</label>
                        <input type="date" name="expected_delivery_date" value="{{ now()->addDays(3)->format('Y-m-d') }}" class="form-control">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">VENDOR TUJUAN (DATABASE)</label>
                    <select name="vendor_name" class="form-control">
                        @forelse($vendors as $vd)
                            <option value="{{ $vd->nama_vendor }}">{{ $vd->nama_vendor }}</option>
                        @empty
                            <option value="PT. Nusantara Perkasa">PT. Nusantara Perkasa</option>
                        @endforelse
                    </select>
                </div>

                <div class="form-group" style="margin-bottom:0">
                    <label class="form-label">PURPOSE / REASON FOR PURCHASE <span class="required">*</span></label>
                    <textarea name="alasan" rows="3" class="form-control" required
                              placeholder="Identify the project or specific fleet unit requiring this component..."></textarea>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalPurchaseRequest')">Cancel</button>
                <button type="submit" class="btn btn-primary" style="background:#0F2647">
                    SUBMIT REQUEST <i class="fa-regular fa-paper-plane"></i>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function openAddMasterModal() {
        document.getElementById('mdModalTitle').textContent = 'Tambah Item Baru';
        document.getElementById('formMasterItem').action = '{{ route('mekanik.master-data.store') }}';
        document.getElementById('mdFormMethod').value = 'POST';
        document.getElementById('md_kode').value   = '';
        document.getElementById('md_nama').value   = '';
        document.getElementById('md_tipe').value   = '{{ $tab === "toolkit" ? "toolkit" : ($tab === "support" ? "support" : "sparepart") }}';
        document.getElementById('md_satuan').value = 'Unit';
        document.getElementById('md_stok').value   = '1';
        document.getElementById('md_min').value    = '3';
        document.getElementById('md_abc').value    = 'B';
        document.getElementById('md_ket').value    = '';
        openModal('modalMasterItem');
    }

    function openEditMasterModal(item) {
        document.getElementById('mdModalTitle').textContent = 'Edit item';
        document.getElementById('formMasterItem').action = '/mekanik/master-data/' + item.id;
        document.getElementById('mdFormMethod').value = 'PUT';
        document.getElementById('md_kode').value   = item.kode_sparepart || '';
        document.getElementById('md_nama').value   = item.nama_sparepart || '';
        document.getElementById('md_tipe').value   = item.tipe_item || 'sparepart';
        document.getElementById('md_satuan').value = item.satuan || 'Unit';
        document.getElementById('md_stok').value   = item.stok || 0;
        document.getElementById('md_min').value    = item.minimal_stok || 3;
        document.getElementById('md_abc').value    = (item.kategori_abc || 'B').toUpperCase();
        document.getElementById('md_ket').value    = item.keterangan || '';
        openModal('modalMasterItem');
    }

    function openPurchaseModal(item) {
        document.getElementById('pr_sparepart_id').value = item.id;
        document.getElementById('pr_nama_input').value   = item.nama_sparepart;
        document.getElementById('pr_title').textContent  = item.nama_sparepart;
        document.getElementById('pr_code').textContent   = 'PART ID: ' + (item.kode_sparepart || 'PM-001');
        document.getElementById('pr_stock').textContent  = (item.stok || 0) + ' ' + (item.satuan || 'Units');
        document.getElementById('pr_loc').textContent    = item.lokasi || 'Warehouse Alpha';
        document.getElementById('pr_badge').textContent  = (item.kategori || 'SPAREPART').toUpperCase();
        openModal('modalPurchaseRequest');
    }
</script>
@endpush

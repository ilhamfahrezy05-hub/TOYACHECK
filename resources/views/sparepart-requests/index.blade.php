@extends('layouts.app')

@section('title', 'Permintaan Suku Cadang')
@section('page-title', 'Permintaan Suku Cadang')
@section('page-subtitle', 'Tinjau, edit, dan ajukan pengajuan suku cadang dari mekanik ke tim pengadaan')

@section('page-actions')
    @role('admin')
        <button type="button" class="btn btn-primary" onclick="openModal('modalCreateSparepartReq')">
            <i class="fa-solid fa-paper-plane"></i>
            Ajukan ke Pengadaan
        </button>
    @endrole
@endsection

@section('content')

{{-- ── STAT CARDS ── --}}
<div class="stats-grid" style="grid-template-columns: repeat(4, 1fr); margin-bottom: 24px;">

    <div class="stat-card">
        <div class="stat-icon blue"><i class="fa-solid fa-inbox"></i></div>
        <div class="stat-info">
            <div class="stat-label">Total Permintaan</div>
            <div class="stat-value">{{ $stats['total'] }}</div>
            <div class="stat-change">Semua pengajuan</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon amber"><i class="fa-solid fa-clock"></i></div>
        <div class="stat-info">
            <div class="stat-label">Menunggu</div>
            <div class="stat-value">{{ $stats['pending'] }}</div>
            <div class="stat-change">Perlu keputusan</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon green"><i class="fa-solid fa-circle-check"></i></div>
        <div class="stat-info">
            <div class="stat-label">Disetujui</div>
            <div class="stat-value">{{ $stats['approved'] }}</div>
            <div class="stat-change">Lanjut ke pengadaan</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon red"><i class="fa-solid fa-circle-xmark"></i></div>
        <div class="stat-info">
            <div class="stat-label">Ditolak</div>
            <div class="stat-value">{{ $stats['rejected'] }}</div>
            <div class="stat-change">Tidak disetujui</div>
        </div>
    </div>

</div>

@php
    $viewMode = request('view', 'cards');
@endphp

{{-- ── HEADER KONTROL VIEW & FILTER ── --}}
<div class="card" style="margin-bottom: 20px;">
    <div class="card-header" style="flex-wrap: wrap; gap: 12px;">
        <div>
            <div class="card-title">Daftar Pengajuan Suku Cadang</div>
            <div class="card-subtitle">Total {{ $requests->total() }} pengajuan tercatat di sistem</div>
        </div>

        <div class="d-flex align-center gap-12" style="margin-left: auto;">
            {{-- Filter Status --}}
            <form method="GET" action="{{ route('sparepart-requests.index') }}" class="d-flex align-center gap-8">
                <input type="hidden" name="view" value="{{ $viewMode }}">
                <select name="status" class="form-control" style="width:160px;height:36px;font-size:13px" onchange="this.form.submit()">
                    <option value="">Semua Status</option>
                    <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Menunggu (Pending)</option>
                    <option value="approved" {{ $status === 'approved' ? 'selected' : '' }}>Disetujui</option>
                    <option value="rejected" {{ $status === 'rejected' ? 'selected' : '' }}>Ditolak</option>
                </select>
            </form>

            {{-- Toggle View (Figma Cards / Tabel) --}}
            <div class="d-flex align-center" style="background:#F1F5F9;border-radius:8px;padding:3px;border:1px solid var(--border)">
                <a href="{{ request()->fullUrlWithQuery(['view' => 'cards']) }}"
                   class="btn btn-sm {{ $viewMode !== 'table' ? 'btn-primary' : 'btn-secondary' }}"
                   style="height:30px;padding:0 12px;font-size:12px;box-shadow:none"
                   title="Tampilan Kartu Figma">
                    <i class="fa-solid fa-table-cells-large"></i> Kartu
                </a>
                <a href="{{ request()->fullUrlWithQuery(['view' => 'table']) }}"
                   class="btn btn-sm {{ $viewMode === 'table' ? 'btn-primary' : 'btn-secondary' }}"
                   style="height:30px;padding:0 12px;font-size:12px;box-shadow:none"
                   title="Tampilan Tabel Data">
                    <i class="fa-solid fa-table-list"></i> Tabel
                </a>
            </div>
        </div>
    </div>
</div>

@if($viewMode !== 'table')
    {{-- ── 1. TAMPILAN FEED KARTU FIGMA (FIGMA DESIGN MATCH) ── --}}
    <div style="display:flex;flex-direction:column;gap:18px;margin-bottom:24px">
        @forelse($requests as $req)
        @php
            $isPending  = $req->isPending();
            $isApproved = $req->status === 'approved';
            $isRejected = $req->status === 'rejected';
            $urgensi    = $req->qty >= 5 ? 'A - Penting' : 'B - Rutinitas';
            $kodePM     = 'PM-' . str_pad($req->id, 7, '0', STR_PAD_LEFT);
        @endphp
        <div class="card" style="border:1px solid var(--border);box-shadow:0 2px 10px rgba(15,30,54,0.04);transition:transform .15s">
            {{-- Header Kartu --}}
            <div style="display:flex;justify-content:space-between;align-items:center;padding:14px 22px;border-bottom:1px solid var(--border);background:#FAFCFF;font-size:13.5px">
                <div>
                    <span style="font-weight:700;color:var(--text-primary)">
                        {{ $req->requester->name ?? 'Mekanik' }}
                    </span>
                    <span style="color:var(--text-secondary)">telah membuat pengajuan permintaan</span>
                    @if($req->maintenance)
                        <span style="color:var(--text-muted)">untuk unit <strong>{{ $req->maintenance->vehicle->plat_nomor ?? 'Armada' }}</strong></span>
                    @endif
                </div>
                <div style="font-size:12px;color:var(--text-muted);display:flex;align-items:center;gap:6px">
                    <i class="fa-regular fa-clock"></i>
                    {{ $req->created_at->diffForHumans() }}
                </div>
            </div>

            {{-- Body Kartu --}}
            <div style="padding:22px;display:flex;flex-wrap:wrap;gap:20px;align-items:flex-start">
                {{-- Foto / Ikon Sparepart --}}
                <div style="width:96px;height:96px;border-radius:12px;background:#F1F5F9;border:1px solid var(--border);display:flex;align-items:center;justify-content:center;flex-shrink:0;color:var(--primary);font-size:36px">
                    <i class="fa-solid fa-gears"></i>
                </div>

                {{-- Deskripsi Permintaan --}}
                <div style="flex:1;min-width:260px">
                    <div style="display:flex;align-items:center;gap:10px;margin-bottom:4px;flex-wrap:wrap">
                        <h3 style="font-size:17px;font-weight:800;color:var(--text-primary);margin:0">
                            {{ $req->nama_sparepart }}
                        </h3>
                        <span class="badge" style="border:1.5px solid var(--primary);color:var(--primary);background:var(--primary-subtle);font-size:11px;font-weight:700;padding:2px 8px">
                            {{ $urgensi }}
                        </span>
                        @if($isApproved)
                            <span class="badge badge-success"><i class="fa-solid fa-circle-check"></i> Disetujui</span>
                        @elseif($isRejected)
                            <span class="badge badge-danger"><i class="fa-solid fa-circle-xmark"></i> Ditolak</span>
                        @else
                            <span class="badge badge-warning"><i class="fa-solid fa-clock"></i> Menunggu Keputusan</span>
                        @endif
                    </div>

                    <div style="font-family:monospace;font-size:12px;color:var(--text-muted);margin-bottom:8px">
                        {{ $kodePM }}
                    </div>

                    <div style="font-size:12.5px;color:var(--text-secondary);line-height:1.5">
                        <span style="font-weight:600;color:var(--text-primary)">Tujuan Permintaan:</span><br>
                        {{ $req->alasan ?: 'Dilakukan pergantian bagian terkait untuk memastikan kelaikan unit armada operasional distribusi.' }}
                    </div>

                    @if($req->catatan_admin)
                        <div style="margin-top:8px;padding:8px 12px;background:#FEF2F2;border-left:3px solid var(--danger);border-radius:6px;font-size:12px;color:#991B1B">
                            <strong>Catatan Admin:</strong> {{ $req->catatan_admin }}
                        </div>
                    @endif
                </div>

                {{-- Kotak Rincian Abu-Abu (Figma Right Box) --}}
                <div style="background:#F8FAFC;border:1px solid var(--border);border-radius:12px;padding:16px 20px;min-width:240px;display:flex;flex-direction:column;gap:10px">
                    <div style="display:flex;justify-content:space-between;align-items:center;font-size:12.5px">
                        <span style="color:var(--text-secondary)"><i class="fa-solid fa-box-open" style="margin-right:6px"></i> Stok Sisa</span>
                        <strong style="color:var(--text-primary);font-size:14px">{{ $req->sparepart->stok ?? 2 }}</strong>
                    </div>
                    <div style="display:flex;justify-content:space-between;align-items:center;font-size:12.5px">
                        <span style="color:var(--text-secondary)"><i class="fa-solid fa-cart-shopping" style="margin-right:6px"></i> Jumlah Permintaan</span>
                        <strong style="color:var(--primary);font-size:16px">{{ $req->qty }}</strong>
                    </div>
                    <div style="border-top:1px dashed var(--border);padding-top:8px;display:flex;justify-content:space-between;align-items:center;font-size:12px">
                        <span style="color:var(--text-muted)">Ekspektasi Pengiriman</span>
                        <span style="font-weight:600;color:var(--text-primary)">{{ ($req->expected_delivery_date ?? $req->created_at->addDays(2))->translatedFormat('d M Y') }}</span>
                    </div>
                </div>
            </div>

            {{-- Footer Kartu (Aksi Edit, Tolak & Terima) --}}
            @hasanyrole('admin|pengadaan')
            <div style="padding:14px 22px;border-top:1px solid var(--border);background:#F8FAFC;display:flex;justify-content:flex-end;align-items:center;gap:12px;flex-wrap:wrap">
                @role('admin')
                    <button type="button" class="btn btn-warning"
                            data-id="{{ $req->id }}"
                            data-sparepart-id="{{ $req->sparepart_id ?? '' }}"
                            data-nama="{{ $req->nama_sparepart }}"
                            data-qty="{{ $req->qty }}"
                            data-maintenance-id="{{ $req->maintenance_id ?? '' }}"
                            data-priority="{{ $req->priority_level ?? 'Normal' }}"
                            data-delivery="{{ $req->expected_delivery_date?->format('Y-m-d') ?? '' }}"
                            data-vendor="{{ $req->vendor_name ?? '' }}"
                            data-alasan="{{ $req->alasan ?? '' }}"
                            data-catatan-admin="{{ $req->catatan_admin ?? '' }}"
                            onclick="openEditRequestModal(this)">
                        <i class="fa-solid fa-pen-to-square"></i> Edit Permintaan
                    </button>
                @endrole

                @if($isPending)
                    <button type="button" class="btn btn-secondary" style="border:1px solid var(--border);font-weight:600"
                            onclick="openRejectModal({{ $req->id }}, {{ \Illuminate\Support\Js::from($req->nama_sparepart) }})">
                        <i class="fa-solid fa-xmark"></i> Tolak
                    </button>

                    <form method="POST" action="{{ route('sparepart-requests.approve', $req->id) }}" style="display:inline">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-primary" style="background:#0F1E36;border-color:#0F1E36;font-weight:700;padding:9px 24px">
                            <i class="fa-solid fa-check"></i> Terima
                        </button>
                    </form>
                @else
                    <div style="font-size:12px;color:var(--text-muted)">
                        Ditinjau oleh <strong>{{ $req->reviewer->name ?? 'Admin' }}</strong> pada {{ $req->reviewed_at?->translatedFormat('d M Y H:i') }} WIB
                    </div>
                @endif
            </div>
            @endhasanyrole
        </div>
        @empty
        <div class="card" style="padding:48px 24px;text-align:center">
            <div class="empty-state">
                <div class="empty-icon"><i class="fa-solid fa-inbox"></i></div>
                <div class="empty-title">Belum Ada Pengajuan Suku Cadang</div>
                <div class="empty-text">Pengajuan yang diajukan oleh mekanik atau admin akan otomatis tampil di sini.</div>
            </div>
        </div>
        @endforelse
    </div>
@else
    {{-- ── 2. TAMPILAN TABEL DATA TRADISIONAL ── --}}
    <div class="card">
        <div class="card-body-p0">
            <div class="table-wrap">
                <table class="tc-table">
                    <thead>
                        <tr>
                            <th style="width:52px">#</th>
                            <th>Tanggal</th>
                            <th>Suku Cadang</th>
                            <th>Diajukan Oleh</th>
                            <th>Untuk Perbaikan</th>
                            <th>Status</th>
                            @hasanyrole('admin|pengadaan')
                            <th style="width:165px;text-align:center">Aksi</th>
                            @endhasanyrole
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($requests as $index => $req)
                        @php
                            $meta = match($req->status) {
                                'approved' => ['badge-success', 'fa-circle-check', 'Disetujui'],
                                'rejected' => ['badge-danger', 'fa-circle-xmark', 'Ditolak'],
                                default    => ['badge-warning', 'fa-clock', 'Menunggu'],
                            };
                        @endphp
                        <tr>
                            <td style="color:var(--text-muted);font-size:12px">{{ $requests->firstItem() + $index }}</td>
                            <td style="white-space:nowrap">
                                <div style="font-weight:600;font-size:13px">{{ $req->created_at->translatedFormat('d M Y') }}</div>
                                <div style="font-size:11px;color:var(--text-muted)">{{ $req->created_at->format('H:i') }} WIB</div>
                            </td>
                            <td style="max-width:260px">
                                <div style="font-weight:600">{{ $req->nama_sparepart }}
                                    <span class="badge badge-neutral" style="font-size:11px">x{{ $req->qty }}</span>
                                </div>
                                @if($req->alasan)
                                    <div style="font-size:11px;color:var(--text-muted)">{{ \Illuminate\Support\Str::limit($req->alasan, 60) }}</div>
                                @endif
                            </td>
                            <td style="font-weight:500">{{ $req->requester->name ?? '—' }}</td>
                            <td>
                                @if($req->maintenance)
                                    <a href="{{ route('maintenances.show', $req->maintenance_id) }}" style="font-weight:600;font-family:monospace;color:var(--primary);text-decoration:none">
                                        {{ $req->maintenance->vehicle->kode_armada ?? '#' . $req->maintenance_id }}
                                    </a>
                                @else
                                    <span style="color:var(--text-muted)">—</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $meta[0] }}"><i class="fa-solid {{ $meta[1] }}"></i> {{ $meta[2] }}</span>
                                @if($req->catatan_admin)
                                    <div style="font-size:11px;color:var(--text-secondary);margin-top:4px;max-width:200px">
                                        <i class="fa-regular fa-comment"></i> {{ $req->catatan_admin }}
                                    </div>
                                @endif
                            </td>
                            @hasanyrole('admin|pengadaan')
                            <td>
                                <div class="d-flex gap-6" style="justify-content:center">
                                    @role('admin')
                                        <button type="button" class="btn btn-warning btn-sm btn-icon" title="Edit Permintaan"
                                                data-id="{{ $req->id }}"
                                                data-sparepart-id="{{ $req->sparepart_id ?? '' }}"
                                                data-nama="{{ $req->nama_sparepart }}"
                                                data-qty="{{ $req->qty }}"
                                                data-maintenance-id="{{ $req->maintenance_id ?? '' }}"
                                                data-priority="{{ $req->priority_level ?? 'Normal' }}"
                                                data-delivery="{{ $req->expected_delivery_date?->format('Y-m-d') ?? '' }}"
                                                data-vendor="{{ $req->vendor_name ?? '' }}"
                                                data-alasan="{{ $req->alasan ?? '' }}"
                                                data-catatan-admin="{{ $req->catatan_admin ?? '' }}"
                                                onclick="openEditRequestModal(this)">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>
                                    @endrole
                                    @if($req->isPending())
                                        <form method="POST" action="{{ route('sparepart-requests.approve', $req->id) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-primary btn-sm btn-icon" title="Setujui">
                                                <i class="fa-solid fa-check"></i>
                                            </button>
                                        </form>
                                        <button class="btn btn-danger btn-sm btn-icon" title="Tolak"
                                                onclick="openRejectModal({{ $req->id }}, {{ \Illuminate\Support\Js::from($req->nama_sparepart) }})">
                                            <i class="fa-solid fa-xmark"></i>
                                        </button>
                                        @role('admin')
                                        <form method="POST" action="{{ route('sparepart-requests.destroy', $req->id) }}"
                                              onsubmit="return confirm('Hapus permintaan ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-secondary btn-sm btn-icon" title="Hapus">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                        @endrole
                                    @else
                                        <span style="font-size:11px;color:var(--text-muted)">
                                            {{ $req->reviewer->name ?? '' }}<br>
                                            {{ $req->reviewed_at?->format('d/m H:i') }}
                                        </span>
                                    @endif
                                </div>
                            </td>
                            @endhasanyrole
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">
                                    <div class="empty-icon"><i class="fa-solid fa-inbox"></i></div>
                                    <div class="empty-title">Belum Ada Permintaan</div>
                                    <div class="empty-text">Pengajuan suku cadang akan muncul di sini.</div>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endif

@if($requests->hasPages())
<div class="card" style="margin-top: 16px;">
    <div class="card-footer">
        {{ $requests->links('pagination::bootstrap-4') }}
    </div>
</div>
@endif

@endsection

@section('modals')

{{-- Modal Tolak (admin & pengadaan) --}}
@hasanyrole('admin|pengadaan')
<div class="modal-overlay" id="modalRejectRequest">
    <div class="modal-box" style="max-width:460px">
        <div class="modal-header">
            <div class="modal-title" style="color:var(--danger)">
                <i class="fa-solid fa-circle-xmark" style="margin-right:8px"></i>
                Tolak Permintaan
            </div>
            <button class="modal-close" type="button" onclick="closeModal('modalRejectRequest')">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form id="formRejectRequest" method="POST" action="">
            @csrf
            @method('PATCH')
            <div class="modal-body">
                <p style="margin-bottom:14px;font-size:13px">
                    Tolak permintaan <strong id="rj_name"></strong>? Beri alasan agar pengaju paham.
                </p>
                <div class="form-group" style="margin-bottom:0">
                    <label class="form-label" for="rj_catatan">Alasan Penolakan <span class="required">*</span></label>
                    <textarea id="rj_catatan" name="catatan_admin" rows="3" class="form-control" required></textarea>
                    @error('catatan_admin')
                        <div class="invalid-feedback"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalRejectRequest')">Batal</button>
                <button type="submit" class="btn btn-danger">Tolak Permintaan</button>
            </div>
        </form>
    </div>
</div>
@endhasanyrole

@role('admin')
{{-- Modal Ajukan Mandiri ke Pengadaan --}}
<div class="modal-overlay" id="modalCreateSparepartReq">
    <div class="modal-box" style="max-width:560px">
        <div class="modal-header">
            <div class="modal-title">
                <i class="fa-solid fa-paper-plane" style="color:var(--primary);margin-right:8px"></i>
                Ajukan Suku Cadang ke Pengadaan
            </div>
            <button class="modal-close" type="button" onclick="closeModal('modalCreateSparepartReq')">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form method="POST" action="{{ route('sparepart-requests.store') }}">
            @csrf
            <div class="modal-body" style="max-height:72vh;overflow-y:auto;">
                <div class="form-group">
                    <label class="form-label" for="csr_maintenance_id">Terkait Catatan Perbaikan (Opsional)</label>
                    <select id="csr_maintenance_id" name="maintenance_id" class="form-control">
                        <option value="">-- Pengajuan Umum / Stok Gudang --</option>
                        @foreach($maintenances as $mOpt)
                            <option value="{{ $mOpt->id }}">
                                #{{ $mOpt->id }} &bull; {{ $mOpt->vehicle->kode_armada ?? '-' }} ({{ $mOpt->vehicle->plat_nomor ?? '-' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="csr_sparepart_id">Pilih dari Katalog Sparepart</label>
                    <select id="csr_sparepart_id" name="sparepart_id" class="form-control" onchange="syncReqSparepartName('csr_')">
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
                        <label class="form-label" for="csr_nama_sparepart">Nama Sparepart <span class="required">*</span></label>
                        <input type="text" id="csr_nama_sparepart" name="nama_sparepart" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="csr_qty">Qty <span class="required">*</span></label>
                        <input type="number" id="csr_qty" name="qty" min="1" value="1" class="form-control" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="csr_priority">Prioritas</label>
                        <select id="csr_priority" name="priority_level" class="form-control">
                            <option value="Normal" selected>Normal</option>
                            <option value="Rendah">Rendah</option>
                            <option value="Tinggi">Tinggi</option>
                            <option value="Urgent">Urgent</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="csr_delivery">Target Estimasi Tiba</label>
                        <input type="date" id="csr_delivery" name="expected_delivery_date" class="form-control">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="csr_vendor">Rekomendasi Vendor</label>
                    <select id="csr_vendor" name="vendor_name" class="form-control">
                        <option value="">-- Biarkan Pengadaan Menentukan --</option>
                        @foreach($vendors as $v)
                            <option value="{{ $v->nama_vendor }}">{{ $v->nama_vendor }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group" style="margin-bottom:0">
                    <label class="form-label" for="csr_alasan">Alasan / Kebutuhan</label>
                    <textarea id="csr_alasan" name="alasan" rows="2" class="form-control"></textarea>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalCreateSparepartReq')">Batal</button>
                <button type="submit" class="btn btn-primary">Kirim ke Pengadaan</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Edit Permintaan Suku Cadang --}}
<div class="modal-overlay" id="modalEditSparepartReq">
    <div class="modal-box" style="max-width:560px">
        <div class="modal-header">
            <div class="modal-title">
                <i class="fa-solid fa-pen-to-square" style="color:var(--warning);margin-right:8px"></i>
                Edit Permintaan Suku Cadang
            </div>
            <button class="modal-close" type="button" onclick="closeModal('modalEditSparepartReq')">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form id="formEditSparepartReq" method="POST" action="">
            @csrf
            @method('PUT')
            <div class="modal-body" style="max-height:72vh;overflow-y:auto;">
                <div class="form-group">
                    <label class="form-label" for="esr_maintenance_id">Terkait Catatan Perbaikan (Opsional)</label>
                    <select id="esr_maintenance_id" name="maintenance_id" class="form-control">
                        <option value="">-- Pengajuan Umum / Stok Gudang --</option>
                        @foreach($maintenances as $mOpt)
                            <option value="{{ $mOpt->id }}">
                                #{{ $mOpt->id }} &bull; {{ $mOpt->vehicle->kode_armada ?? '-' }} ({{ $mOpt->vehicle->plat_nomor ?? '-' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="esr_sparepart_id">Pilih dari Katalog Sparepart</label>
                    <select id="esr_sparepart_id" name="sparepart_id" class="form-control" onchange="syncReqSparepartName('esr_')">
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
                        <label class="form-label" for="esr_nama_sparepart">Nama Sparepart <span class="required">*</span></label>
                        <input type="text" id="esr_nama_sparepart" name="nama_sparepart" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="esr_qty">Qty <span class="required">*</span></label>
                        <input type="number" id="esr_qty" name="qty" min="1" class="form-control" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="esr_priority">Prioritas</label>
                        <select id="esr_priority" name="priority_level" class="form-control">
                            <option value="Rendah">Rendah</option>
                            <option value="Normal">Normal</option>
                            <option value="Tinggi">Tinggi</option>
                            <option value="Urgent">Urgent</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="esr_delivery">Target Estimasi Tiba</label>
                        <input type="date" id="esr_delivery" name="expected_delivery_date" class="form-control">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="esr_vendor">Vendor</label>
                    <select id="esr_vendor" name="vendor_name" class="form-control">
                        <option value="">-- Belum Ditentukan --</option>
                        @foreach($vendors as $v)
                            <option value="{{ $v->nama_vendor }}">{{ $v->nama_vendor }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="esr_alasan">Alasan / Keterangan</label>
                    <textarea id="esr_alasan" name="alasan" rows="2" class="form-control"></textarea>
                </div>

                <div class="form-group" style="margin-bottom:0">
                    <label class="form-label" for="esr_catatan_admin">Catatan Admin</label>
                    <textarea id="esr_catatan_admin" name="catatan_admin" rows="2" class="form-control"></textarea>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalEditSparepartReq')">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
@endrole

@endsection

@push('scripts')
<script>
(function () {
    'use strict';

    window.openRejectModal = function (id, name) {
        document.getElementById('formRejectRequest').action = '/sparepart-requests/' + id + '/reject';
        document.getElementById('rj_name').textContent = name;
        document.getElementById('rj_catatan').value = '';
        openModal('modalRejectRequest');
    };

    window.syncReqSparepartName = function (prefix) {
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

    window.openEditRequestModal = function (btn) {
        var id = btn.getAttribute('data-id');
        document.getElementById('formEditSparepartReq').action = '/sparepart-requests/' + id;
        document.getElementById('esr_maintenance_id').value = btn.getAttribute('data-maintenance-id') || '';
        document.getElementById('esr_sparepart_id').value = btn.getAttribute('data-sparepart-id') || '';
        document.getElementById('esr_nama_sparepart').value = btn.getAttribute('data-nama') || '';
        document.getElementById('esr_qty').value = btn.getAttribute('data-qty') || 1;
        document.getElementById('esr_priority').value = btn.getAttribute('data-priority') || 'Normal';
        document.getElementById('esr_delivery').value = btn.getAttribute('data-delivery') || '';
        document.getElementById('esr_vendor').value = btn.getAttribute('data-vendor') || '';
        document.getElementById('esr_alasan').value = btn.getAttribute('data-alasan') || '';
        document.getElementById('esr_catatan_admin').value = btn.getAttribute('data-catatan-admin') || '';
        syncReqSparepartName('esr_');
        openModal('modalEditSparepartReq');
    };
})();
</script>
@endpush

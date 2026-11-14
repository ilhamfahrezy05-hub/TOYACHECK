@extends('layouts.app')

@section('title', 'Rekap Log Perbaikan')
@section('page-title', 'Rekap Log Perbaikan Armada')
@section('page-subtitle', 'Sistem Manajemen Perawatan Armada Mandiri — Tersinkronisasi dengan Database')

@section('page-actions')
    <div class="d-flex align-center gap-8">
        <a href="{{ route('mekanik.repairs.export-pdf', ['q' => $search, 'month' => $month]) }}" target="_blank" class="btn btn-secondary">
            <i class="fa-solid fa-file-pdf" style="color:#DC2626"></i>
            Export PDF
        </a>
        <a href="{{ route('mekanik.repairs.create') }}" class="btn btn-primary" style="background:#0F2647">
            <i class="fa-solid fa-circle-plus"></i>
            Tambah Log
        </a>
    </div>
@endsection

@push('styles')
<style>
    /* ── 3 KPI CARDS (FIGMA REPAIR LOG) ── */
    .mk-rep-kpis {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
        margin-bottom: 24px;
    }

    .mk-rep-kpi {
        background: #FFFFFF;
        border: 1px solid var(--border);
        border-radius: 14px;
        padding: 20px 22px;
        display: flex;
        align-items: center;
        gap: 16px;
        box-shadow: 0 2px 8px rgba(15, 30, 54, 0.03);
    }

    .mk-rep-kpi-icon {
        width: 46px;
        height: 46px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
    }

    .mk-rep-kpi-icon.navy   { background: #E8EEF5; color: #091E3A; }
    .mk-rep-kpi-icon.orange { background: #FFF7ED; color: #EA580C; }
    .mk-rep-kpi-icon.blue   { background: #EFF6FF; color: #1E3A8A; }

    .mk-rep-kpi-label {
        font-size: 10.5px;
        font-weight: 800;
        letter-spacing: 0.06em;
        color: #64748B;
        text-transform: uppercase;
        margin-bottom: 4px;
    }

    .mk-rep-kpi-val {
        font-size: 26px;
        font-weight: 800;
        color: #091E3A;
        line-height: 1;
    }

    .mk-rep-kpi-val.orange { color: #EA580C; }

    .mk-rep-kpi-unit {
        font-size: 12px;
        font-weight: 600;
        color: #94A3B8;
        margin-left: 4px;
    }

    @media (max-width: 900px) {
        .mk-rep-kpis { grid-template-columns: 1fr; }
    }
</style>
@endpush

@section('content')

{{-- ── 3 KPI CARDS (DARI DATABASE) ── --}}
<div class="mk-rep-kpis">
    <div class="mk-rep-kpi">
        <div class="mk-rep-kpi-icon navy">
            <i class="fa-regular fa-calendar-check"></i>
        </div>
        <div>
            <div class="mk-rep-kpi-label">TOTAL PERBAIKAN PERIODE INI</div>
            <div class="mk-rep-kpi-val">
                {{ $totalBulanIni }}<span class="mk-rep-kpi-unit">Catatan</span>
            </div>
        </div>
    </div>

    <div class="mk-rep-kpi">
        <div class="mk-rep-kpi-icon orange">
            <i class="fa-solid fa-user-gear"></i>
        </div>
        <div>
            <div class="mk-rep-kpi-label">UNIT DALAM PERBAIKAN / RUSAK</div>
            <div class="mk-rep-kpi-val orange">
                {{ $unitDalamPerbaikan }}<span class="mk-rep-kpi-unit" style="color:#FB923C">Aktif</span>
            </div>
        </div>
    </div>

    <div class="mk-rep-kpi">
        <div class="mk-rep-kpi-icon blue">
            <i class="fa-solid fa-truck"></i>
        </div>
        <div>
            <div class="mk-rep-kpi-label">UNIT BERJALAN (SIAP JALAN)</div>
            <div class="mk-rep-kpi-val">
                {{ $unitBerjalan }}/{{ max($totalArmada, 1) }}<span class="mk-rep-kpi-unit">Unit</span>
            </div>
        </div>
    </div>
</div>

{{-- ── TABLE REKAP LOG PERBAIKAN ── --}}
<div class="card">
    <div class="card-header" style="flex-wrap:wrap;gap:12px">
        <div>
            <div class="card-title">Daftar Rekap Log Perbaikan</div>
            <div class="card-subtitle">Total {{ $maintenances->total() }} log perbaikan tercatat di database</div>
        </div>

        <form method="GET" action="{{ route('mekanik.repairs') }}" class="d-flex align-center gap-8" style="margin-left:auto;flex-wrap:wrap">
            <div style="position:relative">
                <i class="fa-solid fa-magnifying-glass" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#94A3B8;font-size:12px"></i>
                <input type="text"
                       name="q"
                       value="{{ $search }}"
                       class="form-control"
                       style="padding-left:34px;width:230px"
                       placeholder="Cari plat, driver, kendala...">
            </div>

            <select name="month" class="form-control" style="width:170px" onchange="this.form.submit()">
                <option value="">Semua Periode</option>
                @foreach($availableMonths as $ym)
                    @php
                        $labelYm = \Carbon\Carbon::createFromFormat('Y-m', $ym)->translatedFormat('F Y');
                    @endphp
                    <option value="{{ $ym }}" {{ $month === $ym ? 'selected' : '' }}>{{ $labelYm }}</option>
                @endforeach
            </select>

            @if($search || $month)
                <a href="{{ route('mekanik.repairs') }}" class="btn btn-secondary btn-sm">Reset</a>
            @endif
        </form>
    </div>

    <div class="card-body-p0">
        <div class="table-wrap">
            <table class="tc-table">
                <thead>
                    <tr>
                        <th>TANGGAL</th>
                        <th>ARMADA (PLAT NO)</th>
                        <th>DRIVER / MEKANIK</th>
                        <th>PART / SUKU CADANG</th>
                        <th>KENDALA</th>
                        <th>SOLUSI</th>
                        <th>STATUS</th>
                        <th style="text-align:center">ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($maintenances as $item)
                        @php
                            $partsList = $item->details->map(fn ($d) => $d->nama_sparepart . ' (' . $d->qty . ')')->implode(', ');
                            $driverName = $item->nama_driver ?: ($item->mekanik->name ?? '—');
                            $statusBadge = match($item->status_maintenance) {
                                'completed'   => ['badge-success', 'Selesai'],
                                'in_progress' => ['badge-info',    'Diperbaiki'],
                                default       => ['badge-warning', 'Pending'],
                            };
                        @endphp
                        <tr>
                            <td style="white-space:nowrap">
                                <div style="font-weight:700;color:#0F172A">{{ $item->tanggal_maintenance->translatedFormat('d M Y') }}</div>
                                <div style="font-size:11px;color:var(--text-muted)">{{ $item->estimasi_downtime ?? '—' }}</div>
                            </td>
                            <td>
                                <span style="background:#091E3A;color:#fff;padding:3px 9px;border-radius:4px;font-size:11.5px;font-weight:800;font-family:monospace">
                                    {{ strtoupper($item->vehicle->plat_nomor ?? '—') }}
                                </span>
                                <div style="font-size:11px;color:var(--text-muted);margin-top:2px">{{ $item->vehicle->jenis_kendaraan ?? '' }}</div>
                            </td>
                            <td style="font-weight:600;color:#334155">
                                {{ $driverName }}
                                <div style="font-size:11px;color:var(--text-muted)">Mekanik: {{ $item->mekanik->name ?? '—' }}</div>
                            </td>
                            <td style="font-weight:600;color:#1E293B;max-width:190px">
                                {{ $partsList ?: '—' }}
                            </td>
                            <td style="max-width:180px">
                                <div style="font-weight:600;color:#0F172A">{{ $item->deskripsi_masalah }}</div>
                                @if($item->kategori_kendala)
                                    <span class="badge badge-neutral" style="font-size:10px;margin-top:2px">{{ $item->kategori_kendala }}</span>
                                @endif
                            </td>
                            <td style="color:#334155;max-width:190px">
                                {{ $item->solusi ?: '—' }}
                            </td>
                            <td>
                                <span class="badge {{ $statusBadge[0] }}">{{ $statusBadge[1] }}</span>
                            </td>
                            <td style="text-align:center;white-space:nowrap">
                                <div class="d-flex gap-8" style="justify-content:center">
                                    <button type="button"
                                            class="btn btn-secondary btn-sm btn-icon"
                                            title="Edit Log Perbaikan"
                                            onclick="openEditRepairModal({
                                                id: {{ $item->id }},
                                                tanggal: '{{ $item->tanggal_maintenance->format('Y-m-d') }}',
                                                vehicle_id: '{{ $item->vehicle_id }}',
                                                driver: '{{ addslashes($driverName) }}',
                                                kategori: '{{ addslashes($item->kategori_kendala ?? 'Service Ringan') }}',
                                                part: '{{ addslashes($item->details->pluck('nama_sparepart')->implode(', ')) }}',
                                                kendala: '{{ addslashes($item->deskripsi_masalah) }}',
                                                solusi: '{{ addslashes($item->solusi ?? '') }}',
                                                status: '{{ $item->status_maintenance }}'
                                            })">
                                        <i class="fa-solid fa-pen"></i>
                                    </button>

                                    <button type="button"
                                            class="btn btn-danger btn-sm btn-icon"
                                            title="Hapus Log Perbaikan"
                                            onclick="openDeleteRepairModal({{ $item->id }}, '{{ addslashes(strtoupper($item->vehicle->plat_nomor ?? 'Armada')) }}')">
                                        <i class="fa-regular fa-trash-can"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <div class="empty-state">
                                    <div class="empty-icon"><i class="fa-solid fa-screwdriver-wrench"></i></div>
                                    <div class="empty-title">Belum Ada Log Perbaikan</div>
                                    <div class="empty-text">Klik tombol "+ Tambah Log" untuk mencatat perbaikan armada baru ke database.</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($maintenances->hasPages())
        <div class="card-footer">
            {{ $maintenances->links('pagination::bootstrap-4') }}
        </div>
    @endif
</div>

@endsection

@section('modals')
{{-- ── MODAL EDIT LOG PERBAIKAN ── --}}
<div class="modal-overlay" id="modalEditRepair">
    <div class="modal-box">
        <form id="formEditRepair" method="POST" action="" style="display:flex;flex-direction:column;max-height:90vh;overflow:hidden">
            @csrf
            @method('PUT')

            <div class="modal-header">
                <div class="modal-title">
                    <i class="fa-solid fa-pen-to-square" style="margin-right:8px;color:#091E3A"></i>
                    Edit Log Perbaikan
                </div>
                <button type="button" class="modal-close" onclick="closeModal('modalEditRepair')">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="modal-body" style="overflow-y:auto;flex:1">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
                    <div class="form-group">
                        <label class="form-label">TANGGAL <span class="required">*</span></label>
                        <input type="date" name="tanggal_maintenance" id="er_tanggal" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">ARMADA <span class="required">*</span></label>
                        <select name="vehicle_id" id="er_vehicle_id" class="form-control" required>
                            @foreach($vehicles as $v)
                                <option value="{{ $v->id }}">{{ strtoupper($v->plat_nomor) }} — {{ $v->jenis_kendaraan }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
                    <div class="form-group">
                        <label class="form-label">DRIVER</label>
                        <input type="text" name="nama_driver" id="er_driver" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label">KATEGORI KENDALA</label>
                        <select name="kategori_kendala" id="er_kategori" class="form-control">
                            <option value="Kelistrikan">Kelistrikan</option>
                            <option value="Kaki-kaki">Kaki-kaki</option>
                            <option value="Ban">Ban</option>
                            <option value="Mesin">Mesin</option>
                            <option value="Service Ringan">Service Ringan</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">PART / SUKU CADANG</label>
                    <input type="text" name="part_name" id="er_part" class="form-control" placeholder="Contoh: Filter Oli, Kampas Rem">
                </div>

                <div class="form-group">
                    <label class="form-label">KENDALA <span class="required">*</span></label>
                    <textarea name="deskripsi_masalah" id="er_kendala" rows="2" class="form-control" required></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">SOLUSI / TINDAKAN</label>
                    <textarea name="solusi" id="er_solusi" rows="2" class="form-control"></textarea>
                </div>

                <div class="form-group" style="margin-bottom:0">
                    <label class="form-label">STATUS PENGERJAAN (SINKRON STATUS ARMADA)</label>
                    <select name="status_maintenance" id="er_status" class="form-control">
                        <option value="pending">Pending (Armada Tidak Layak Jalan)</option>
                        <option value="in_progress">Sedang Dikerjakan (Armada Sedang Diperbaiki)</option>
                        <option value="completed">Selesai (Armada Otomatis Siap Jalan)</option>
                    </select>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalEditRepair')">BATALKAN</button>
                <button type="submit" class="btn btn-primary" style="background:#0F2647">SIMPAN PERUBAHAN</button>
            </div>
        </form>
    </div>
</div>

{{-- ── MODAL HAPUS LOG PERBAIKAN ── --}}
<div class="modal-overlay" id="modalDeleteRepair">
    <div class="modal-box" style="max-width:420px">
        <div class="modal-header">
            <div class="modal-title" style="color:var(--danger)">Hapus Log Perbaikan</div>
            <button type="button" class="modal-close" onclick="closeModal('modalDeleteRepair')">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <div class="modal-body" style="text-align:center">
            Apakah Anda yakin ingin menghapus rekap perbaikan untuk unit <strong id="delRepairUnit"></strong> dari database?
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeModal('modalDeleteRepair')">Batal</button>
            <form id="formDeleteRepair" method="POST" action="">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">Ya, Hapus</button>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function openEditRepairModal(data) {
        document.getElementById('formEditRepair').action = '/mekanik/repairs/' + data.id;
        document.getElementById('er_tanggal').value    = data.tanggal;
        document.getElementById('er_vehicle_id').value = data.vehicle_id;
        document.getElementById('er_driver').value     = data.driver;
        document.getElementById('er_kategori').value   = data.kategori || 'Service Ringan';
        document.getElementById('er_part').value       = data.part;
        document.getElementById('er_kendala').value    = data.kendala;
        document.getElementById('er_solusi').value     = data.solusi;
        document.getElementById('er_status').value     = data.status || 'completed';
        openModal('modalEditRepair');
    }

    function openDeleteRepairModal(id, unitPlat) {
        document.getElementById('formDeleteRepair').action = '/mekanik/repairs/' + id;
        document.getElementById('delRepairUnit').textContent = unitPlat;
        openModal('modalDeleteRepair');
    }
</script>
@endpush

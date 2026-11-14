<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Catatan Perbaikan Armada — TOYACHECK</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        body {
            font-family: 'Plus Jakarta Sans', Arial, sans-serif;
            color: #0F1E36;
            background: #F8FAFC;
            padding: 24px;
            font-size: 12px;
            line-height: 1.45;
        }

        .no-print {
            max-width: 1100px;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            background: #FFFFFF;
            padding: 14px 20px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(15, 30, 54, 0.06);
            border: 1px solid #E2E8F0;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 9px 18px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            border: none;
            transition: all 0.15s;
        }

        .btn-primary {
            background: #0B2E8A;
            color: #FFFFFF;
        }
        .btn-primary:hover { background: #071F60; }

        .btn-secondary {
            background: #F1F5F9;
            color: #334155;
            border: 1px solid #CBD5E1;
        }
        .btn-secondary:hover { background: #E2E8F0; }

        .print-sheet {
            max-width: 1100px;
            margin: 0 auto;
            background: #FFFFFF;
            border-radius: 16px;
            padding: 32px 36px;
            box-shadow: 0 4px 20px rgba(15, 30, 54, 0.05);
            border: 1px solid #E2E8F0;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding-bottom: 20px;
            border-bottom: 2.5px solid #0B2E8A;
            margin-bottom: 20px;
        }

        .brand-title {
            font-size: 24px;
            font-weight: 800;
            letter-spacing: 0.04em;
            color: #0B2E8A;
        }

        .brand-sub {
            font-size: 11.5px;
            color: #64748B;
            margin-top: 4px;
            font-weight: 500;
        }

        .meta-print {
            text-align: right;
            font-size: 11px;
            color: #475569;
        }

        .stats-strip {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            margin-bottom: 22px;
        }

        .stat-box {
            background: #F8FAFC;
            border: 1px solid #E2E8F0;
            border-radius: 10px;
            padding: 12px 16px;
        }

        .stat-label {
            font-size: 11px;
            color: #64748B;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .stat-val {
            font-size: 20px;
            font-weight: 800;
            color: #0F1E36;
            margin-top: 3px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            font-size: 11.5px;
        }

        th, td {
            border: 1px solid #E2E8F0;
            padding: 9px 12px;
            text-align: left;
            vertical-align: top;
        }

        th {
            background: #0B2E8A;
            color: #FFFFFF;
            font-weight: 700;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        tbody tr:nth-child(even) {
            background: #F8FAFC;
        }

        .badge-status {
            display: inline-block;
            padding: 3px 9px;
            border-radius: 999px;
            font-size: 10.5px;
            font-weight: 700;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .badge-status.pending {
            background: #FEF3C7;
            color: #B45309;
            border: 1px solid #FCD34D;
        }

        .badge-status.in_progress {
            background: #EDE9FE;
            color: #6D28D9;
            border: 1px solid #C4B5FD;
        }

        .badge-status.completed {
            background: #DCFCE7;
            color: #15803D;
            border: 1px solid #86EFAC;
        }

        .plat-code {
            font-family: 'JetBrains Mono', monospace;
            font-weight: 700;
            background: #0F1E36;
            color: #FFFFFF;
            padding: 2px 7px;
            border-radius: 4px;
            font-size: 10.5px;
            letter-spacing: 0.05em;
        }

        .footer-sign {
            margin-top: 36px;
            display: flex;
            justify-content: space-between;
            page-break-inside: avoid;
        }

        .sign-box {
            text-align: center;
            width: 200px;
            font-size: 11.5px;
            color: #334155;
        }

        .sign-space {
            height: 60px;
        }

        @media print {
            .no-print { display: none !important; }
            body { background: #FFFFFF; padding: 0; }
            .print-sheet { border: none; box-shadow: none; padding: 10px 0; }
        }
    </style>
</head>
<body>

<div class="no-print">
    <div style="font-size:13px;font-weight:700;color:#0F1E36">
        Dokumen Laporan Catatan Perbaikan Armada (Total {{ $maintenances->count() }} Data)
    </div>
    <div style="display:flex;gap:8px">
        <button type="button" class="btn btn-primary" onclick="window.print()">
            Cetak / Simpan PDF
        </button>
        <a href="{{ auth()->user()->hasRole('mekanik') ? route('mekanik.repairs', request()->query()) : route('maintenances.index', request()->query()) }}" class="btn btn-secondary">
            Kembali
        </a>
    </div>
</div>

<div class="print-sheet">
    <div class="header">
        <div>
            <div class="brand-title">TOYACHECK — LAPORAN CATATAN PERBAIKAN ARMADA</div>
            <div class="brand-sub">PT Tirta Alam Segar &bull; Divisi Maintenance & Workshop Mekanik Mountoya</div>
        </div>
        <div class="meta-print">
            <div>Dicetak pada: <strong>{{ now()->format('d/m/Y H:i') }} WIB</strong></div>
            <div>Oleh: <strong>{{ auth()->user()->name }}</strong> ({{ strtoupper(auth()->user()->getRoleNames()->first() ?? 'Admin') }})</div>
        </div>
    </div>

    <div class="stats-strip">
        <div class="stat-box">
            <div class="stat-label">Total Log</div>
            <div class="stat-val">{{ $stats['total'] }}</div>
        </div>
        <div class="stat-box">
            <div class="stat-label">Menunggu (Pending)</div>
            <div class="stat-val" style="color:#D97706">{{ $stats['pending'] }}</div>
        </div>
        <div class="stat-box">
            <div class="stat-label">Sedang Dikerjakan</div>
            <div class="stat-val" style="color:#7C3AED">{{ $stats['in_progress'] }}</div>
        </div>
        <div class="stat-box">
            <div class="stat-label">Selesai</div>
            <div class="stat-val" style="color:#16A34A">{{ $stats['completed'] }}</div>
        </div>
    </div>

    @if(!empty($status) || !empty($search))
    <div style="margin-bottom:14px;font-size:11px;color:#475569;display:flex;gap:8px">
        <span style="font-weight:700">Filter Aktif:</span>
        @if(!empty($status))
            <span style="background:#EFF6FF;color:#1E40AF;padding:2px 8px;border-radius:4px;border:1px solid #BFDBFE">
                Status: {{ strtoupper($status) }}
            </span>
        @endif
        @if(!empty($search))
            <span style="background:#EFF6FF;color:#1E40AF;padding:2px 8px;border-radius:4px;border:1px solid #BFDBFE">
                Pencarian: "{{ $search }}"
            </span>
        @endif
    </div>
    @endif

    <table>
        <thead>
            <tr>
                <th style="width:36px;text-align:center">No</th>
                <th style="width:110px">Tanggal</th>
                <th>Armada</th>
                <th>Mekanik & Sopir</th>
                <th>Kerusakan / Masalah</th>
                <th>Sparepart Digunakan</th>
                <th style="width:110px;text-align:center">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($maintenances as $index => $item)
                @php
                    $statusLabel = match($item->status_maintenance) {
                        'pending'     => 'Pending',
                        'in_progress' => 'Proses',
                        'completed'   => 'Selesai',
                        default       => ucfirst($item->status_maintenance),
                    };
                @endphp
                <tr>
                    <td style="text-align:center">{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ $item->tanggal_maintenance ? $item->tanggal_maintenance->format('d/m/Y') : '-' }}</strong><br>
                        <span style="font-size:10.5px;color:#64748B">
                            {{ $item->tanggal_maintenance ? $item->tanggal_maintenance->format('H:i') . ' WIB' : '' }}
                        </span>
                    </td>
                    <td>
                        <span class="plat-code">{{ strtoupper($item->vehicle->plat_nomor ?? '-') }}</span><br>
                        <span style="font-size:10.5px;color:#475569">
                            {{ $item->vehicle->merk ?? '' }} {{ $item->vehicle->jenis_kendaraan ?? '' }}
                        </span>
                    </td>
                    <td>
                        <strong>{{ $item->mekanik->name ?? 'Mekanik' }}</strong><br>
                        <span style="font-size:10.5px;color:#64748B">
                            Sopir: {{ $item->nama_driver ?? ($item->checklist?->driver?->name ?? '-') }}
                        </span>
                    </td>
                    <td>
                        <div style="font-weight:600;color:#0F1E36">
                            {{ $item->deskripsi_masalah ?: ($item->kategori_kendala ?: 'Perbaikan unit') }}
                        </div>
                        @if($item->solusi)
                            <div style="font-size:10.5px;color:#16A34A;margin-top:2px">
                                Solusi: {{ $item->solusi }}
                            </div>
                        @endif
                    </td>
                    <td>
                        @if($item->details && $item->details->count() > 0)
                            <ul style="padding-left:14px;margin:0">
                                @foreach($item->details as $d)
                                    <li>{{ $d->sparepart->nama_sparepart ?? ($d->nama_sparepart ?: 'Sparepart') }} ({{ $d->qty }} pcs)</li>
                                @endforeach
                            </ul>
                        @else
                            <span style="color:#64748B">Jasa / Tanpa ganti suku cadang</span>
                        @endif
                    </td>
                    <td style="text-align:center">
                        <span class="badge-status {{ $item->status_maintenance }}">
                            {{ $statusLabel }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align:center;padding:24px;color:#64748B">
                        Tidak ada data catatan perbaikan armada yang ditemukan.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer-sign">
        <div class="sign-box">
            <div>Mengetahui,</div>
            <div style="font-weight:700;margin-top:2px">Kepala Bengkel & Mekanik</div>
            <div class="sign-space"></div>
            <div style="border-top:1px solid #94A3B8;padding-top:4px;font-weight:700">( ............................................ )</div>
        </div>

        <div class="sign-box">
            <div>Dibuat oleh,</div>
            <div style="font-weight:700;margin-top:2px">{{ auth()->user()->name }}</div>
            <div class="sign-space"></div>
            <div style="border-top:1px solid #94A3B8;padding-top:4px;font-weight:700">( {{ auth()->user()->employee_id ?? 'Admin Distribusi' }} )</div>
        </div>
    </div>
</div>

</body>
</html>


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Riwayat K3 & Penggunaan Kendaraan — {{ $vehicle->plat_nomor }}</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            color: #111827;
            margin: 28px;
            font-size: 12px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #071843;
            padding-bottom: 12px;
            margin-bottom: 18px;
        }
        .brand {
            font-size: 20px;
            font-weight: 800;
            letter-spacing: 0.04em;
            color: #071843;
        }
        .meta-box {
            background: #F3F4F6;
            border: 1px solid #D1D5DB;
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 18px;
            display: flex;
            justify-content: space-between;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }
        th, td {
            border: 1px solid #9CA3AF;
            padding: 8px 10px;
            text-align: left;
            vertical-align: top;
        }
        th {
            background: #071843;
            color: #FFFFFF;
            font-size: 11px;
            text-transform: uppercase;
        }
        .badge-aman {
            color: #166534;
            font-weight: 700;
        }
        .badge-kendala {
            color: #BA1A1A;
            font-weight: 700;
        }
        .no-print {
            margin-bottom: 16px;
            display: flex;
            gap: 10px;
        }
        .btn {
            background: #4318D1;
            color: #FFFFFF;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            font-size: 12px;
        }
        @media print {
            .no-print { display: none !important; }
            body { margin: 10px; }
        }
    </style>
</head>
<body onload="window.print()">

<div class="no-print">
    <button type="button" class="btn" onclick="window.print()">Cetak / Simpan PDF</button>
    <a href="{{ route('driver.vehicle.show', $vehicle->id) }}" class="btn" style="background:#374151">Kembali</a>
</div>

<div class="header">
    <div>
        <div class="brand">TOYACHECK — LAPORAN K3 & KENDARAAN</div>
        <div style="font-size:11px;color:#4B5563;margin-top:3px">PT Tirta Alam Segar / Mountoya Distribusi & HSE</div>
    </div>
    <div style="text-align:right;font-size:11px;color:#374151">
        Dicetak pada: {{ now()->format('d/m/Y H:i') }} WIB<br>
        Oleh: {{ auth()->user()->name ?? 'Petugas HSE' }}
    </div>
</div>

<div class="meta-box">
    <div>
        <strong>Plat Nomor:</strong> {{ strtoupper($vehicle->plat_nomor) }}<br>
        <strong>Kendaraan:</strong> {{ $vehicle->merk }} ({{ $vehicle->jenis_kendaraan }})
    </div>
    <div>
        <strong>Status Armada:</strong> {{ \App\Models\Vehicle::STATUSES[$vehicle->status] ?? $vehicle->status }}<br>
        <strong>Total Riwayat Tercatat:</strong> {{ $usageHistory->count() }} Laporan
    </div>
</div>

<table>
    <thead>
        <tr>
            <th style="width:36px">No</th>
            <th>Tanggal & Waktu</th>
            <th>Petugas / Sopir</th>
            <th>Kategori</th>
            <th>Status</th>
            <th>Ringkasan Item & Catatan</th>
        </tr>
    </thead>
    <tbody>
        @forelse($usageHistory as $idx => $log)
            @php
                $badItems = $log->items->where('kondisi', false);
            @endphp
            <tr>
                <td>{{ $idx + 1 }}</td>
                <td>{{ $log->tanggal->format('d/m/Y H:i') }} WIB</td>
                <td>
                    <strong>{{ $log->driver->name ?? '-' }}</strong><br>
                    <span style="font-size:10px;color:#4B5563">{{ $log->driver->employee_id ?? $log->driver->username ?? '' }}</span>
                </td>
                <td>{{ strtoupper($log->checklist_type) }}</td>
                <td>
                    <span class="{{ $log->status === 'aman' ? 'badge-aman' : 'badge-kendala' }}">
                        {{ $log->status === 'aman' ? 'AMAN' : 'KENDALA (' . $badItems->count() . ')' }}
                    </span>
                </td>
                <td>
                    @if($badItems->count() > 0)
                        <div><strong>Item Kendala:</strong> {{ $badItems->pluck('item')->implode(', ') }}</div>
                    @else
                        <div>Seluruh komponen dalam keadaan baik / bersih sesuai standar.</div>
                    @endif
                    @if($log->catatan)
                        <div style="margin-top:4px;color:#374151"><em>Catatan: {{ $log->catatan }}</em></div>
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" style="text-align:center;padding:20px">Belum ada riwayat pemeriksaan untuk kendaraan ini.</td>
            </tr>
        @endforelse
    </tbody>
</table>

</body>
</html>


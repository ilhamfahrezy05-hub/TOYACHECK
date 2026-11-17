<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Persetujuan Pengadaan — TOYACHECK</title>
    <style>
        body { font-family: Arial, Helvetica, sans-serif; color: #0F172A; margin: 24px; font-size: 12px; }
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #0F172A; padding-bottom: 12px; margin-bottom: 20px; }
        .title { font-size: 18px; font-weight: bold; }
        .sub { font-size: 11px; color: #475569; margin-top: 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #CBD5E1; padding: 8px 10px; text-align: left; }
        th { background: #F1F5F9; font-size: 11px; text-transform: uppercase; }
        .no-print { margin-bottom: 16px; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body onload="window.print()">
    <div class="no-print">
        <button onclick="window.print()" style="padding:8px 16px;background:#0F172A;color:#fff;border:none;border-radius:4px;cursor:pointer;font-weight:bold">
            Cetak / Simpan PDF
        </button>
    </div>

    <div class="header">
        <div>
            <div class="title">DAFTAR PERSETUJUAN PENGADAAN SUKU CADANG ARMADA</div>
            <div class="sub">PT. TOYAMILINDO (MOUNTOYA) — Divisi Procurement &amp; Logistics Control</div>
        </div>
        <div style="text-align:right;font-size:11px">
            Dicetak: {{ now()->translatedFormat('d F Y H:i') }} WIB<br>
            Petugas: {{ auth()->user()->name }}
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:40px">No</th>
                <th>ID Permintaan</th>
                <th>Nama Suku Cadang</th>
                <th>Qty</th>
                <th>Kategori</th>
                <th>Nama Vendor</th>
                <th>Tanggal Disetujui</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($approvedRequests as $idx => $req)
                <tr>
                    <td>{{ $idx + 1 }}</td>
                    <td style="font-family:monospace;font-weight:bold">#REQ-{{ $req->created_at->format('Y') }}-{{ str_pad($req->id, 3, '0', STR_PAD_LEFT) }}</td>
                    <td><strong>{{ $req->nama_sparepart }}</strong></td>
                    <td>{{ $req->qty }}</td>
                    <td>{{ $req->sparepart->kategori ?? 'Suku Cadang' }}</td>
                    <td>{{ $req->vendor_name ?: 'PT. Nusantara Perkasa' }}</td>
                    <td>{{ ($req->reviewed_at ?? $req->created_at)->translatedFormat('d M Y') }}</td>
                    <td><strong>Disetujui</strong></td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align:center;padding:20px">Tidak ada data permintaan disetujui.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>

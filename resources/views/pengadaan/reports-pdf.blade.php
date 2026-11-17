<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Riwayat Belanja Pengadaan — TOYACHECK</title>
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
            <div class="title">LAPORAN RIWAYAT PEMBELANJAAN SUKU CADANG (PENGADAAN)</div>
            <div class="sub">PT. TOYAMILINDO (MOUNTOYA) — Sistem Monitoring Operasional Armada Distribusi</div>
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
                <th>Kode Order</th>
                <th>Nama Suku Cadang &amp; SKU</th>
                <th>Nama Vendor</th>
                <th>Tanggal &amp; Jam</th>
                <th style="text-align:right">Total Harga</th>
            </tr>
        </thead>
        <tbody>
            @forelse($purchases as $idx => $pur)
                <tr>
                    <td>{{ $idx + 1 }}</td>
                    <td style="font-family:monospace;font-weight:bold">{{ $pur->kode_pembelian }}</td>
                    <td>
                        @foreach($pur->items as $it)
                            <div><strong>{{ $it->nama_sparepart }}</strong> ({{ $it->kode_barang }}) &times; {{ $it->qty }}</div>
                        @endforeach
                    </td>
                    <td>{{ $pur->vendor?->nama_vendor ?: $pur->vendor_name }}</td>
                    <td>{{ $pur->tanggal_pembelian->translatedFormat('d M Y') }} {{ $pur->waktu_pembelian }} WIB</td>
                    <td style="text-align:right;font-weight:bold">Rp {{ number_format($pur->total_harga, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align:center;padding:20px">Tidak ada data transaksi pembelanjaan.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <th colspan="5" style="text-align:right">TOTAL PENGELUARAN</th>
                <th style="text-align:right">Rp {{ number_format($totalNominal, 0, ',', '.') }}</th>
            </tr>
        </tfoot>
    </table>
</body>
</html>

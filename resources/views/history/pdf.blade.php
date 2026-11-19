<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Checklist Kendaraan — {{ $singleVehicle ? ($singleVehicle->plat_nomor . ' ') : '' }}{{ $bulan }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 8mm 8mm 7mm 8mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: 'Arial', 'Helvetica', sans-serif;
            color: #111827;
            background: #e2e8f0;
            font-size: 9.5px;
            line-height: 1.25;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* Screen Toolbar */
        .print-toolbar {
            position: sticky;
            top: 0;
            z-index: 50;
            background: #0f172a;
            color: #fff;
            padding: 10px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }

        .toolbar-title {
            font-size: 13px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .toolbar-actions {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .month-form {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #1e293b;
            padding: 4px 10px;
            border-radius: 6px;
            border: 1px solid #334155;
        }

        .month-form label {
            font-size: 11px;
            color: #cbd5e1;
            font-weight: 600;
        }

        .month-form input[type="month"] {
            background: #0f172a;
            color: #fff;
            border: 1px solid #475569;
            border-radius: 4px;
            padding: 4px 8px;
            font-size: 12px;
        }

        .btn-toolbar {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border: none;
            border-radius: 6px;
            padding: 7px 14px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
        }

        .btn-print { background: #1d4ed8; color: #fff; }
        .btn-apply { background: #0f766e; color: #fff; padding: 5px 10px; }
        .btn-back  { background: #334155; color: #fff; }

        /* Sheet Wrapper (A4 Landscape preview on screen) */
        .sheets-container {
            padding: 16px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 18px;
        }

        .sheet-page {
            width: 281mm;
            background: #ffffff;
            padding: 6mm 7mm;
            box-shadow: 0 4px 18px rgba(15, 23, 42, 0.14);
        }

        .page-break {
            page-break-before: always;
            break-before: page;
        }

        /* ======================================================
           SHEET 1: FISIK — Checklist Kendaraan Distribusi
        ====================================================== */
        .fisik-top-title {
            text-align: center;
            margin-bottom: 6px;
        }

        .fisik-title-pill {
            display: inline-block;
            border: 1.5px solid #111827;
            border-radius: 8px;
            padding: 3px 26px;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.2px;
        }

        .fisik-meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
            font-size: 9px;
        }

        .fisik-meta-table td {
            padding: 1.5px 4px;
            vertical-align: middle;
        }

        .fisik-meta-label {
            width: 110px;
            font-weight: 600;
        }

        .fisik-meta-colon {
            width: 10px;
            text-align: center;
        }

        .fisik-note-line {
            font-weight: 700;
            font-size: 8.5px;
        }

        .matrix-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .matrix-table th,
        .matrix-table td {
            border: 1px solid #111827;
            padding: 1.5px 3px;
            vertical-align: middle;
            overflow: hidden;
        }

        .matrix-table thead th {
            background: #d1d5db;
            font-weight: 700;
            text-align: center;
            font-size: 8.5px;
        }

        .col-no { width: 20px; text-align: center; }
        .col-komponen { width: 105px; }
        .col-kriteria { width: 145px; }
        .col-day { width: 16.5px; text-align: center; padding: 1px 0 !important; font-size: 8.5px; }

        .group-row td {
            background: #e5e7eb;
            font-weight: 700;
            font-size: 8.5px;
            padding: 2px 4px;
        }

        .item-row td {
            font-size: 8px;
            line-height: 1.15;
            height: 14.5px;
        }

        .mark-ok {
            font-weight: 700;
            color: #111827;
            font-size: 9.5px;
        }

        .mark-bad {
            font-weight: 800;
            color: #b91c1c;
            font-size: 9px;
        }

        .fisik-footer {
            margin-top: 7px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 16px;
        }

        .fisik-notes-box {
            flex: 1;
            font-size: 8.5px;
        }

        .fisik-notes-box .label {
            font-weight: 700;
            margin-bottom: 3px;
        }

        .fisik-notes-list {
            margin: 0;
            padding-left: 16px;
        }

        .fisik-notes-list li {
            margin-bottom: 2px;
        }

        .fisik-sign-table {
            width: 315px;
            border-collapse: collapse;
            font-size: 8.5px;
            text-align: center;
        }

        .fisik-sign-table th,
        .fisik-sign-table td {
            border: 1px solid #111827;
            padding: 2px 4px;
        }

        .fisik-sign-table .sign-space {
            height: 32px;
            vertical-align: bottom;
            font-weight: 700;
        }

        .fisik-sign-table .sign-role {
            font-weight: 600;
        }

        /* ======================================================
           SHEET 2: K3 — CHECKLIST KENDARAAN UNTUK PENGIRIMAN PRODUK JADI
        ====================================================== */
        .k3-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            margin-bottom: 8px;
        }

        .k3-header-spacer {
            width: 175px;
        }

        .k3-title-pill {
            border: 1.5px solid #111827;
            border-radius: 6px;
            padding: 5px 28px;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            text-align: center;
        }

        .k3-doc-code {
            width: 175px;
            text-align: right;
        }

        .k3-brand {
            font-size: 13px;
            font-weight: 900;
            letter-spacing: 0.8px;
            color: #111827;
        }

        .k3-doc-no {
            font-size: 9px;
            font-weight: 800;
            margin-top: 3px;
            color: #0f172a;
            letter-spacing: 0.2px;
        }

        .k3-instruction {
            font-size: 8px;
            font-weight: 800;
            text-transform: uppercase;
            margin-bottom: 4px;
        }

        .k3-meta-grid {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
            font-size: 8.5px;
            font-weight: 700;
        }

        .k3-meta-grid td {
            padding: 2px 3px;
            vertical-align: bottom;
        }

        .k3-meta-line {
            border-bottom: 1px solid #111827;
            min-width: 130px;
            display: inline-block;
            padding: 0 4px 1px 4px;
            font-weight: 600;
        }

        .k3-col-no { width: 22px; text-align: center; }
        .k3-col-kondisi { width: 280px; }

        .k3-table thead th {
            background: #ffffff;
            color: #111827;
            font-weight: 800;
            font-size: 8.5px;
        }

        .k3-table td {
            font-size: 8.3px;
            padding: 3px 4px;
        }

        .k3-decision-row td {
            font-weight: 800;
            text-align: center;
            background: #f9fafb;
            height: 22px;
            font-size: 8.5px;
        }

        .k3-initial-row td {
            font-weight: 700;
            text-align: center;
            height: 22px;
            font-size: 8px;
        }

        .k3-bottom-note {
            margin-top: 6px;
            font-size: 7.3px;
            font-weight: 700;
            line-height: 1.35;
        }

        .k3-footer-area {
            margin-top: 6px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 24px;
        }

        .k3-comment-box {
            flex: 1;
            max-width: 500px;
            min-height: 54px;
            border: 1px solid #111827;
            padding: 4px 7px;
            font-size: 8.2px;
        }

        .k3-comment-box .title {
            font-weight: 800;
            margin-bottom: 3px;
        }

        .k3-signs {
            display: flex;
            gap: 48px;
            padding-right: 18px;
            font-size: 8.5px;
            font-weight: 700;
            text-align: center;
        }

        .k3-sign-col {
            width: 115px;
        }

        .k3-sign-col .line {
            margin-top: 38px;
            border-bottom: 1px solid #111827;
            padding-bottom: 2px;
            font-weight: 600;
            min-height: 14px;
        }

        .k3-company-strip {
            margin-top: 10px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            font-size: 8px;
        }

        .k3-company-name {
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.5px;
        }

        .k3-company-addr {
            text-align: right;
            font-size: 7.3px;
            line-height: 1.25;
            font-weight: 600;
        }

        @media print {
            body {
                background: #ffffff;
            }
            .print-toolbar {
                display: none !important;
            }
            .sheets-container {
                padding: 0;
                gap: 0;
            }
            .sheet-page {
                width: 100%;
                padding: 0;
                box-shadow: none;
            }
        }
    </style>
</head>
<body>

<div class="print-toolbar">
    <div class="toolbar-title">
        <span>Laporan Checklist Kendaraan (2 Lembar / Armada: Fisik &amp; K3)</span>
        <span style="font-size:11px;color:#94a3b8;font-weight:500;">
            ({{ count($sheets) }} armada &middot; {{ count($sheets) * 2 }} halaman)
        </span>
    </div>

    <div class="toolbar-actions">
        <form method="GET" action="{{ url()->current() }}" class="month-form">
            @foreach(request()->except(['bulan']) as $k => $v)
                @if(filled($v))
                    <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                @endif
            @endforeach
            <label for="bulanPicker">Bulan:</label>
            <input type="month" id="bulanPicker" name="bulan" value="{{ $bulan }}">
            <button type="submit" class="btn-toolbar btn-apply">Terapkan</button>
        </form>

        <a href="{{ route('riwayat.index', $filters) }}" class="btn-toolbar btn-back">Kembali</a>
        <button type="button" onclick="window.print()" class="btn-toolbar btn-print">Cetak / Simpan PDF</button>
    </div>
</div>

<div class="sheets-container">
@forelse($sheets as $sheetIndex => $sheet)
    @php
        $v = $sheet['vehicle'];
        $jenisMobil = trim(($v->jenis_kendaraan ?? '-') . ($v->dimensi_box ? ' / ' . $v->dimensi_box : ''));
    @endphp

    {{-- =====================================================================
         LEMBAR 1: FISIK — Checklist Kendaraan Distribusi
    ====================================================================== --}}
    <div class="sheet-page {{ $sheetIndex > 0 ? 'page-break' : '' }}">
        <div class="fisik-top-title">
            <span class="fisik-title-pill">Checklist Kendaraan Distribusi</span>
        </div>

        <table class="fisik-meta-table">
            <tr>
                <td class="fisik-meta-label">Nama Pengemudi</td>
                <td class="fisik-meta-colon">:</td>
                <td>{{ $sheet['driverName'] }}</td>
            </tr>
            <tr>
                <td class="fisik-meta-label">No. Polisi</td>
                <td class="fisik-meta-colon">:</td>
                <td><strong>{{ $v->plat_nomor }}</strong> ({{ $v->merk }})</td>
            </tr>
            <tr>
                <td class="fisik-meta-label">Jenis Mobil</td>
                <td class="fisik-meta-colon">:</td>
                <td>{{ $jenisMobil }}</td>
            </tr>
            <tr>
                <td class="fisik-meta-label">Bulan</td>
                <td class="fisik-meta-colon">:</td>
                <td>{{ $sheet['bulanNama'] }} {{ $sheet['tahunNama'] }}</td>
            </tr>
            <tr>
                <td class="fisik-meta-label">Note</td>
                <td class="fisik-meta-colon">:</td>
                <td class="fisik-note-line">Jika keadaan kendaraan baik maka beri tanda &ldquo;&#10003;&rdquo;, jika kendaraan tidak baik beri tanda &ldquo;X&rdquo;</td>
            </tr>
        </table>

        <table class="matrix-table">
            <thead>
                <tr>
                    <th class="col-no">No</th>
                    <th class="col-komponen">Komponen</th>
                    <th class="col-kriteria">Kriteria Pemeriksaan</th>
                    @for($d = 1; $d <= 31; $d++)
                        <th class="col-day">{{ $d }}</th>
                    @endfor
                </tr>
            </thead>
            <tbody>
                @forelse($sheet['fisikGroups'] as $group)
                    <tr class="group-row">
                        <td colspan="34">{{ $group['letter'] }}. {{ $group['groupName'] }}</td>
                    </tr>
                    @foreach($group['rows'] as $row)
                        <tr class="item-row">
                            <td class="col-no">{{ $row['no'] }}</td>
                            <td class="col-komponen">{{ $row['nama'] }}</td>
                            <td class="col-kriteria">{{ $row['criteria'] }}</td>
                            @for($d = 1; $d <= 31; $d++)
                                @php $mark = $row['days'][$d] ?? null; @endphp
                                <td class="col-day">
                                    @if($mark === '✓')
                                        <span class="mark-ok">&#10003;</span>
                                    @elseif($mark === 'X')
                                        <span class="mark-bad">X</span>
                                    @endif
                                </td>
                            @endfor
                        </tr>
                    @endforeach
                @empty
                    <tr>
                        <td colspan="34" style="text-align:center;padding:12px;color:#64748b;">
                            Belum ada parameter pemeriksaan fisik terdaftar.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="fisik-footer">
            <div class="fisik-notes-box">
                <div class="label">Catatan Tambahan :</div>
                @if(!empty($sheet['fisikNotes']))
                    <ol class="fisik-notes-list">
                        @foreach(array_slice($sheet['fisikNotes'], 0, 5) as $note)
                            <li>{{ $note }}</li>
                        @endforeach
                    </ol>
                @else
                    <div style="color:#6b7280;font-style:italic;">1. Tidak ada catatan kendala pada bulan {{ $sheet['bulanNama'] }} {{ $sheet['tahunNama'] }}.</div>
                @endif
            </div>

            <table class="fisik-sign-table">
                <tr>
                    <th style="width:33.3%;">Dibuat Oleh,</th>
                    <th style="width:33.3%;">Diperiksa Oleh,</th>
                    <th style="width:33.3%;">Diketahui Oleh,</th>
                </tr>
                <tr>
                    <td class="sign-space">{{ $sheet['driverName'] !== '-' ? $sheet['driverName'] : '' }}</td>
                    <td class="sign-space">Eddy Ilyas</td>
                    <td class="sign-space">Shofa Nuzula Anamy</td>
                </tr>
                <tr>
                    <td class="sign-role">Driver</td>
                    <td class="sign-role">Head Of Distribution</td>
                    <td class="sign-role">HSE</td>
                </tr>
            </table>
        </div>
    </div>

    {{-- =====================================================================
         LEMBAR 2: K3 — CHECKLIST KENDARAAN UNTUK PENGIRIMAN PRODUK JADI
    ====================================================================== --}}
    <div class="sheet-page page-break">
        <div class="k3-header">
            <div class="k3-header-spacer"></div>
            <div class="k3-title-pill">CHECKLIST KENDARAAN UNTUK PENGIRIMAN PRODUK JADI</div>
            <div class="k3-doc-code">
                <div class="k3-brand">MOUNTOYA<sup>&reg;</sup></div>
                <div class="k3-doc-no">No. Dok: DSB- F - 02 - 01, Rev.00</div>
            </div>
        </div>

        <div class="k3-instruction">
            BERI TANDA V UNTUK TIAP KOLOM YANG SESUAI STANDARD DAN TANDA X UNTUK TIAP KOLOM YANG TIDAK SESUAI STANDAR
        </div>

        <table class="k3-meta-grid">
            <tr>
                <td style="width:18%;">Kendaraan Milik</td>
                <td style="width:34%;">
                    @if(($v->kepemilikan ?? 'internal') === 'eksternal')
                        : <s style="color:#6b7280;">Internal</s> / <u>Eksternal*</u> <span style="font-weight:500;font-size:7.5px;">(coret salah satu)</span>
                    @else
                        : <u>Internal</u> / <s style="color:#6b7280;">Eksternal*</s> <span style="font-weight:500;font-size:7.5px;">(coret salah satu)</span>
                    @endif
                </td>
                <td style="width:12%;">Ekspedisi</td>
                <td style="width:36%;">: <span class="k3-meta-line">PT. Toyamilindo ({{ ($v->kepemilikan ?? 'internal') === 'eksternal' ? 'Eksternal' : 'Internal' }})</span></td>
            </tr>
            <tr>
                <td>Nomor Kendaraan</td>
                <td>: <span class="k3-meta-line">{{ $v->plat_nomor }} ({{ $v->merk }})</span></td>
                <td>Pengemudi</td>
                <td>: <span class="k3-meta-line">{{ $sheet['driverName'] }}</span></td>
            </tr>
            <tr>
                <td colspan="4" style="text-align:center;padding-top:3px;">
                    Bulan : <span class="k3-meta-line" style="min-width:95px;text-align:left;">{{ $sheet['bulanSingkat'] }}</span>
                    &nbsp;&nbsp;&nbsp;&nbsp;
                    Tahun : <span class="k3-meta-line" style="min-width:85px;text-align:left;">{{ $sheet['tahunNama'] }}</span>
                </td>
            </tr>
        </table>

        <table class="matrix-table k3-table">
            <thead>
                <tr>
                    <th rowspan="2" class="k3-col-no">NO</th>
                    <th rowspan="2" class="k3-col-kondisi">KONDISI KENDARAAN</th>
                    <th colspan="31">Tanggal</th>
                </tr>
                <tr>
                    @for($d = 1; $d <= 31; $d++)
                        <th class="col-day">{{ $d }}</th>
                    @endfor
                </tr>
            </thead>
            <tbody>
                @forelse($sheet['k3Rows'] as $row)
                    <tr>
                        <td class="k3-col-no" style="text-align:center;">{{ $row['no'] }}</td>
                        <td class="k3-col-kondisi">{{ $row['nama'] }}</td>
                        @for($d = 1; $d <= 31; $d++)
                            @php $mark = $row['days'][$d] ?? null; @endphp
                            <td class="col-day">
                                @if($mark === '✓')
                                    <span class="mark-ok">&#10003;</span>
                                @elseif($mark === 'X')
                                    <span class="mark-bad">X</span>
                                @endif
                            </td>
                        @endfor
                    </tr>
                @empty
                    <tr>
                        <td colspan="33" style="text-align:center;padding:12px;color:#64748b;">
                            Belum ada parameter pemeriksaan K3 terdaftar.
                        </td>
                    </tr>
                @endforelse

                <tr class="k3-decision-row">
                    <td colspan="2">KEPUTUSAN DIPAKAI : Ya (Y) atau TIDAK (T)</td>
                    @for($d = 1; $d <= 31; $d++)
                        @php $dec = $sheet['k3Decisions'][$d] ?? null; @endphp
                        <td class="col-day" style="{{ $dec === 'T' ? 'color:#b91c1c;' : '' }}">{{ $dec ?? '' }}</td>
                    @endfor
                </tr>

                <tr class="k3-initial-row">
                    <td colspan="2">Inisial pemeriksa</td>
                    @for($d = 1; $d <= 31; $d++)
                        <td class="col-day" style="font-size:7px;">{{ $sheet['k3Initials'][$d] ?? '' }}</td>
                    @endfor
                </tr>
            </tbody>
        </table>

        <div class="k3-bottom-note">
            NOTE : &nbsp;JIKA KONDISI KENDARAAN MEMENUHI SEMUA KETENTUAN TERSEBUT DIATAS DAN KEPUTUSANNYA DIPAKAI MAKA BERIKAN TANDA &ldquo;Y&rdquo; DAN TANDA &ldquo;T&rdquo; JIKA KENDARAAN TERNYATA TIDAK DAPAT DIPAKAI/DITOLAK<br>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;LIHAT DETAIL KETERANGAN SETIAP KETENTUAN KONDISI KENDARAAN
        </div>

        <div class="k3-footer-area">
            <div class="k3-comment-box">
                <div class="title">KOMENTAR :</div>
                @if(!empty($sheet['k3Notes']))
                    <div>{{ implode(' | ', array_slice($sheet['k3Notes'], 0, 4)) }}</div>
                @endif
            </div>

            <div class="k3-signs">
                <div class="k3-sign-col">
                    <div>Diperiksa Oleh,</div>
                    <div class="line">{{ $sheet['k3ExaminerName'] }}</div>
                </div>
                <div class="k3-sign-col">
                    <div>Diketahui Oleh,</div>
                    <div class="line">Shofa Nuzula Anamy</div>
                </div>
            </div>
        </div>

        <div class="k3-company-strip">
            <div class="k3-company-name">PT. TOYAMILINDO</div>
            <div class="k3-company-addr">
                Jl. Pangeran Cakrabuana, Desa Wanasaba Kidul, Talun, Cirebon, 45171<br>
                Tlp. 0231-321879, Fax.0231-321798, email : toyamilindo@yahoo.com<br>
                www.mountoya.co.id
            </div>
        </div>
    </div>
@empty
    <div class="sheet-page" style="text-align:center;padding:40px;">
        <h3 style="margin:0 0 8px 0;">Tidak Ada Data Armada</h3>
        <p style="margin:0;color:#64748b;">Tidak ditemukan armada atau riwayat checklist untuk periode {{ $bulan }}.</p>
    </div>
@endforelse
</div>

</body>
</html>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak QR Code Akses Mobile — TOYACHECK</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #F1F5F9;
            color: #0F1E36;
            padding: 24px 16px 48px;
            min-height: 100vh;
        }

        /* ── Screen Toolbar ── */
        .toolbar {
            max-width: 900px;
            margin: 0 auto 24px;
            background: #FFFFFF;
            padding: 16px 24px;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(15, 30, 54, 0.08);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            border: 1px solid #E2E8F0;
        }

        .toolbar-info h1 {
            font-size: 17px;
            font-weight: 800;
            color: #0F1E36;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .toolbar-info p {
            font-size: 12.5px;
            color: #64748B;
            margin-top: 3px;
        }

        .toolbar-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 18px;
            border-radius: 10px;
            font-size: 13.5px;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            border: none;
            transition: all 0.15s ease;
        }

        .btn-primary {
            background: #2563EB;
            color: #FFFFFF;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);
        }

        .btn-primary:hover {
            background: #1D4ED8;
        }

        .btn-secondary {
            background: #F1F5F9;
            color: #334155;
            border: 1px solid #CBD5E1;
        }

        .btn-secondary:hover {
            background: #E2E8F0;
        }

        .print-tip {
            max-width: 900px;
            margin: 0 auto 20px;
            background: #EFF6FF;
            border: 1px solid #BFDBFE;
            border-radius: 12px;
            padding: 10px 16px;
            font-size: 12px;
            color: #1E40AF;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* ── Sheet Container (A4 Simulation on screen) ── */
        .sheet-container {
            max-width: 900px;
            margin: 0 auto;
            background: #FFFFFF;
            padding: 24px;
            border-radius: 18px;
            box-shadow: 0 4px 24px rgba(15, 30, 54, 0.08);
            border: 1px solid #E2E8F0;
        }

        /* ── Document Official Header ── */
        .doc-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-bottom: 14px;
            border-bottom: 2px solid #0F1E36;
            margin-bottom: 20px;
        }

        .doc-header-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .doc-logo {
            height: 40px;
            width: auto;
            object-fit: contain;
        }

        .doc-title-main {
            font-size: 18px;
            font-weight: 800;
            color: #0F1E36;
            letter-spacing: 0.02em;
            line-height: 1.2;
        }

        .doc-subtitle-main {
            font-size: 11px;
            font-weight: 600;
            color: #64748B;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        .doc-meta {
            text-align: right;
            font-size: 11px;
            color: #64748B;
            line-height: 1.4;
        }

        .doc-meta strong {
            color: #0F1E36;
        }

        /* ── Sticker Grid (2x2 layout) ── */
        .sticker-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        /* ── Single Sticker Card ── */
        .sticker-card {
            background: #FFFFFF;
            border: 2px dashed #94A3B8;
            border-radius: 16px;
            padding: 20px 18px;
            text-align: center;
            position: relative;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        /* Scissor cut mark decoration */
        .cut-mark {
            position: absolute;
            top: -11px;
            right: 18px;
            background: #FFFFFF;
            padding: 0 8px;
            font-size: 11px;
            font-weight: 700;
            color: #94A3B8;
            display: flex;
            align-items: center;
            gap: 4px;
            font-family: monospace;
        }

        .cut-mark i {
            font-size: 12px;
        }

        /* Sticker Brand */
        .sticker-brand-row {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-bottom: 2px;
        }

        .sticker-logo {
            height: 24px;
            width: auto;
            object-fit: contain;
        }

        .sticker-brand-name {
            font-size: 17px;
            font-weight: 800;
            color: #0F1E36;
            font-style: italic;
            letter-spacing: 0.02em;
        }

        .sticker-tagline {
            font-size: 10.5px;
            color: #64748B;
            font-weight: 600;
            margin-bottom: 12px;
        }

        /* QR Frame */
        .qr-frame {
            background: #FFFFFF;
            border: 2px solid #E2E8F0;
            border-radius: 14px;
            padding: 10px;
            display: inline-flex;
            justify-content: center;
            align-items: center;
            margin: 0 auto 12px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.04);
        }

        .qr-img {
            width: 170px;
            height: 170px;
            display: block;
        }

        /* Badge Akses */
        .badge-access {
            display: inline-block;
            background: #0F1E36;
            color: #FFFFFF;
            font-size: 11.5px;
            font-weight: 800;
            letter-spacing: 0.06em;
            padding: 5px 14px;
            border-radius: 6px;
            margin-bottom: 8px;
            text-transform: uppercase;
        }

        /* Instructions */
        .sticker-instructions {
            background: #F8FAFC;
            border: 1px solid #E2E8F0;
            border-radius: 10px;
            padding: 10px 12px;
            text-align: left;
            font-size: 11px;
            line-height: 1.5;
            color: #334155;
            margin-bottom: 10px;
        }

        .instruction-title {
            font-weight: 800;
            color: #0F1E36;
            margin-bottom: 4px;
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 11px;
        }

        .instruction-step {
            display: flex;
            align-items: flex-start;
            gap: 6px;
            margin-bottom: 2px;
        }

        .step-num {
            background: #2563EB;
            color: #FFFFFF;
            width: 15px;
            height: 15px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 9.5px;
            font-weight: 700;
            flex-shrink: 0;
            margin-top: 1px;
        }

        /* URL Fallback */
        .url-box {
            font-size: 10px;
            color: #475569;
            font-family: 'JetBrains Mono', monospace;
            background: #FFFFFF;
            border: 1px dashed #CBD5E1;
            padding: 5px 8px;
            border-radius: 6px;
            word-break: break-all;
        }

        .url-box strong {
            color: #2563EB;
        }

        .sticker-footer-note {
            margin-top: 8px;
            font-size: 9.5px;
            color: #94A3B8;
            font-weight: 600;
        }

        /* ── Print Styles (A4 Page Fit) ── */
        @page {
            size: A4 portrait;
            margin: 10mm 10mm 10mm 10mm;
        }

        @media print {
            body {
                background: #FFFFFF !important;
                padding: 0 !important;
                margin: 0 !important;
            }

            .toolbar,
            .print-tip {
                display: none !important;
            }

            .sheet-container {
                max-width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                box-shadow: none !important;
                border: none !important;
            }

            .doc-header {
                padding-bottom: 10px;
                margin-bottom: 14px;
            }

            .sticker-grid {
                grid-template-columns: repeat(2, 1fr) !important;
                gap: 14px !important;
            }

            .sticker-card {
                border: 2px dashed #94A3B8 !important;
                padding: 16px 14px !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }

            .qr-img {
                width: 160px !important;
                height: 160px !important;
            }

            .badge-access {
                background: #0F1E36 !important;
                color: #FFFFFF !important;
            }

            .step-num {
                background: #2563EB !important;
                color: #FFFFFF !important;
            }
        }
    </style>
</head>
<body>

@php
    $universalUrl = route('mobile.login');
    $qrImageSrc   = 'https://api.qrserver.com/v1/create-qr-code/?size=350x350&margin=8&data=' . urlencode($universalUrl);
@endphp

{{-- Toolbar Layar --}}
<div class="toolbar">
    <div class="toolbar-info">
        <h1>
            <i class="fa-solid fa-qrcode" style="color:#2563EB"></i>
            Stiker QR Code Akses Mobile TOYACHECK
        </h1>
        <p>1 Lembar A4 berisi 4 stiker QR Code universal untuk ditempel di armada truk</p>
    </div>
    <div class="toolbar-actions">
        <a href="{{ route('vehicles.index') }}" class="btn btn-secondary">
            <i class="fa-solid fa-arrow-left"></i>
            Kembali
        </a>
        <button type="button" onclick="window.print()" class="btn btn-primary">
            <i class="fa-solid fa-print"></i>
            Cetak / Simpan PDF
        </button>
    </div>
</div>

<div class="print-tip">
    <i class="fa-solid fa-circle-info" style="font-size:14px"></i>
    <span><strong>Tips Cetak PDF:</strong> Di jendela cetak browser, centang opsi <strong>"Background graphics"</strong> agar warna badge dan pembatas tetap tajam dan jelas.</span>
</div>

{{-- Lembar Cetak A4 --}}
<div class="sheet-container">

    {{-- Header Dokumen Resmi --}}
    <div class="doc-header">
        <div class="doc-header-left">
            <img src="{{ asset('img/logo.png') }}" alt="TOYACHECK Logo" class="doc-logo" onerror="this.style.display='none'">
            <div>
                <div class="doc-title-main">TOYACHECK &bull; QR CODE AKSES MOBILE</div>
                <div class="doc-subtitle-main">Standard Operational Procedure — Fleet Mobile Access</div>
            </div>
        </div>
        <div class="doc-meta">
            Dokumen: <strong>STK-QR-UNIV</strong><br>
            Dicetak: <strong>{{ now()->format('d/m/Y') }}</strong>
        </div>
    </div>

    {{-- Grid 4 Stiker QR --}}
    <div class="sticker-grid">
        @for($i = 1; $i <= 4; $i++)
            <div class="sticker-card">
                <div class="cut-mark">
                    <i class="fa-solid fa-scissors"></i> GUNTING DI SINI
                </div>

                {{-- Header Stiker --}}
                <div>
                    <div class="sticker-brand-row">
                        <img src="{{ asset('img/logo.png') }}" alt="TOYACHECK Logo" class="sticker-logo" onerror="this.style.display='none'">
                        <span class="sticker-brand-name">TOYACHECK</span>
                    </div>
                    <div class="sticker-tagline">Integrated Fleet Inspection System</div>
                </div>

                {{-- Gambar QR Code --}}
                <div class="qr-frame">
                    <img src="{{ $qrImageSrc }}" alt="QR Code TOYACHECK Mobile" class="qr-img">
                </div>

                {{-- Badge Akses --}}
                <div>
                    <span class="badge-access">SCAN AKSES MOBILE</span>
                </div>

                {{-- Instruksi Sopir --}}
                <div class="sticker-instructions">
                    <div class="instruction-title">
                        <i class="fa-solid fa-circle-check" style="color:#2563EB"></i>
                        Petunjuk Driver / Pemeriksa:
                    </div>
                    <div class="instruction-step">
                        <span class="step-num">1</span>
                        <span>Scan QR Code ini menggunakan kamera HP.</span>
                    </div>
                    <div class="instruction-step">
                        <span class="step-num">2</span>
                        <span>Login dengan <strong>ID Karyawan &amp; Password</strong>.</span>
                    </div>
                    <div class="instruction-step">
                        <span class="step-num">3</span>
                        <span>Pilih armada truk Anda &amp; isi checklist.</span>
                    </div>
                </div>

                {{-- Fallback URL Manual --}}
                <div class="url-box">
                    Atau buka browser: <strong>{{ $universalUrl }}</strong>
                </div>

                <div class="sticker-footer-note">
                    <i class="fa-solid fa-location-dot"></i> Tempelkan di dashboard / kaca kabin driver truk
                </div>
            </div>
        @endfor
    </div>

</div>

</body>
</html>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Standar Operasional Prosedur — TOYACHECK</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
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
            background: #F1F5F9;
            padding: 24px;
            font-size: 12.5px;
            line-height: 1.6;
        }

        .no-print {
            max-width: 840px;
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
            flex-wrap: wrap;
        }

        .preview-label {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 13px;
            font-weight: 600;
            color: #334155;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 9px 18px;
            border-radius: 8px;
            font-size: 12.5px;
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
        .btn-primary:hover {
            background: #071F60;
        }

        .btn-secondary {
            background: #F1F5F9;
            color: #334155;
            border: 1px solid #CBD5E1;
        }
        .btn-secondary:hover {
            background: #E2E8F0;
        }

        .print-sheet {
            max-width: 840px;
            margin: 0 auto;
            background: #FFFFFF;
            border-radius: 12px;
            padding: 44px 52px;
            box-shadow: 0 4px 20px rgba(15, 30, 54, 0.06);
            border: 1px solid #E2E8F0;
        }

        .doc-title {
            text-align: center;
            font-size: 20px;
            font-weight: 800;
            color: #0F1E36;
            padding-bottom: 16px;
            margin-bottom: 26px;
            border-bottom: 2px solid #0F1E36;
        }

        .role-section {
            margin-bottom: 22px;
            page-break-inside: avoid;
        }

        .role-section:last-child {
            margin-bottom: 0;
        }

        .role-heading {
            font-size: 14.5px;
            font-weight: 700;
            color: #0B2E8A;
            margin-bottom: 8px;
            padding-bottom: 4px;
            border-bottom: 1px solid #E2E8F0;
        }

        .sop-list {
            padding-left: 22px;
            margin: 0;
        }

        .sop-list li {
            padding: 3px 0 3px 4px;
            color: #1E293B;
            text-align: justify;
        }

        .empty-note {
            font-size: 12.5px;
            color: #64748B;
            font-style: italic;
            padding: 4px 0;
        }

        @page {
            size: A4 portrait;
            margin: 18mm 18mm;
        }

        @media print {
            body {
                background: #FFFFFF;
                padding: 0;
            }
            .no-print {
                display: none !important;
            }
            .print-sheet {
                max-width: 100%;
                box-shadow: none;
                border: none;
                border-radius: 0;
                padding: 0;
            }
        }
    </style>
</head>
<body>

    {{-- Toolbar Preview Sebelum Cetak --}}
    <div class="no-print">
        <div class="preview-label">
            <i class="fa-solid fa-eye" style="color:#0B2E8A;"></i>
            <span>Preview Dokumen SOP (Siap Cetak / Simpan PDF)</span>
        </div>

        <div style="display:flex;gap:8px;align-items:center;">
            <a href="{{ route('sop.index', $roleFilter ? ['role' => $roleFilter] : []) }}" class="btn btn-secondary">
                <i class="fa-solid fa-arrow-left"></i>
                Kembali ke Menu SOP
            </a>
            <button type="button" class="btn btn-primary" onclick="window.print()">
                <i class="fa-solid fa-print"></i>
                Cetak / Simpan PDF
            </button>
        </div>
    </div>

    {{-- Lembar Dokumen SOP (Sesuai Isi Dokumen Word) --}}
    <div class="print-sheet">
        <h1 class="doc-title">Standar Operasional Prosedur</h1>

        @foreach($visibleRoles as $roleKey => $roleLabel)
            @php $items = $sops->get($roleKey, collect()); @endphp
            <section class="role-section">
                <h2 class="role-heading">{{ $roleLabel }}</h2>

                @if($items->isNotEmpty())
                    <ol class="sop-list">
                        @foreach($items as $item)
                            <li>{{ $item->isi_prosedur }}</li>
                        @endforeach
                    </ol>
                @else
                    <p class="empty-note">Belum ada butir prosedur untuk bagian ini.</p>
                @endif
            </section>
        @endforeach
    </div>

</body>
</html>

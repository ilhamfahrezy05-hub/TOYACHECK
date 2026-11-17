@extends('layouts.app')

@php
    $prCode = '#PR-' . (8820 + $sparepartRequest->id);
    $abc    = $sparepartRequest->sparepart->kategori_abc ?? 'A';
    $isHigh = in_array(strtolower($sparepartRequest->priority_level ?? ''), ['critical', 'urgent', 'tinggi']);
@endphp

@section('title', 'Detail Permintaan ' . $prCode)
@section('page-title', 'Detail Permintaan Pengadaan')
@section('page-subtitle', 'Validasi spesifikasi dan tahapan pengadaan suku cadang armada')

@section('page-actions')
    <a href="{{ route('pengadaan.requests', ['selected' => $sparepartRequest->id]) }}" class="btn btn-secondary btn-sm">
        <i class="fa-solid fa-arrow-left"></i> Kembali ke Daftar
    </a>
@endsection

@section('content')

<div style="max-width:960px">

    {{-- 1. TOP SUMMARY BANNER (FIGMA 918:26597) --}}
    <div style="background:#E2E8F0;border-radius:8px;padding:20px 24px;display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:20px;max-width:480px">
        <div>
            <div style="font-size:13px;font-weight:900;color:#0F172A;margin-bottom:4px">{{ $prCode }}</div>
            <div style="font-size:14px;font-weight:700;color:#1E293B">{{ $sparepartRequest->nama_sparepart }} (x{{ $sparepartRequest->qty }})</div>
            <div style="font-size:11.5px;color:#475569;margin-top:2px">
                Klasifikasi {{ $abc }} &bull; {{ $sparepartRequest->created_at->diffForHumans() }}
            </div>
        </div>

        @if($sparepartRequest->status === 'approved')
            <span style="background:#DCFCE7;color:#15803D;font-size:10px;font-weight:800;padding:4px 10px;border-radius:4px">
                DISETUJUI
            </span>
        @elseif($sparepartRequest->status === 'rejected')
            <span style="background:#FEE2E2;color:#B91C1C;font-size:10px;font-weight:800;padding:4px 10px;border-radius:4px">
                DITOLAK
            </span>
        @else
            <span style="background:#FEF3C7;color:#B45309;font-size:10px;font-weight:800;padding:4px 10px;border-radius:4px">
                MENUNGGU PERSETUJUAN
            </span>
        @endif
    </div>

    {{-- 2. SUB-HEADER BAR + TOMBOL KEPUTUSAN --}}
    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:14px;margin-bottom:20px">
        <div>
            <div style="font-size:14px;font-weight:800;color:#0F172A">Permintaan #{{ $sparepartRequest->id }}</div>
            <div style="font-size:12px;color:#64748B">
                Diserahkan pada {{ $sparepartRequest->created_at->translatedFormat('d M Y') }} &bull; {{ $sparepartRequest->created_at->format('H:i') }} WIB
            </div>
        </div>

        <div class="d-flex align-center gap-12">
            @if($sparepartRequest->isPending())
                <form method="POST" action="{{ route('pengadaan.requests.reject', $sparepartRequest->id) }}">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn" style="background:#FFFFFF;border:1.5px solid #FCA5A5;color:#DC2626;font-weight:700;padding:8px 22px">
                        <i class="fa-solid fa-ban"></i> Ditolak
                    </button>
                </form>

                <form method="POST" action="{{ route('pengadaan.requests.approve', $sparepartRequest->id) }}">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn" style="background:#090D16;color:#FFFFFF;font-weight:700;padding:8px 22px">
                        <i class="fa-solid fa-check"></i> Diterima
                    </button>
                </form>
            @else
                <a href="{{ route('pengadaan.reports.create', ['request_id' => $sparepartRequest->id]) }}"
                   class="btn" style="background:#090D16;color:#FFFFFF;font-weight:700;padding:9px 20px">
                    <i class="fa-solid fa-file-invoice-dollar"></i> Catat Pembelian &amp; Upload Nota
                </a>
            @endif
        </div>
    </div>

    {{-- 3. KARTU INFORMASI UMUM --}}
    <div class="card" style="padding:22px 26px;margin-bottom:20px">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px">
            <div style="font-size:14px;font-weight:800;color:#0F172A">Informasi umum</div>
            <span style="background:#FEF3C7;color:#B45309;font-size:10px;font-weight:800;padding:4px 12px;border-radius:4px">
                {{ $sparepartRequest->status === 'approved' ? 'DISETUJUI' : ($sparepartRequest->status === 'rejected' ? 'DITOLAK' : 'MENUNGGU STATUS') }}
            </span>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px">
            <div>
                <div style="font-size:10.5px;font-weight:700;color:#94A3B8;text-transform:uppercase;margin-bottom:4px">DEPARTEMEN</div>
                <div style="font-size:13.5px;font-weight:800;color:#0F172A">
                    Mekanik - kualifikasi {{ $abc }} ({{ $sparepartRequest->requester->name ?? 'Workshop' }})
                </div>
            </div>

            <div>
                <div style="font-size:10.5px;font-weight:700;color:#94A3B8;text-transform:uppercase;margin-bottom:4px">LEVEL PRIORITAS</div>
                <div style="font-size:13.5px;font-weight:800;color:{{ $isHigh ? '#DC2626' : '#0F172A' }};display:flex;align-items:center;gap:6px">
                    <span style="width:7px;height:7px;border-radius:50%;background:{{ $isHigh ? '#DC2626' : '#2563EB' }}"></span>
                    {{ $isHigh ? 'Urgensi tinggi' : 'Standar Operasional' }}
                </div>
            </div>
        </div>
    </div>

    {{-- 4. KARTU TAHAPAN PENGADAAN (STEPPER 3 TAHAP FIGMA 918:26597) --}}
    <div class="card" style="padding:24px 26px">
        <div style="font-size:14px;font-weight:800;color:#0F172A;margin-bottom:20px">Tahapan pengadaan</div>

        {{-- Step 1: Validasi Permintaan --}}
        <div style="display:flex;justify-content:space-between;align-items:flex-start;padding-bottom:22px;position:relative">
            <div style="display:flex;gap:14px">
                <div style="width:12px;height:12px;border-radius:50%;background:#16A34A;margin-top:4px;flex-shrink:0"></div>
                <div>
                    <div style="font-size:13.5px;font-weight:800;color:#0F172A">Validasi permintaan</div>
                    <div style="font-size:12px;color:#64748B;margin-top:2px">
                        @if($sparepartRequest->isPending())
                            menunggu keputusan anda menerima atau menolak
                        @else
                            Keputusan telah diverifikasi oleh {{ $sparepartRequest->reviewer->name ?? 'Pengadaan' }}
                        @endif
                    </div>
                </div>
            </div>
            <div style="font-size:11.5px;color:#64748B;font-weight:600">
                {{ ($sparepartRequest->reviewed_at ?? $sparepartRequest->created_at)->translatedFormat('d M Y / H:i') }} WIB
            </div>
        </div>

        {{-- Step 2: Pembelanjaan Barang --}}
        <div style="display:flex;justify-content:space-between;align-items:flex-start;padding-bottom:22px">
            <div style="display:flex;gap:14px">
                <div style="width:12px;height:12px;border-radius:50%;background:#16A34A;margin-top:4px;flex-shrink:0"></div>
                <div>
                    <div style="font-size:13.5px;font-weight:800;color:#0F172A">Pembelanjaan barang</div>
                    <div style="font-size:12px;color:#64748B;margin-top:2px">
                        Segera melakukan pengadaan barang pada vendor rekanan ({{ $sparepartRequest->vendor_name ?: 'Mitra Otomotif' }})
                    </div>
                </div>
            </div>
        </div>

        {{-- Step 3: Catat Pembelian & Serah Terima --}}
        <div style="display:flex;justify-content:space-between;align-items:flex-start">
            <div style="display:flex;gap:14px">
                <div style="width:12px;height:12px;border-radius:50%;background:{{ $linkedPurchase ? '#16A34A' : '#F59E0B' }};margin-top:4px;flex-shrink:0"></div>
                <div>
                    <div style="font-size:13.5px;font-weight:800;color:{{ $linkedPurchase ? '#0F172A' : '#D97706' }}">Catat pembelian &amp; serah terima</div>
                    <div style="font-size:12px;color:#64748B;margin-top:2px">
                        @if($linkedPurchase)
                            Transaksi tercatat ({{ $linkedPurchase->kode_pembelian }} — Rp {{ number_format($linkedPurchase->total_harga, 0, ',', '.') }})
                        @else
                            Dokumentasi dan konfirmasi nota pembelanjaan
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

@endsection

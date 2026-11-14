@extends('driver.layout')

@section('title', 'Notifikasi & Panduan — TOYACHECK')
@section('back_url', route('driver.index'))
@section('header_title', 'Notifikasi & Manual')

@push('styles')
<style>
    .m-notif-list {
        padding: 14px 18px 24px;
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .m-notif-card {
        background: #F5F6F8;
        border: 1px solid #E5E7EB;
        border-radius: 14px;
        padding: 13px 15px;
        text-decoration: none;
        color: #111827;
        display: flex;
        align-items: flex-start;
        gap: 12px;
    }

    .m-manual-box {
        margin: 14px 18px 4px;
        background: linear-gradient(135deg, #EFF6FF 0%, #DBEAFE 100%);
        border: 1px solid #BFDBFE;
        border-radius: 16px;
        padding: 16px;
    }
</style>
@endpush

@section('content')

{{-- Halaman Manual Singkat sesuai Frame "Halaman Manual" di Figma --}}
<div class="m-manual-box">
    <div style="font-size:13.5px;font-weight:800;color:#1E3A8A;margin-bottom:8px">
        <i class="fa-solid fa-book-open" style="margin-right:6px"></i>
        SOP & Panduan Operasional Driver
    </div>
    <ol style="padding-left:18px;font-size:11.5px;color:#1E40AF;line-height:1.6">
        <li>Scan <strong>QR Code</strong> yang tertempel di armada sebelum memulai perjalanan.</li>
        <li>Lakukan <strong>Cek Fisik</strong> untuk memastikan mesin, rem, ban, dan lampu aman.</li>
        <li>Lakukan <strong>Cek K3 & Kebersihan</strong> untuk memastikan kabin dan bak bebas kontaminasi (diaudit oleh HSE).</li>
        <li>Jika ditemukan kerusakan, lampirkan foto bukti pada item terkait agar segera ditangani Mekanik.</li>
    </ol>
</div>

<div style="padding:12px 20px 0;font-size:14px;font-weight:700;color:#111827">
    Aktivitas & Notifikasi Terbaru
</div>

<div class="m-notif-list">
    @forelse($recentAlerts as $alert)
        @php
            $badItems = $alert->items->pluck('item')->join(', ');
        @endphp
        <a href="{{ route('driver.history.detail', $alert->id) }}" class="m-notif-card">
            <div style="width:36px;height:36px;border-radius:10px;background:{{ $alert->status === 'kendala' ? '#FEE2E2' : '#DCFCE7' }};color:{{ $alert->status === 'kendala' ? '#DC2626' : '#16A34A' }};display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:16px">
                <i class="fa-solid {{ $alert->status === 'kendala' ? 'fa-triangle-exclamation' : 'fa-circle-check' }}"></i>
            </div>
            <div style="flex:1;min-width:0">
                <div style="font-size:12.5px;font-weight:700;color:#111827">
                    {{ $alert->vehicle->plat_nomor ?? 'Armada' }} — {{ strtoupper($alert->checklist_type) }}
                </div>
                <div style="font-size:11px;color:#4B5563;margin-top:2px">
                    @if($alert->status === 'kendala')
                        Temuan kendala: <strong>{{ $badItems ?: 'Perlu pengecekan' }}</strong>
                    @else
                        Semua komponen dinyatakan aman & layak jalan.
                    @endif
                </div>
                <div style="font-size:10px;color:#6B7280;margin-top:4px">
                    {{ $alert->tanggal->format('d M Y, H:i') }} WIB
                </div>
            </div>
        </a>
    @empty
        <div style="text-align:center;padding:32px 20px;color:#6B7280;font-size:12.5px">
            Belum ada notifikasi terbaru.
        </div>
    @endforelse
</div>

@endsection


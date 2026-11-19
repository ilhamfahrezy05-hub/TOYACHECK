@extends('driver.layout')

@section('title', 'Detail Riwayat — ' . ($checklist->vehicle->plat_nomor ?? ''))
@section('back_url', url()->previous() !== url()->current() ? url()->previous() : route('driver.history'))
@section('header_title', 'Riwayat')

@push('styles')
<style>
    .m-detail-hero {
        padding: 18px 20px 14px;
        display: flex;
        align-items: center;
        gap: 14px;
        background: #FFFFFF;
        border-bottom: 1px solid #E5E7EB;
    }

    .m-detail-item {
        background: linear-gradient(180deg, #EBECEE 0%, #D4D6DA 100%);
        border-radius: 14px;
        padding: 12px 15px;
        box-shadow: 0 3px 7px rgba(0,0,0,0.12);
        border-left: 5px solid #22C55E;
    }

    .m-detail-item.bad {
        border-left-color: #DC2626;
        background: linear-gradient(180deg, #FEF2F2 0%, #FEE2E2 100%);
    }
</style>
@endpush

@section('content')

@php
    $hariMap = [
        'Sunday' => 'minggu', 'Monday' => 'senin', 'Tuesday' => 'selasa',
        'Wednesday' => 'rabu', 'Thursday' => 'kamis', 'Friday' => 'jumat', 'Saturday' => 'sabtu',
    ];
    $bulanMap = [
        1 => 'januari', 2 => 'februari', 3 => 'maret', 4 => 'april',
        5 => 'mei', 6 => 'juni', 7 => 'juli', 8 => 'agustus',
        9 => 'september', 10 => 'oktober', 11 => 'november', 12 => 'desember',
    ];
    $hari  = $hariMap[$checklist->tanggal->format('l')] ?? $checklist->tanggal->format('l');
    $bulan = $bulanMap[(int) $checklist->tanggal->format('n')] ?? $checklist->tanggal->format('F');
    $tglFormatted = $hari . ', ' . $checklist->tanggal->format('d') . ' ' . $bulan . ' ' . $checklist->tanggal->format('Y');
@endphp

<div class="m-detail-hero">
    @include('driver.partials.truck-illust', ['vehicle' => $checklist->vehicle, 'size' => 'md'])

    <div style="flex:1;min-width:0">
        <div style="font-size:11.5px;font-weight:700;color:#111827">{{ $tglFormatted }}</div>
        <div style="font-size:15px;font-weight:800;color:#111827;margin:2px 0 6px">
            {{ $checklist->vehicle->plat_nomor ?? '-' }}
        </div>
        <div style="font-size:11px;color:#4B5563">
            Petugas: <strong>{{ $checklist->driver->name ?? '-' }}</strong><br>
            Kategori: <strong>{{ $checklist->checklist_type === 'k3' ? 'Cek K3 & Kebersihan' : 'Cek Fisik Kendaraan' }}</strong>
        </div>
    </div>
</div>

{{-- Aksi Khusus Role K3/HSE: Edit (Ganti Laporan) & Hapus Laporan K3 --}}
@role('hse')
@if($checklist->checklist_type === 'k3')
<div style="margin:12px 18px 0;display:flex;gap:10px">
    <a href="{{ route('driver.history.edit', $checklist->id) }}"
       style="flex:1;display:inline-flex;align-items:center;justify-content:center;gap:6px;background:#071843;color:#FFFFFF;font-size:12px;font-weight:700;padding:10px 12px;border-radius:10px;text-decoration:none;box-shadow:0 3px 8px rgba(7,24,67,0.2)">
        <i class="fa-solid fa-pen-to-square"></i>
        Edit / Ganti Laporan K3
    </a>

    <form action="{{ route('driver.history.destroy', $checklist->id) }}"
          method="POST"
          onsubmit="return confirm('Apakah Anda yakin ingin menghapus laporan K3 tanggal {{ $tglFormatted }} ini?');"
          style="margin:0">
        @csrf
        @method('DELETE')
        <button type="submit"
                style="height:100%;display:inline-flex;align-items:center;justify-content:center;gap:6px;background:#FEE2E2;color:#BA1A1A;border:1px solid #FCA5A5;font-size:12px;font-weight:700;padding:10px 14px;border-radius:10px;cursor:pointer">
            <i class="fa-solid fa-trash-can"></i>
            Hapus
        </button>
    </form>
</div>
@endif
@endrole

@if($checklist->status === 'perbaikan')
    <div style="margin:14px 18px 0;padding:12px 14px;background:#FEF3C7;border:1.5px solid #F59E0B;border-radius:12px;font-size:12px;color:#92400E">
        <div style="font-weight:800;margin-bottom:4px">
            <i class="fa-solid fa-screwdriver-wrench" style="margin-right:4px"></i>
            Status Pemeriksaan: Dalam Perbaikan
        </div>
        <div>Seluruh komponen pemeriksaan dikunci karena unit sedang dalam perbaikan.</div>
    </div>
@endif

@if($checklist->catatan)
    <div style="margin:14px 18px 0;padding:12px 14px;background:#EFF6FF;border:1px solid #BFDBFE;border-radius:12px;font-size:12px;color:#1E3A8A">
        <strong>Catatan Keterangan Petugas:</strong><br>
        {{ $checklist->catatan }}
    </div>
@endif

<div style="padding:14px 18px 24px;display:flex;flex-direction:column;gap:12px">
    @foreach($checklist->items as $item)
        <div class="m-detail-item {{ !$item->kondisi ? 'bad' : '' }}">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:10px">
                <div>
                    <div style="font-size:12.5px;font-weight:700;color:#111827">{{ $item->item }}</div>
                    <div style="font-size:10.5px;color:#374151;margin-top:2px">{{ $item->criteria ?? '-' }}</div>
                </div>
                <span style="font-size:10.5px;font-weight:800;padding:3px 9px;border-radius:999px;background:{{ $item->kondisi ? '#DCFCE7' : '#FEE2E2' }};color:{{ $item->kondisi ? '#166534' : '#991B1B' }};white-space:nowrap">
                    {{ $item->kondisi ? 'Aman' : 'Kendala' }}
                </span>
            </div>

            @if($item->foto_dokumentasi)
                <div style="margin-top:10px">
                    <a href="{{ asset('storage/' . $item->foto_dokumentasi) }}" target="_blank">
                        <img src="{{ asset('storage/' . $item->foto_dokumentasi) }}"
                             alt="Foto Dokumentasi"
                             style="width:100%;max-height:160px;object-fit:cover;border-radius:10px;border:1px solid #D1D5DB">
                    </a>
                </div>
            @endif
        </div>
    @endforeach
</div>

@endsection


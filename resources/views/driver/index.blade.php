@extends('driver.layout')

@section('title', 'Pilih Kendaraan Anda — TOYACHECK')

@push('styles')
<style>
    .m-section-heading {
        padding: 12px 22px 10px;
        font-size: 15px;
        font-weight: 700;
        color: #111827;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .m-vehicle-list {
        padding: 4px 20px 24px;
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    /* Kartu Kendaraan Abu-abu Halus dengan Drop Shadow persis seperti di Figma */
    .m-vehicle-card {
        background: linear-gradient(180deg, #F5F6F8 0%, #EAECEF 100%);
        border-radius: 16px;
        padding: 14px 16px;
        display: flex;
        align-items: center;
        gap: 14px;
        text-decoration: none;
        color: #111827;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.12);
        border: 1px solid rgba(255, 255, 255, 0.8);
        transition: transform 0.16s ease, box-shadow 0.16s ease;
        position: relative;
    }

    .m-vehicle-card:hover,
    .m-vehicle-card:active {
        transform: translateY(-2px);
        box-shadow: 0 7px 16px rgba(0, 0, 0, 0.16);
    }

    .m-vehicle-info {
        flex: 1;
        min-width: 0;
    }

    .m-vehicle-title {
        font-size: 13.5px;
        font-weight: 700;
        color: #111827;
        line-height: 1.25;
    }

    .m-vehicle-plat {
        font-size: 13.5px;
        font-weight: 800;
        color: #111827;
        margin-bottom: 6px;
        letter-spacing: 0.02em;
    }

    .m-vehicle-meta {
        font-size: 9.5px;
        color: #374151;
        line-height: 1.45;
        display: flex;
        flex-direction: column;
    }

    .m-status-dot {
        position: absolute;
        bottom: 12px;
        right: 12px;
        width: 9px;
        height: 9px;
        border-radius: 50%;
    }
    .m-status-dot.siap_jalan  { background: #22C55E; box-shadow: 0 0 0 3px rgba(34,197,94,0.2); }
    .m-status-dot.maintenance { background: #F59E0B; box-shadow: 0 0 0 3px rgba(245,158,11,0.2); }
    .m-status-dot.rusak       { background: #EF4444; box-shadow: 0 0 0 3px rgba(239,68,68,0.2); }
</style>
@endpush

@section('content')

{{-- Search Bar Bersih sesuai Figma --}}
<div class="m-search-wrap">
    <form action="{{ route('driver.index') }}" method="GET" class="m-search-box" id="mobileSearchForm">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input
            type="text"
            name="q"
            id="mobileVehicleSearch"
            value="{{ $search }}"
            class="m-search-input"
            placeholder="Cari kendaraan..."
            oninput="filterMobileVehicles(this.value)">
    </form>
</div>

<div class="m-section-heading">
    <span>{{ auth()->check() && auth()->user()->hasRole('hse') ? 'Pilih Kendaraan' : 'Pilih Kendaraan Anda' }}</span>
    @role('hse')
    <a href="{{ route('driver.k3-items') }}"
       style="font-size:11px;font-weight:700;color:#FFFFFF;background:#071843;padding:5px 11px;border-radius:999px;text-decoration:none;display:inline-flex;align-items:center;gap:5px">
        <i class="fa-solid fa-pen" style="font-size:9.5px"></i>
        Edit Data K3
    </a>
    @endrole
</div>

{{-- Daftar Kartu Kendaraan persis sesuai Figma --}}
<div class="m-vehicle-list" id="mobileVehicleList">
    @forelse($vehicles as $vehicle)
        <a href="{{ route('driver.vehicle.show', $vehicle->id) }}"
           class="m-vehicle-card"
           data-search="{{ strtolower($vehicle->plat_nomor . ' ' . $vehicle->merk . ' ' . $vehicle->jenis_kendaraan) }}">

            <span class="m-status-dot {{ $vehicle->status }}" title="Status: {{ \App\Models\Vehicle::STATUSES[$vehicle->status] ?? $vehicle->status }}"></span>

            {{-- Ilustrasi Truk di Kiri --}}
            @include('driver.partials.truck-illust', ['vehicle' => $vehicle, 'size' => 'md'])

            {{-- Detail Kendaraan di Kanan --}}
            <div class="m-vehicle-info">
                <div class="m-vehicle-title">
                    {{ $vehicle->merk }} {{ str_contains(strtolower($vehicle->merk), 'box') ? '' : $vehicle->jenis_kendaraan }}
                </div>
                <div class="m-vehicle-plat">{{ strtoupper($vehicle->plat_nomor) }}</div>

                <div class="m-vehicle-meta">
                    <span>Jumlah servis berkala : <strong>{{ $vehicle->servis_count }}</strong></span>
                    <span>Jumlah kendala : <strong>{{ $vehicle->kendala_count }}</strong></span>
                </div>
            </div>
        </a>
    @empty
        <div style="text-align:center;padding:48px 20px;color:#6B7280">
            <i class="fa-solid fa-truck-fast" style="font-size:32px;margin-bottom:10px;opacity:0.4"></i>
            <div style="font-size:14px;font-weight:700;color:#374151">Kendaraan Tidak Ditemukan</div>
            <div style="font-size:12px;margin-top:4px">Tidak ada armada aktif yang cocok dengan pencarian Anda.</div>
        </div>
    @endforelse
</div>

@endsection

@push('scripts')
<script>
    function filterMobileVehicles(query) {
        var q = (query || '').toLowerCase().trim();
        var cards = document.querySelectorAll('#mobileVehicleList .m-vehicle-card');
        cards.forEach(function (card) {
            var text = card.getAttribute('data-search') || '';
            card.style.display = (!q || text.includes(q)) ? 'flex' : 'none';
        });
    }
</script>
@endpush

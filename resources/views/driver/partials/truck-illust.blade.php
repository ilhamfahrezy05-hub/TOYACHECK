{{-- Partial Ilustrasi Truk Armada sesuai jenis kendaraan & Figma --}}
@php
    $jenisLower = strtolower(($vehicle->jenis_kendaraan ?? '') . ' ' . ($vehicle->merk ?? ''));
    $isGranmax  = str_contains($jenisLower, 'granmax') || str_contains($jenisLower, 'pickup') || str_contains($jenisLower, 'blind');
    $isOpenBak  = str_contains($jenisLower, 'bak') || str_contains($jenisLower, 'double') || str_contains($jenisLower, 'tronton');
    $size       = $size ?? 'md'; // sm, md, lg
    $widthMap   = ['sm' => '96px', 'md' => '115px', 'lg' => '175px'];
    $svgWidth   = $widthMap[$size] ?? '115px';
@endphp

<div class="truck-illust-wrap" style="width:{{ $svgWidth }};flex-shrink:0;display:flex;align-items:center;justify-content:center;position:relative">
    @if($isGranmax)
        {{-- Ilustrasi Granmax / Pickup Box Putih sesuai Figma --}}
        <svg viewBox="0 0 220 150" style="width:100%;height:auto;filter:drop-shadow(0 6px 10px rgba(0,0,0,0.18))">
            <ellipse cx="110" cy="136" rx="88" ry="8" fill="rgba(0,0,0,0.18)"/>
            <!-- Box Belakang Putih -->
            <polygon points="85,20 202,28 202,102 85,105" fill="#F8FAFC" stroke="#CBD5E1" stroke-width="2"/>
            <polygon points="62,28 85,20 85,105 62,98" fill="#E2E8F0" stroke="#CBD5E1" stroke-width="1.5"/>
            <text x="142" y="62" font-family="Inter,sans-serif" font-size="8" font-weight="800" fill="#1E3A8A" text-anchor="middle" opacity="0.75">MOUNTOYA</text>
            <!-- Kabin Depan Putih -->
            <path d="M26,75 L46,42 L85,40 L85,112 L20,112 L20,84 Z" fill="#FFFFFF" stroke="#CBD5E1" stroke-width="2"/>
            <!-- Kaca Depan -->
            <polygon points="31,73 48,47 76,46 76,73" fill="#1E293B"/>
            <!-- Grille & Lampu -->
            <rect x="20" y="86" width="24" height="10" rx="2" fill="#334155"/>
            <rect x="21" y="82" width="10" height="6" rx="1" fill="#FEF08A"/>
            <!-- Bumper -->
            <rect x="17" y="102" width="68" height="10" rx="3" fill="#1E293B"/>
            <!-- Plat Nomor Kecil -->
            <rect x="26" y="104" width="24" height="6" rx="1" fill="#0F172A"/>
            <text x="38" y="109" font-family="monospace" font-size="4.5" font-weight="700" fill="#FFFFFF" text-anchor="middle">{{ $vehicle->plat_nomor ?? 'E 8325 KI' }}</text>
            <!-- Roda Depan & Belakang -->
            <circle cx="64" cy="116" r="16" fill="#1E293B"/>
            <circle cx="64" cy="116" r="8" fill="#94A3B8"/>
            <circle cx="164" cy="114" r="16" fill="#1E293B"/>
            <circle cx="164" cy="114" r="8" fill="#94A3B8"/>
        </svg>
    @else
        {{-- Ilustrasi Toyota Dyna Box / Truk Biru Mountoya sesuai Figma --}}
        <svg viewBox="0 0 230 160" style="width:100%;height:auto;filter:drop-shadow(0 8px 12px rgba(0,0,0,0.22))">
            <ellipse cx="115" cy="144" rx="95" ry="9" fill="rgba(0,0,0,0.22)"/>
            <!-- Box Kargo Aluminium / Putih -->
            <polygon points="18,16 118,12 118,105 18,98" fill="#F1F5F9" stroke="#94A3B8" stroke-width="2"/>
            <polygon points="118,12 145,22 145,106 118,105" fill="#CBD5E1" stroke="#94A3B8" stroke-width="1.5"/>
            <line x1="50" y1="15" x2="50" y2="100" stroke="#CBD5E1" stroke-width="1"/>
            <line x1="84" y1="13" x2="84" y2="102" stroke="#CBD5E1" stroke-width="1"/>
            <rect x="28" y="36" width="46" height="22" rx="3" fill="#2563EB" opacity="0.12"/>
            <text x="51" y="49" font-family="Inter,sans-serif" font-size="7.5" font-weight="800" fill="#1D4ED8" text-anchor="middle">MOUNTOYA</text>

            <!-- Roda Belakang -->
            <circle cx="52" cy="116" r="17" fill="#0F172A"/>
            <circle cx="52" cy="116" r="8" fill="#64748B"/>

            <!-- Kabin Truk Biru (Toyota Dyna / Hino Dutro Style) -->
            <path d="M98,42 L168,38 C178,38 186,44 192,56 L206,86 L208,118 L98,118 Z" fill="#1D4ED8" stroke="#1E3A8A" stroke-width="2"/>
            <!-- Pintu Samping Kabin -->
            <path d="M104,48 L142,46 L146,108 L104,108 Z" fill="#2563EB" stroke="#1E40AF" stroke-width="1.2"/>
            <!-- Stiker Putih di Pintu -->
            <rect x="110" y="82" width="26" height="10" rx="2" fill="#FFFFFF"/>
            <text x="123" y="89" font-family="Inter,sans-serif" font-size="4.8" font-weight="800" fill="#1D4ED8" text-anchor="middle">TOYA</text>

            <!-- Kaca Samping -->
            <polygon points="108,52 138,50 140,76 108,76" fill="#0F172A"/>
            <!-- Kaca Depan (Windshield) -->
            <polygon points="148,47 184,47 198,76 150,76" fill="#1E293B" stroke="#0F172A" stroke-width="1.5"/>
            <!-- Spion Hitam -->
            <rect x="142" y="54" width="5" height="16" rx="2" fill="#0F172A"/>
            <rect x="196" y="52" width="5" height="15" rx="2" fill="#0F172A"/>

            <!-- Grille & Lampu Depan -->
            <rect x="158" y="92" width="44" height="12" rx="2" fill="#0F172A"/>
            <rect x="154" y="90" width="13" height="10" rx="2" fill="#F8FAFC" stroke="#94A3B8"/>
            <rect x="196" y="90" width="10" height="10" rx="2" fill="#FDE047"/>

            <!-- Bumper Depan + Plat Nomor -->
            <rect x="148" y="106" width="64" height="13" rx="3" fill="#1E293B"/>
            <rect x="172" y="108" width="30" height="9" rx="1.5" fill="#FFFFFF" stroke="#0F172A" stroke-width="1"/>
            <text x="187" y="114.5" font-family="monospace" font-size="5.2" font-weight="800" fill="#0F172A" text-anchor="middle">{{ $vehicle->plat_nomor ?? 'E 8544 KI' }}</text>

            <!-- Roda Depan -->
            <circle cx="128" cy="122" r="19" fill="#0F172A"/>
            <circle cx="128" cy="122" r="10" fill="#1D4ED8"/>
            <circle cx="128" cy="122" r="5" fill="#94A3B8"/>
        </svg>
    @endif
</div>


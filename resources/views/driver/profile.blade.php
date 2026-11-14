@extends('driver.layout')

@section('title', 'Informasi Akun — TOYACHECK Mobile')
@section('back_url', route('driver.index'))
@section('header_title', 'Informasi Akun')

@push('styles')
<style>
    .m-profile-hero {
        padding: 24px 20px;
        background: linear-gradient(135deg, #0B2E8A 0%, #0F1E36 100%);
        color: #FFFFFF;
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
    }

    .m-profile-avatar {
        width: 78px;
        height: 78px;
        border-radius: 50%;
        border: 3px solid #FFFFFF;
        object-fit: cover;
        margin-bottom: 12px;
        box-shadow: 0 4px 14px rgba(0,0,0,0.25);
    }

    .m-stat-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
        padding: 16px 20px 8px;
    }

    .m-stat-box {
        background: #F5F6F8;
        border: 1px solid #E5E7EB;
        border-radius: 14px;
        padding: 14px;
        text-align: center;
    }

    .m-form-card {
        margin: 12px 20px;
        background: #FFFFFF;
        border: 1px solid #E5E7EB;
        border-radius: 16px;
        padding: 16px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    }

    .m-input {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #D1D5DB;
        border-radius: 10px;
        font-size: 13px;
        font-family: inherit;
        margin-top: 4px;
        margin-bottom: 12px;
    }
</style>
@endpush

@section('content')

<div class="m-profile-hero">
    <img src="{{ $user->avatar_url }}" alt="Avatar" class="m-profile-avatar">
    <div style="font-size:17px;font-weight:800">{{ $user->name }}</div>
    <div style="font-size:12px;opacity:0.85;margin-top:3px">
        ID Karyawan: <strong>{{ $user->employee_id ?? $user->username }}</strong>
    </div>
    <span style="margin-top:8px;background:rgba(255,255,255,0.2);padding:3px 12px;border-radius:999px;font-size:11px;font-weight:700;text-transform:uppercase">
        {{ $user->roles->first()?->name ?? 'Driver' }}
    </span>
</div>

<div class="m-stat-grid">
    <div class="m-stat-box">
        <div style="font-size:22px;font-weight:800;color:#0B2E8A">{{ $totalChecklists }}</div>
        <div style="font-size:11px;color:#4B5563;font-weight:600">Total Inspeksi</div>
    </div>
    <div class="m-stat-box">
        <div style="font-size:22px;font-weight:800;color:#4CAF28">{{ $thisMonthChecklists }}</div>
        <div style="font-size:11px;color:#4B5563;font-weight:600">Bulan Ini</div>
    </div>
</div>

@if($assignedVehicle)
<div class="m-form-card" style="border-left:4px solid #2563EB;margin-top:6px">
    <div style="font-size:13px;font-weight:700;color:#111827;margin-bottom:4px">
        <i class="fa-solid fa-truck" style="color:#2563EB;margin-right:6px"></i>
        Armada Terakhir Dioperasikan
    </div>
    <div style="font-size:14px;font-weight:800;color:#111827">
        {{ $assignedVehicle->plat_nomor }} — {{ $assignedVehicle->merk }}
    </div>
    <div style="font-size:11px;color:#4B5563;margin-top:2px">
        Terakhir diakses: {{ $user->vehicle_assigned_at?->format('d M Y, H:i') }} WIB
    </div>
</div>
@endif

<div class="m-form-card">
    <div style="font-size:13.5px;font-weight:700;color:#111827;margin-bottom:12px">
        <i class="fa-solid fa-user-pen" style="color:#0B2E8A;margin-right:6px"></i>
        Perbarui Profil, Foto & Kata Sandi
    </div>

    <form action="{{ route('settings.profile') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <input type="hidden" name="from_mobile" value="1">

        <label style="font-size:11.5px;font-weight:600;color:#374151">Nama Lengkap</label>
        <input type="text" name="name" value="{{ old('name', $user->name) }}" class="m-input" required>

        <label style="font-size:11.5px;font-weight:600;color:#374151">Username</label>
        <input type="text" name="username" value="{{ old('username', $user->username) }}" class="m-input" required>

        <label style="font-size:11.5px;font-weight:600;color:#374151">Foto Profil (Opsional)</label>
        <input type="file" name="avatar" accept="image/*" class="m-input">

        <hr style="border:none;border-top:1px dashed #D1D5DB;margin:6px 0 12px">

        <div style="font-size:12px;font-weight:700;color:#0B2E8A;margin-bottom:8px;display:flex;align-items:center;gap:6px">
            <i class="fa-solid fa-key"></i>
            <span>Ganti Kata Sandi (Opsional)</span>
        </div>

        <label style="font-size:11.5px;font-weight:600;color:#374151">Kata Sandi Baru</label>
        <div style="position:relative">
            <input type="password"
                   name="password"
                   id="mobileNewPassword"
                   class="m-input"
                   style="padding-right:38px"
                   placeholder="Kosongkan jika tidak ingin mengganti"
                   autocomplete="new-password">
            <button type="button"
                    onclick="toggleMobilePwd('mobileNewPassword', this)"
                    style="position:absolute;right:10px;top:50%;transform:translateY(-65%);background:none;border:none;color:#6B7280;cursor:pointer;font-size:13px">
                <i class="fa-regular fa-eye"></i>
            </button>
        </div>

        <label style="font-size:11.5px;font-weight:600;color:#374151">Konfirmasi Kata Sandi Baru</label>
        <div style="position:relative">
            <input type="password"
                   name="password_confirmation"
                   id="mobileConfirmPassword"
                   class="m-input"
                   style="padding-right:38px"
                   placeholder="Ulangi kata sandi baru"
                   autocomplete="new-password">
            <button type="button"
                    onclick="toggleMobilePwd('mobileConfirmPassword', this)"
                    style="position:absolute;right:10px;top:50%;transform:translateY(-65%);background:none;border:none;color:#6B7280;cursor:pointer;font-size:13px">
                <i class="fa-regular fa-eye"></i>
            </button>
        </div>

        <button type="submit" style="width:100%;background:#0B2E8A;color:#fff;border:none;border-radius:10px;padding:11px;font-size:13px;font-weight:700;cursor:pointer;margin-top:4px">
            <i class="fa-solid fa-floppy-disk" style="margin-right:6px"></i>
            Simpan Perubahan Akun
        </button>
    </form>
</div>

<script>
    function toggleMobilePwd(inputId, btn) {
        var inp = document.getElementById(inputId);
        var icon = btn.querySelector('i');
        if (!inp) return;
        if (inp.type === 'password') {
            inp.type = 'text';
            icon.className = 'fa-regular fa-eye-slash';
        } else {
            inp.type = 'password';
            icon.className = 'fa-regular fa-eye';
        }
    }
</script>

<div style="padding:8px 20px 28px">
    <form action="{{ route('logout') }}" method="POST">
        @csrf
        <button type="submit" style="width:100%;background:#FEE2E2;color:#DC2626;border:1px solid #FCA5A5;border-radius:12px;padding:12px;font-size:13.5px;font-weight:700;cursor:pointer">
            <i class="fa-solid fa-right-from-bracket" style="margin-right:6px"></i>
            Keluar dari Aplikasi
        </button>
    </form>
</div>

@endsection


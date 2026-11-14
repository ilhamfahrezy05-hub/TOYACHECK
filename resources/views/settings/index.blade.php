@extends('layouts.app')

@section('title', 'Pengaturan')
@section('page-title', 'Pengaturan')
@section('page-subtitle', 'Kelola profil, keamanan akun, dan preferensi tampilan')

@push('styles')
<style>
    .set-layout { display: grid; grid-template-columns: 240px 1fr; gap: 20px; align-items: start; }
    @media (max-width: 900px) { .set-layout { grid-template-columns: 1fr; } }

    .set-tabs { padding: 10px; display: flex; flex-direction: column; gap: 4px; }
    .set-tab {
        display: flex; align-items: center; gap: 10px;
        padding: 11px 14px; border-radius: 8px;
        font-size: 13.5px; font-weight: 600; color: var(--text-secondary);
        text-decoration: none; transition: all .15s;
    }
    .set-tab:hover { background: var(--primary-subtle); color: var(--primary); }
    .set-tab.active { background: var(--primary); color: #fff; }

    .set-toggle {
        display: flex; align-items: center; justify-content: space-between; gap: 16px;
        padding: 14px 0; border-bottom: 1px dashed var(--border);
    }
    .set-toggle:last-of-type { border-bottom: 0; }
    .set-toggle-title { font-weight: 600; font-size: 13.5px; }
    .set-toggle-desc  { font-size: 12px; color: var(--text-muted); margin-top: 2px; }
    .set-toggle input { width: 18px; height: 18px; accent-color: var(--primary); cursor: pointer; }
</style>
@endpush

@section('content')

@php
    $tab = request('tab', 'profil');
    if (! in_array($tab, ['profil', 'keamanan', 'preferensi'], true)) { $tab = 'profil'; }

    $perPage = $user->perPage();
@endphp

<div class="set-layout">

    {{-- ── MENU TAB ── --}}
    <div class="card">
        <div class="set-tabs">
            <a href="{{ route('settings.index', ['tab' => 'profil']) }}" class="set-tab {{ $tab === 'profil' ? 'active' : '' }}">
                <i class="fa-solid fa-user"></i> Profil
            </a>
            <a href="{{ route('settings.index', ['tab' => 'keamanan']) }}" class="set-tab {{ $tab === 'keamanan' ? 'active' : '' }}">
                <i class="fa-solid fa-lock"></i> Keamanan
            </a>
            <a href="{{ route('settings.index', ['tab' => 'preferensi']) }}" class="set-tab {{ $tab === 'preferensi' ? 'active' : '' }}">
                <i class="fa-solid fa-sliders"></i> Preferensi &amp; Notifikasi
            </a>
        </div>
    </div>

    {{-- ── KONTEN TAB ── --}}
    <div class="card">

        {{-- PROFIL --}}
        @if($tab === 'profil')
        <div class="card-header">
            <div>
                <div class="card-title">Profil Saya</div>
                <div class="card-subtitle">NIK dan role hanya bisa diubah oleh Admin lewat menu Pengguna</div>
            </div>
        </div>

        <form method="POST" action="{{ route('settings.profile') }}" enctype="multipart/form-data" style="padding:20px 24px">
            @csrf
            @method('PUT')

            {{-- Avatar Section ala Figma --}}
            <div style="display:flex;align-items:center;gap:20px;padding-bottom:20px;margin-bottom:20px;border-bottom:1px solid var(--border)">
                <div style="position:relative;width:76px;height:76px;border-radius:50%;overflow:hidden;border:2px solid var(--border);flex-shrink:0;background:#0F1E36;box-shadow:0 2px 8px rgba(15,30,54,0.08)">
                    <img src="{{ $user->avatar_url }}" id="profileAvatarPreview" alt="{{ $user->name }}" style="width:100%;height:100%;object-fit:cover">
                </div>
                <div>
                    <div style="font-weight:700;font-size:14px;color:var(--text-primary);margin-bottom:4px">Foto Profil</div>
                    <div style="font-size:12px;color:var(--text-muted);margin-bottom:10px">Format JPG, JPEG, PNG, atau WEBP. Maksimal 2MB.</div>
                    <label class="btn btn-secondary btn-sm" style="cursor:pointer;display:inline-flex;align-items:center;gap:6px">
                        <i class="fa-solid fa-camera"></i> Unggah Foto
                        <input type="file" name="avatar" accept="image/*" style="display:none" onchange="previewProfileImage(this)">
                    </label>
                    @error('avatar')<div class="invalid-feedback" style="display:block">{{ $message }}</div>@enderror
                </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                <div class="form-group">
                    <label class="form-label">NIK</label>
                    <input type="text" class="form-control" value="{{ $user->employee_id ?: '—' }}" disabled>
                </div>

                <div class="form-group">
                    <label class="form-label">Role</label>
                    <input type="text" class="form-control" value="{{ ucfirst($user->getRoleNames()->first() ?? '—') }}" disabled>
                </div>

                <div class="form-group">
                    <label class="form-label" for="s_name">Nama Lengkap <span class="required">*</span></label>
                    <input type="text" id="s_name" name="name" required
                           class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}"
                           value="{{ old('name', $user->name) }}">
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="s_username">Username <span class="required">*</span></label>
                    <input type="text" id="s_username" name="username" required
                           class="form-control {{ $errors->has('username') ? 'is-invalid' : '' }}"
                           value="{{ old('username', $user->username) }}">
                    @error('username')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <button type="submit" class="btn btn-primary" style="margin-top:8px">
                <i class="fa-solid fa-floppy-disk"></i> Simpan Profil
            </button>
        </form>

        <script>
            function previewProfileImage(input) {
                if (input.files && input.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const preview = document.getElementById('profileAvatarPreview');
                        if (preview) preview.src = e.target.result;
                    };
                    reader.readAsDataURL(input.files[0]);
                }
            }
        </script>
        @endif

        {{-- KEAMANAN --}}
        @if($tab === 'keamanan')
        <div class="card-header">
            <div>
                <div class="card-title">Ubah Kata Sandi</div>
                <div class="card-subtitle">Gunakan minimal 6 karakter</div>
            </div>
        </div>

        <form method="POST" action="{{ route('settings.password') }}" style="padding:20px 24px;max-width:480px">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label class="form-label" for="s_current">Kata Sandi Saat Ini <span class="required">*</span></label>
                <input type="password" id="s_current" name="current_password" required
                       class="form-control {{ $errors->has('current_password') ? 'is-invalid' : '' }}">
                @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="s_new">Kata Sandi Baru <span class="required">*</span></label>
                <input type="password" id="s_new" name="password" required minlength="6"
                       class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}">
                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="s_confirm">Ulangi Kata Sandi Baru <span class="required">*</span></label>
                <input type="password" id="s_confirm" name="password_confirmation" required class="form-control">
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-key"></i> Ubah Kata Sandi
            </button>
        </form>
        @endif

        {{-- PREFERENSI & NOTIFIKASI --}}
        @if($tab === 'preferensi')
        <div class="card-header">
            <div>
                <div class="card-title">Preferensi &amp; Notifikasi</div>
                <div class="card-subtitle">Berlaku untuk akun Anda saja</div>
            </div>
        </div>

        <form method="POST" action="{{ route('settings.preferences') }}" style="padding:20px 24px;max-width:560px">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label class="form-label" for="s_per_page">Baris per Halaman (tabel)</label>
                <select id="s_per_page" name="per_page" class="form-control" style="max-width:200px">
                    @foreach([10, 15, 25, 50] as $n)
                        <option value="{{ $n }}" {{ (int) old('per_page', $perPage) === $n ? 'selected' : '' }}>{{ $n }} baris</option>
                    @endforeach
                </select>
                <div class="form-hint" style="font-size:12px;color:var(--text-muted);margin-top:4px">
                    Dipakai pada Riwayat Checklist, Vendor, dan Permintaan.
                </div>
            </div>

            <div style="margin:18px 0 6px;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--text-secondary)">
                Notifikasi Lonceng
            </div>

            <label class="set-toggle">
                <div>
                    <div class="set-toggle-title">Armada rusak</div>
                    <div class="set-toggle-desc">Tampilkan titik notifikasi saat ada armada berstatus rusak (Admin &amp; Mekanik).</div>
                </div>
                <input type="checkbox" name="notify_kendala" value="1"
                       {{ old('notify_kendala', $user->preference('notify_kendala', true)) ? 'checked' : '' }}>
            </label>

            <label class="set-toggle">
                <div>
                    <div class="set-toggle-title">Permintaan menunggu persetujuan</div>
                    <div class="set-toggle-desc">Tampilkan titik notifikasi untuk permintaan suku cadang baru (Admin).</div>
                </div>
                <input type="checkbox" name="notify_permintaan" value="1"
                       {{ old('notify_permintaan', $user->preference('notify_permintaan', true)) ? 'checked' : '' }}>
            </label>

            <button type="submit" class="btn btn-primary" style="margin-top:14px">
                <i class="fa-solid fa-floppy-disk"></i> Simpan Preferensi
            </button>
        </form>
        @endif

    </div>
</div>

@endsection

@extends('layouts.app')

@section('title', 'Manajemen Pengguna')
@section('page-title', 'Manajemen Pengguna')
@section('page-subtitle', 'Kelola akun pengguna sistem TOYACHECK')

{{-- ===================== PAGE ACTIONS ===================== --}}
@section('page-actions')
    <button class="btn btn-primary" id="btnTambahUser" onclick="openModal('modalCreateUser')">
        <i class="fa-solid fa-plus"></i>
        Tambah Pengguna
    </button>
@endsection

{{-- ===================== CONTENT ===================== --}}
@section('content')

<div class="card">
    <div class="card-header">
        <div>
            <div class="card-title">Daftar Pengguna</div>
            <div class="card-subtitle">Total {{ $users->total() }} pengguna terdaftar</div>
        </div>

        {{-- Search --}}
        <div class="d-flex gap-8 align-center">
            <div style="position:relative">
                <i class="fa-solid fa-magnifying-glass" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:var(--text-muted);font-size:13px"></i>
                <input
                    type="text"
                    id="searchInput"
                    placeholder="Cari nama / username..."
                    class="form-control"
                    style="padding-left:32px;width:220px"
                    oninput="filterTable(this.value)">
            </div>
        </div>
    </div>

    <div class="card-body-p0">
        <div class="table-wrap">
            <table class="tc-table" id="usersTable">
                <thead>
                    <tr>
                        <th style="width:52px">#</th>
                        <th>NIK</th>
                        <th>Nama Lengkap</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Bergabung</th>
                        <th style="width:110px;text-align:center">Aksi</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @forelse ($users as $index => $user)
                    <tr data-name="{{ strtolower($user->name) }}" data-username="{{ strtolower($user->username) }}" data-nik="{{ strtolower($user->employee_id ?? '') }}">
                        <td style="color:var(--text-muted);font-size:12px">
                            {{ $users->firstItem() + $index }}
                        </td>

                        <td>
                            @if($user->employee_id)
                                <code style="font-family:monospace;font-size:12px;font-weight:600;background:var(--border-light);padding:2px 7px;border-radius:4px;color:var(--text-primary)">
                                    {{ $user->employee_id }}
                                </code>
                            @else
                                <span style="color:var(--text-muted);font-size:12px">—</span>
                            @endif
                        </td>

                        <td>
                            <div class="d-flex align-center gap-8">
                                <div class="user-avatar" style="width:34px;height:34px;font-size:12px;flex-shrink:0;overflow:hidden;border:1px solid var(--border)">
                                    <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" style="width:100%;height:100%;object-fit:cover;border-radius:50%">
                                </div>
                                <span style="font-weight:600;color:var(--text-primary)">{{ $user->name }}</span>
                            </div>
                        </td>

                        <td>
                            <code style="background:var(--border-light);padding:2px 7px;border-radius:4px;font-size:12px;color:var(--text-secondary)">
                                {{ $user->username }}
                            </code>
                        </td>

                        <td>
                            @php
                                $role = $user->getRoleNames()->first() ?? 'unknown';
                                $roleClass = match($role) {
                                    'admin'     => 'badge-admin',
                                    'driver'    => 'badge-driver',
                                    'mekanik'   => 'badge-mekanik',
                                    'hse'       => 'badge-hse',
                                    'pengadaan' => 'badge-info',
                                    default     => 'badge-neutral',
                                };
                            @endphp
                            <span class="badge {{ $roleClass }}">{{ ucfirst($role) }}</span>
                        </td>

                        <td style="color:var(--text-secondary);font-size:13px">
                            <i class="fa-regular fa-calendar" style="margin-right:4px"></i>
                            {{ $user->created_at->format('d M Y') }}
                        </td>

                        <td>
                            <div class="d-flex gap-8 align-center" style="justify-content:center">
                                {{-- Edit Button --}}
                                <button
                                    class="btn btn-warning btn-sm btn-icon"
                                    title="Edit Pengguna"
                                    onclick="openEditModal(
                                        {{ $user->id }},
                                        '{{ addslashes($user->employee_id ?? '') }}',
                                        '{{ addslashes($user->name) }}',
                                        '{{ addslashes($user->username) }}',
                                        '{{ addslashes($user->getRoleNames()->first() ?? '') }}'
                                    )">
                                    <i class="fa-solid fa-pen"></i>
                                </button>

                                {{-- Delete Button --}}
                                @if($user->id !== auth()->id())
                                <button
                                    class="btn btn-danger btn-sm btn-icon"
                                    title="Hapus Pengguna"
                                    onclick="openDeleteModal({{ $user->id }}, '{{ addslashes($user->name) }}')">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                                @else
                                <button class="btn btn-sm btn-icon" disabled style="background:#F1F5F9;color:var(--text-muted);cursor:not-allowed" title="Tidak bisa hapus akun sendiri">
                                    <i class="fa-solid fa-lock"></i>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr id="emptyRow">
                        <td colspan="6">
                            <div class="empty-state">
                                <div class="empty-icon"><i class="fa-solid fa-users-slash"></i></div>
                                <div class="empty-title">Belum Ada Pengguna</div>
                                <div class="empty-text">Tambahkan pengguna pertama dengan klik tombol "Tambah Pengguna"</div>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($users->hasPages())
    <div class="card-footer">
        {{ $users->links('pagination::bootstrap-4') }}
    </div>
    @endif
</div>

@endsection

{{-- ===================== MODALS ===================== --}}
@section('modals')

{{-- ===== MODAL CREATE ===== --}}
<div class="modal-overlay" id="modalCreateUser">
    <div class="modal-box modal-lg">
        <div class="modal-header">
            <div class="modal-title">
                <i class="fa-solid fa-user-plus" style="color:var(--primary);margin-right:8px"></i>
                Tambah Pengguna Baru
            </div>
            <button class="modal-close" onclick="closeModal('modalCreateUser')" type="button">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form action="{{ route('users.store') }}" method="POST" id="formCreate" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="_action" value="create">

            <div class="modal-body">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                    <div class="form-group" style="margin-bottom:0">
                        <label class="form-label" for="create_employee_id">
                            NIK (Nomor Induk Karyawan)
                        </label>
                        <input
                            type="text"
                            id="create_employee_id"
                            name="employee_id"
                            class="form-control {{ $errors->has('employee_id') && old('_action') === 'create' ? 'is-invalid' : '' }}"
                            value="{{ old('_action') === 'create' ? old('employee_id') : '' }}"
                            placeholder="Contoh: EMP-0012">
                        @if($errors->has('employee_id') && old('_action') === 'create')
                            <div class="invalid-feedback"><i class="fa-solid fa-circle-exclamation"></i> {{ $errors->first('employee_id') }}</div>
                        @endif
                    </div>

                    <div class="form-group" style="margin-bottom:0">
                        <label class="form-label" for="create_name">
                            Nama Lengkap <span class="required">*</span>
                        </label>
                        <input
                            type="text"
                            id="create_name"
                            name="name"
                            class="form-control {{ $errors->has('name') && old('_action') === 'create' ? 'is-invalid' : '' }}"
                            value="{{ old('_action') === 'create' ? old('name') : '' }}"
                            placeholder="Masukkan nama lengkap"
                            required>
                        @if($errors->has('name') && old('_action') === 'create')
                            <div class="invalid-feedback"><i class="fa-solid fa-circle-exclamation"></i> {{ $errors->first('name') }}</div>
                        @endif
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-top:16px">
                    <div class="form-group" style="margin-bottom:0">
                        <label class="form-label" for="create_username">
                            Username <span class="required">*</span>
                        </label>
                        <input
                            type="text"
                            id="create_username"
                            name="username"
                            class="form-control {{ $errors->has('username') && old('_action') === 'create' ? 'is-invalid' : '' }}"
                            value="{{ old('_action') === 'create' ? old('username') : '' }}"
                            placeholder="Masukkan username unik"
                            required>
                        @if($errors->has('username') && old('_action') === 'create')
                            <div class="invalid-feedback"><i class="fa-solid fa-circle-exclamation"></i> {{ $errors->first('username') }}</div>
                        @endif
                    </div>

                    <div class="form-group" style="margin-bottom:0">
                        <label class="form-label" for="create_avatar">
                            Foto Profil (Opsional)
                        </label>
                        <input
                            type="file"
                            id="create_avatar"
                            name="avatar"
                            accept="image/*"
                            class="form-control {{ $errors->has('avatar') && old('_action') === 'create' ? 'is-invalid' : '' }}">
                        @if($errors->has('avatar') && old('_action') === 'create')
                            <div class="invalid-feedback"><i class="fa-solid fa-circle-exclamation"></i> {{ $errors->first('avatar') }}</div>
                        @endif
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-top:16px">
                    <div class="form-group" style="margin-bottom:0">
                        <label class="form-label" for="create_password">
                            Password <span class="required">*</span>
                        </label>
                        <div style="position:relative">
                            <input
                                type="password"
                                id="create_password"
                                name="password"
                                class="form-control {{ $errors->has('password') && old('_action') === 'create' ? 'is-invalid' : '' }}"
                                placeholder="Min. 6 karakter"
                                required>
                            <button type="button" onclick="togglePassword('create_password', this)"
                                style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--text-muted);font-size:13px">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                        @if($errors->has('password') && old('_action') === 'create')
                            <div class="invalid-feedback"><i class="fa-solid fa-circle-exclamation"></i> {{ $errors->first('password') }}</div>
                        @endif
                    </div>

                    <div class="form-group" style="margin-bottom:0">
                        <label class="form-label" for="create_role">
                            Role <span class="required">*</span>
                        </label>
                        <select
                            id="create_role"
                            name="role"
                            class="form-control {{ $errors->has('role') && old('_action') === 'create' ? 'is-invalid' : '' }}"
                            required>
                            <option value="">— Pilih Role —</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->name }}"
                                    {{ old('_action') === 'create' && old('role') === $role->name ? 'selected' : '' }}>
                                    {{ ucfirst($role->name) }}
                                </option>
                            @endforeach
                        </select>
                        @if($errors->has('role') && old('_action') === 'create')
                            <div class="invalid-feedback"><i class="fa-solid fa-circle-exclamation"></i> {{ $errors->first('role') }}</div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalCreateUser')">
                    Batal
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-floppy-disk"></i>
                    Simpan Pengguna
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ===== MODAL EDIT ===== --}}
<div class="modal-overlay" id="modalEditUser">
    <div class="modal-box modal-lg">
        <div class="modal-header">
            <div class="modal-title">
                <i class="fa-solid fa-user-pen" style="color:var(--warning);margin-right:8px"></i>
                Edit Pengguna
            </div>
            <button class="modal-close" onclick="closeModal('modalEditUser')" type="button">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form id="formEdit" action="" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <input type="hidden" name="_action" value="edit">
            <input type="hidden" name="_user_id" id="edit_user_id_hidden">

            <div class="modal-body">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                    <div class="form-group" style="margin-bottom:0">
                        <label class="form-label" for="edit_employee_id">
                            NIK (Nomor Induk Karyawan)
                        </label>
                        <input
                            type="text"
                            id="edit_employee_id"
                            name="employee_id"
                            class="form-control {{ $errors->has('employee_id') && old('_action') === 'edit' ? 'is-invalid' : '' }}"
                            placeholder="Contoh: EMP-0012">
                        @if($errors->has('employee_id') && old('_action') === 'edit')
                            <div class="invalid-feedback"><i class="fa-solid fa-circle-exclamation"></i> {{ $errors->first('employee_id') }}</div>
                        @endif
                    </div>

                    <div class="form-group" style="margin-bottom:0">
                        <label class="form-label" for="edit_name">
                            Nama Lengkap <span class="required">*</span>
                        </label>
                        <input
                            type="text"
                            id="edit_name"
                            name="name"
                            class="form-control {{ $errors->has('name') && old('_action') === 'edit' ? 'is-invalid' : '' }}"
                            placeholder="Nama lengkap"
                            required>
                        @if($errors->has('name') && old('_action') === 'edit')
                            <div class="invalid-feedback"><i class="fa-solid fa-circle-exclamation"></i> {{ $errors->first('name') }}</div>
                        @endif
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-top:16px">
                    <div class="form-group" style="margin-bottom:0">
                        <label class="form-label" for="edit_username">
                            Username <span class="required">*</span>
                        </label>
                        <input
                            type="text"
                            id="edit_username"
                            name="username"
                            class="form-control {{ $errors->has('username') && old('_action') === 'edit' ? 'is-invalid' : '' }}"
                            placeholder="Username unik"
                            required>
                        @if($errors->has('username') && old('_action') === 'edit')
                            <div class="invalid-feedback"><i class="fa-solid fa-circle-exclamation"></i> {{ $errors->first('username') }}</div>
                        @endif
                    </div>

                    <div class="form-group" style="margin-bottom:0">
                        <label class="form-label" for="edit_avatar">
                            Foto Profil Baru (Opsional)
                        </label>
                        <input
                            type="file"
                            id="edit_avatar"
                            name="avatar"
                            accept="image/*"
                            class="form-control {{ $errors->has('avatar') && old('_action') === 'edit' ? 'is-invalid' : '' }}">
                        @if($errors->has('avatar') && old('_action') === 'edit')
                            <div class="invalid-feedback"><i class="fa-solid fa-circle-exclamation"></i> {{ $errors->first('avatar') }}</div>
                        @endif
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-top:16px">
                    <div class="form-group" style="margin-bottom:0">
                        <label class="form-label" for="edit_password">
                            Password Baru
                        </label>
                        <div style="position:relative">
                            <input
                                type="password"
                                id="edit_password"
                                name="password"
                                class="form-control"
                                placeholder="Kosongkan jika tidak diubah">
                            <button type="button" onclick="togglePassword('edit_password', this)"
                                style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--text-muted);font-size:13px">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                        <div style="font-size:11px;color:var(--text-muted);margin-top:4px">
                            <i class="fa-solid fa-circle-info"></i> Biarkan kosong jika tidak ingin mengubah password
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom:0">
                        <label class="form-label" for="edit_role">
                            Role <span class="required">*</span>
                        </label>
                        <select
                            id="edit_role"
                            name="role"
                            class="form-control {{ $errors->has('role') && old('_action') === 'edit' ? 'is-invalid' : '' }}"
                            required>
                            @foreach($roles as $role)
                                <option value="{{ $role->name }}">{{ ucfirst($role->name) }}</option>
                            @endforeach
                        </select>
                        @if($errors->has('role') && old('_action') === 'edit')
                            <div class="invalid-feedback"><i class="fa-solid fa-circle-exclamation"></i> {{ $errors->first('role') }}</div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalEditUser')">
                    Batal
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-floppy-disk"></i>
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ===== MODAL DELETE ===== --}}
<div class="modal-overlay" id="modalDeleteUser">
    <div class="modal-box" style="max-width:420px">
        <div class="modal-header">
            <div class="modal-title" style="color:var(--danger)">
                <i class="fa-solid fa-triangle-exclamation" style="margin-right:8px"></i>
                Konfirmasi Hapus
            </div>
            <button class="modal-close" onclick="closeModal('modalDeleteUser')" type="button">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div class="modal-body" style="text-align:center;padding:32px 24px">
            <div style="width:64px;height:64px;border-radius:50%;background:var(--danger-bg);display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:28px;color:var(--danger)">
                <i class="fa-solid fa-trash"></i>
            </div>
            <div style="font-size:15px;font-weight:600;color:var(--text-primary);margin-bottom:8px">
                Hapus Pengguna?
            </div>
            <div style="font-size:13px;color:var(--text-secondary);line-height:1.6">
                Akun pengguna <strong id="deleteUserName" style="color:var(--text-primary)"></strong>
                akan dihapus secara permanen dan tidak dapat dikembalikan.
            </div>
        </div>

        <div class="modal-footer" style="justify-content:center;gap:12px">
            <button type="button" class="btn btn-secondary" onclick="closeModal('modalDeleteUser')" style="min-width:120px">
                Batal
            </button>
            <form id="formDelete" action="" method="POST" style="display:inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger" style="min-width:120px">
                    <i class="fa-solid fa-trash"></i>
                    Ya, Hapus
                </button>
            </form>
        </div>
    </div>
</div>

@endsection

{{-- ===================== PAGE SCRIPTS ===================== --}}
@push('scripts')
<script>
(function () {
    'use strict';

    /* -----------------------------------------------
       Re-open modal on validation error
    ----------------------------------------------- */
    @if($errors->any())
        @if(old('_action') === 'create')
            document.addEventListener('DOMContentLoaded', function () {
                window.openModal('modalCreateUser');
            });
        @endif

        @if(old('_action') === 'edit' && old('_user_id'))
            document.addEventListener('DOMContentLoaded', function () {
                // Restore edit form fields from old() values
                var form   = document.getElementById('formEdit');
                var userId = '{{ old('_user_id') }}';
                form.action = '/users/' + userId;
                document.getElementById('edit_user_id_hidden').value = userId;
                document.getElementById('edit_employee_id').value = '{{ addslashes(old('employee_id')) }}';
                document.getElementById('edit_name').value        = '{{ addslashes(old('name')) }}';
                document.getElementById('edit_username').value    = '{{ addslashes(old('username')) }}';
                document.getElementById('edit_role').value        = '{{ addslashes(old('role')) }}';
                window.openModal('modalEditUser');
            });
        @endif
    @endif

    /* -----------------------------------------------
       openEditModal(id, employee_id, name, username, role)
       Called from the Edit button in each table row
    ----------------------------------------------- */
    window.openEditModal = function (userId, employeeId, name, username, role) {
        var form = document.getElementById('formEdit');
        form.action = '/users/' + userId;

        document.getElementById('edit_user_id_hidden').value = userId;
        document.getElementById('edit_employee_id').value    = employeeId || '';
        document.getElementById('edit_name').value           = name;
        document.getElementById('edit_username').value       = username;
        document.getElementById('edit_password').value       = '';

        // Set role select
        var roleSelect = document.getElementById('edit_role');
        for (var i = 0; i < roleSelect.options.length; i++) {
            if (roleSelect.options[i].value === role) {
                roleSelect.selectedIndex = i;
                break;
            }
        }

        window.openModal('modalEditUser');
    };

    /* -----------------------------------------------
       openDeleteModal(id, name)
       Called from the Delete button in each table row
    ----------------------------------------------- */
    window.openDeleteModal = function (userId, name) {
        document.getElementById('deleteUserName').textContent = name;
        document.getElementById('formDelete').action = '/users/' + userId;
        window.openModal('modalDeleteUser');
    };

    /* -----------------------------------------------
       togglePassword(inputId, btn)
       Show/hide password field
    ----------------------------------------------- */
    window.togglePassword = function (inputId, btn) {
        var input = document.getElementById(inputId);
        var icon  = btn.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.className = 'fa-solid fa-eye-slash';
        } else {
            input.type = 'password';
            icon.className = 'fa-solid fa-eye';
        }
    };

    /* -----------------------------------------------
       filterTable(query)
       Client-side table search filter
    ----------------------------------------------- */
    window.filterTable = function (query) {
        query = query.toLowerCase().trim();
        var rows = document.querySelectorAll('#tableBody tr[data-name]');
        var visibleCount = 0;

        rows.forEach(function (row) {
            var name     = row.getAttribute('data-name') || '';
            var username = row.getAttribute('data-username') || '';
            var nik      = row.getAttribute('data-nik') || '';
            var match    = name.includes(query) || username.includes(query) || nik.includes(query);
            row.style.display = match ? '' : 'none';
            if (match) visibleCount++;
        });

        // Show/hide empty state row if exists
        var emptyRow = document.getElementById('noResultRow');
        if (query && visibleCount === 0) {
            if (!emptyRow) {
                emptyRow = document.createElement('tr');
                emptyRow.id = 'noResultRow';
                emptyRow.innerHTML =
                    '<td colspan="7">' +
                    '<div class="empty-state">' +
                    '<div class="empty-icon"><i class="fa-solid fa-magnifying-glass"></i></div>' +
                    '<div class="empty-title">Tidak Ditemukan</div>' +
                    '<div class="empty-text">Tidak ada pengguna yang cocok dengan pencarian "<strong>' + query + '</strong>"</div>' +
                    '</div></td>';
                document.getElementById('tableBody').appendChild(emptyRow);
            }
        } else if (emptyRow) {
            emptyRow.remove();
        }
    };

})();
</script>
@endpush
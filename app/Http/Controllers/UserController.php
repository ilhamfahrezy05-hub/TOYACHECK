<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('roles')
            ->latest()
            ->paginate(10);

        $roles = Role::all();

        return view(
            'users.index',
            compact(
                'users',
                'roles'
            )
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'nullable|string|max:30|unique:users,employee_id',
            'name'        => 'required|max:255',
            'username'    => 'required|unique:users',
            'password'    => 'required|min:6',
            'role'        => 'required',
            'avatar'      => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $avatarPath = null;
        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
        }

        $user = User::create([
            'employee_id' => $validated['employee_id'] ?? null,
            'name'        => $validated['name'],
            'username'    => $validated['username'],
            'password'    => $validated['password'],
            'avatar'      => $avatarPath,
        ]);

        $user->assignRole(
            $validated['role']
        );

        return back()->with(
            'success',
            'Data pengguna berhasil ditambahkan.'
        );
    }

    public function update(Request $request, User $user) 
    {
        $validated = $request->validate([
            'employee_id' => 'nullable|string|max:30|unique:users,employee_id,' . $user->id,
            'name'        => 'required',
            'username'    => 'required|unique:users,username,' . $user->id,
            'role'        => 'required',
            'avatar'      => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $updateData = [
            'employee_id' => $validated['employee_id'] ?? null,
            'name'        => $validated['name'],
            'username'    => $validated['username'],
        ];

        if ($request->hasFile('avatar')) {
            if ($user->avatar && \Illuminate\Support\Facades\Storage::disk('public')->exists($user->avatar)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($user->avatar);
            }
            $updateData['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user->update($updateData);

        if ($request->filled('password')) {
            $user->update([
                'password' => $request->password
            ]);
        }

        $user->syncRoles([
            $validated['role']
        ]);

        return back()->with(
            'success',
            'Data pengguna berhasil diperbarui.'
        );
    }

    public function destroy(User $user)
    {
        if ($user->id == Auth::id()) {

            return back()->with(
                'error',
                'Akun yang sedang digunakan tidak dapat dihapus.'
            );
        }

        if ($user->hasActivityHistory()) {

            return back()->with(
                'error',
                'Pengguna ini punya riwayat checklist/perbaikan dan tidak dapat dihapus.'
            );
        }

        $user->delete();

        return back()->with(
            'success',
            'Pengguna berhasil dihapus.'
        );
    }
}

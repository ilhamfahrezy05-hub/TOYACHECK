<?php

namespace App\Http\Controllers;

use App\Models\UserSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

/**
 * Pengaturan akun sendiri: profil, kata sandi, preferensi & notifikasi.
 */
class SettingController extends Controller
{
    public function index()
    {
        return view('settings.index', [
            'user'     => auth()->user(),
            'settings' => auth()->user()->settings,
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'username' => ['required', 'string', 'max:255', Rule::unique('users', 'username')->ignore($user->id)],
            'avatar'   => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'password' => 'nullable|string|min:6|confirmed',
        ], [
            'password.min'       => 'Kata sandi baru minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi baru tidak cocok.',
        ]);

        if ($request->hasFile('avatar')) {
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }
            $validated['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $passwordChanged = false;
        if ($request->filled('password')) {
            $validated['password'] = $request->input('password');
            $passwordChanged = true;
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        $msg = $passwordChanged
            ? 'Profil dan kata sandi baru berhasil disimpan!'
            : 'Profil dan foto berhasil diperbarui.';

        if ($request->boolean('from_mobile') || $user->hasAnyRole(['driver', 'hse'])) {
            return redirect()->route('driver.profile')
                ->with('success', $msg);
        }

        return redirect()->route('settings.index', ['tab' => 'profil'])
            ->with('success', $msg);
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password'         => 'required|min:6|confirmed',
        ], [
            'current_password.required' => 'Kata sandi saat ini wajib diisi.',
            'password.confirmed'        => 'Konfirmasi kata sandi tidak sama.',
        ]);

        $user = auth()->user();

        if (! Hash::check($request->current_password, $user->password)) {
            return redirect()->route('settings.index', ['tab' => 'keamanan'])
                ->withErrors(['current_password' => 'Kata sandi saat ini salah.']);
        }

        $user->update(['password' => $request->password]);

        return redirect()->route('settings.index', ['tab' => 'keamanan'])
            ->with('success', 'Kata sandi berhasil diubah.');
    }

    public function updatePreferences(Request $request)
    {
        $validated = $request->validate([
            'per_page' => ['required', Rule::in([10, 15, 25, 50])],
        ]);

        UserSetting::updateOrCreate(
            ['user_id' => auth()->id()],
            [
                'per_page'          => (int) $validated['per_page'],
                // Checkbox yang tidak dicentang tidak terkirim, jadi pakai boolean()
                'notify_kendala'    => $request->boolean('notify_kendala'),
                'notify_permintaan' => $request->boolean('notify_permintaan'),
            ]
        );

        return redirect()->route('settings.index', ['tab' => 'preferensi'])
            ->with('success', 'Preferensi berhasil disimpan.');
    }
}

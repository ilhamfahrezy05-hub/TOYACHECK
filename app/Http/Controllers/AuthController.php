<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login()
    {
        return view('auth.login');
    }

    public function authenticate(Request $request)
    {
        $identity = trim($request->input('username') ?? $request->input('identity') ?? '');
        $password = $request->input('password') ?? '';

        if (empty($identity) || empty($password)) {
            return back()
                ->withInput($request->only('username', 'identity'))
                ->withErrors([
                    'username' => 'ID Karyawan dan Password wajib diisi.',
                ]);
        }

        // Izinkan login menggunakan ID Karyawan (NIK) maupun Username
        $field = \App\Models\User::where('employee_id', $identity)->exists()
            ? 'employee_id'
            : 'username';

        if (Auth::attempt([$field => $identity, 'password' => $password])) {

            $request->session()->regenerate();

            $user = Auth::user();

            // Jika role driver atau hse, otomatis arahkan ke UI Mobile (/driver)
            if ($user->hasRole('driver') || $user->hasRole('hse')) {
                return redirect()->route('driver.index')
                    ->with('success', 'Selamat bertugas, ' . $user->name . '!');
            }

            return redirect()->route('dashboard')
                ->with('success', 'Selamat datang kembali, ' . $user->name . '!');
        }

        return back()
            ->withInput($request->only('username', 'identity'))
            ->withErrors([
                'username' => 'ID Karyawan / Username atau Password salah.'
            ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
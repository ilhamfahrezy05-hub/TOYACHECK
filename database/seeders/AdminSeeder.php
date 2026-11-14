<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Akun contoh untuk tiap aktor SRS.
     * Aman dijalankan berulang: akun lama hanya diperbarui NIK/namanya,
     * password tidak ditimpa.
     */
    public function run(): void
    {
        // [username, NIK, nama, password awal, role]
        $accounts = [
            ['distributor2', 'EMP-0001', 'Admin Distribusi',          'Admin123!',     'admin'],
            ['driver1',      'EMP-0002', 'Budi Driver',               'Driver123!',    'driver'],
            ['hse1',         'EMP-0003', 'Siti Rahma (HSE)',          'Hse123!',       'hse'],
            ['mekanik1',     'EMP-0004', 'Joko Bengkel (Mekanik)',    'Mekanik123!',   'mekanik'],
            ['pengadaan1',   'EMP-0005', 'Rian Logistik (Pengadaan)', 'Pengadaan123!', 'pengadaan'],
        ];

        foreach ($accounts as [$username, $nik, $name, $password, $role]) {

            $user = User::firstOrNew(['username' => $username]);

            $user->employee_id = $nik;
            $user->name        = $name;

            if (! $user->exists) {
                $user->password = $password;
            }

            $user->save();
            $user->syncRoles([$role]);
        }
    }
}

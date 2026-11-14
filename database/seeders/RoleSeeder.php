<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::create([
            'name' => 'admin'
        ]);
        Role::create([
            'name' => 'driver'
        ]);
        Role::create([
            'name' => 'hse'
        ]);
        Role::create([
            'name' => 'mekanik'
        ]);
        Role::create([
            'name' => 'pengadaan'
        ]);
    }
}

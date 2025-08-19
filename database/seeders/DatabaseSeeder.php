<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Call role seeder first
        $this->call(RoleSeeder::class);

        // Call location seeder
        $this->call(LocationSeeder::class);

        // Create test users with roles
        $superAdmin = User::factory()->create([
            'name' => 'Zachran Razendra',
            'username' => 'zachranraze',
            'email' => 'zachranraze@recodex.id',
        ]);
        $superAdmin->assignRole('super admin');

        $admin = User::factory()->create([
            'name' => 'Ini Admin',
            'username' => 'admin',
            'email' => 'admin@example.com',
        ]);
        $admin->assignRole('admin');

        $karyawan = User::factory()->create([
            'name' => 'Ini Karyawan',
            'username' => 'karyawan',
            'email' => 'karyawan@example.com',
        ]);
        $karyawan->assignRole('karyawan');
    }
}

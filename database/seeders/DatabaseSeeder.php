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

        // User::factory(10)->create();

        // Create test users with roles
        $superAdmin = User::factory()->create([
            'name' => 'Super Admin',
            'username' => 'superadmin',
            'email' => 'superadmin@example.com',
        ]);
        $superAdmin->assignRole('super admin');

        $admin = User::factory()->create([
            'name' => 'Admin User',
            'username' => 'admin',
            'email' => 'admin@example.com',
        ]);
        $admin->assignRole('admin');

        $karyawan = User::factory()->create([
            'name' => 'Karyawan User',
            'username' => 'karyawan',
            'email' => 'karyawan@example.com',
        ]);
        $karyawan->assignRole('karyawan');
    }
}

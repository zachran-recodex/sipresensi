<?php

declare(strict_types=1);

use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    // Create roles
    Role::create(['name' => 'super admin']);
    Role::create(['name' => 'admin']);
    Role::create(['name' => 'karyawan']);
});

test('admin can access show user page', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $user = User::factory()->create();
    $user->assignRole('karyawan');

    $response = $this->actingAs($admin)->get(route('administrator.show-user', $user));

    $response->assertSuccessful();
    $response->assertSee($user->name);
    $response->assertSee($user->email);
    $response->assertSee($user->username);
});

test('super admin can access show user page', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole('super admin');

    $user = User::factory()->create();
    $user->assignRole('karyawan');

    $response = $this->actingAs($superAdmin)->get(route('administrator.show-user', $user));

    $response->assertSuccessful();
    $response->assertSee($user->name);
    $response->assertSee($user->email);
    $response->assertSee($user->username);
});

test('karyawan cannot access show user page', function () {
    $karyawan = User::factory()->create();
    $karyawan->assignRole('karyawan');

    $user = User::factory()->create();
    $user->assignRole('karyawan');

    $response = $this->actingAs($karyawan)->get(route('administrator.show-user', $user));

    $response->assertForbidden();
});

test('unauthenticated user cannot access show user page', function () {
    $user = User::factory()->create();
    $user->assignRole('karyawan');

    $response = $this->get(route('administrator.show-user', $user));

    $response->assertRedirect(route('login'));
});

test('show user page displays face enrollment status for karyawan', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $karyawan = User::factory()->create();
    $karyawan->assignRole('karyawan');

    $response = $this->actingAs($admin)->get(route('administrator.show-user', $karyawan));

    $response->assertSuccessful();
    $response->assertSee('Status Wajah');
    $response->assertSee('Belum Terdaftar');
});

test('show user page displays admin specific information', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole('super admin');

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $response = $this->actingAs($superAdmin)->get(route('administrator.show-user', $admin));

    $response->assertSuccessful();
    $response->assertSee('Informasi Administrator');
    $response->assertSee('Akun Administrator');
});

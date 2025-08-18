<?php

use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function () {
    // Clear the permission cache
    $this->app->make(PermissionRegistrar::class)->forgetCachedPermissions();
});

test('roles can be created', function () {
    // Create roles
    $superAdminRole = Role::create(['name' => 'super admin']);
    $adminRole = Role::create(['name' => 'admin']);
    $karyawanRole = Role::create(['name' => 'karyawan']);

    // Assert roles exist
    expect($superAdminRole->name)->toBe('super admin');
    expect($adminRole->name)->toBe('admin');
    expect($karyawanRole->name)->toBe('karyawan');
});

test('user can be assigned a role', function () {
    // Create roles
    Role::create(['name' => 'super admin']);
    Role::create(['name' => 'admin']);
    Role::create(['name' => 'karyawan']);

    // Create user
    $user = User::factory()->create();

    // Assign role
    $user->assignRole('admin');

    // Assert user has role
    expect($user->hasRole('admin'))->toBeTrue();
    expect($user->hasRole('super admin'))->toBeFalse();
    expect($user->hasRole('karyawan'))->toBeFalse();
});

test('user factory can create user with specific role', function () {
    // Create roles first
    Role::create(['name' => 'super admin']);
    Role::create(['name' => 'admin']);
    Role::create(['name' => 'karyawan']);

    // Create users with different roles using factory
    $superAdmin = User::factory()->superAdmin()->create();
    $admin = User::factory()->admin()->create();
    $karyawan = User::factory()->karyawan()->create();

    // Assert roles
    expect($superAdmin->hasRole('super admin'))->toBeTrue();
    expect($admin->hasRole('admin'))->toBeTrue();
    expect($karyawan->hasRole('karyawan'))->toBeTrue();
});

test('user can have multiple roles', function () {
    // Create roles
    Role::create(['name' => 'admin']);
    Role::create(['name' => 'karyawan']);

    // Create user
    $user = User::factory()->create();

    // Assign multiple roles
    $user->assignRole(['admin', 'karyawan']);

    // Assert user has both roles
    expect($user->hasRole('admin'))->toBeTrue();
    expect($user->hasRole('karyawan'))->toBeTrue();
    expect($user->hasAllRoles(['admin', 'karyawan']))->toBeTrue();
});

test('role can be removed from user', function () {
    // Create roles
    Role::create(['name' => 'admin']);
    Role::create(['name' => 'karyawan']);

    // Create user with roles
    $user = User::factory()->create();
    $user->assignRole(['admin', 'karyawan']);

    // Remove one role
    $user->removeRole('admin');

    // Assert only karyawan role remains
    expect($user->hasRole('admin'))->toBeFalse();
    expect($user->hasRole('karyawan'))->toBeTrue();
});

test('user can be created with username', function () {
    $user = User::factory()->create([
        'username' => 'testuser',
    ]);

    expect($user->username)->toBe('testuser');
    expect($user->username)->not->toBeEmpty();
});

test('username must be unique', function () {
    // Create first user with username
    User::factory()->create(['username' => 'testuser']);

    // Attempt to create second user with same username should throw exception
    $this->expectException(\Illuminate\Database\QueryException::class);
    User::factory()->create(['username' => 'testuser']);
});

test('user authentication identifier is username', function () {
    $user = new User();
    expect($user->getAuthIdentifierName())->toBe('username');
});

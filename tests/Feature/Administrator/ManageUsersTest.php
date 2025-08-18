<?php

declare(strict_types=1);

use App\Livewire\Administrator\ManageUsers;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    // Create roles
    Role::create(['name' => 'super admin']);
    Role::create(['name' => 'admin']);
    Role::create(['name' => 'karyawan']);

    // Create admin user
    $this->adminUser = User::factory()->create();
    $this->adminUser->assignRole('admin');
    $this->adminUser->load('roles'); // Ensure roles are loaded

    // Create regular user
    $this->karyawanUser = User::factory()->create();
    $this->karyawanUser->assignRole('karyawan');
    $this->karyawanUser->load('roles'); // Ensure roles are loaded
});

test('admin can access manage users page', function () {
    // Test basic route access
    $response = $this->actingAs($this->adminUser)
        ->get(route('administrator.manage-users'));

    // Check status code (200)
    expect($response->status())->toBe(200);
});

test('karyawan cannot access manage users page', function () {
    $this->actingAs($this->karyawanUser)
        ->get(route('administrator.manage-users'))
        ->assertForbidden();
});

test('unauthenticated user cannot access manage users page', function () {
    $this->get(route('administrator.manage-users'))
        ->assertRedirect('/login');
});

test('admin can view users list', function () {
    Livewire::actingAs($this->adminUser)
        ->test(ManageUsers::class)
        ->assertSee($this->adminUser->name)
        ->assertSee($this->karyawanUser->name);
});

test('admin can search users', function () {
    Livewire::actingAs($this->adminUser)
        ->test(ManageUsers::class)
        ->set('search', $this->adminUser->name)
        ->assertSee($this->adminUser->name)
        ->assertDontSee($this->karyawanUser->name);
});

test('admin can filter users by role', function () {
    Livewire::actingAs($this->adminUser)
        ->test(ManageUsers::class)
        ->set('roleFilter', 'admin')
        ->assertSee($this->adminUser->name)
        ->assertDontSee($this->karyawanUser->name);
});

test('admin can create new user', function () {
    Livewire::actingAs($this->adminUser)
        ->test(ManageUsers::class)
        ->set('name', 'Test User')
        ->set('username', 'testuser')
        ->set('email', 'test@example.com')
        ->set('password', 'password123')
        ->set('password_confirmation', 'password123')
        ->call('createUser')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('users', [
        'name' => 'Test User',
        'username' => 'testuser',
        'email' => 'test@example.com',
    ]);

    $user = User::where('username', 'testuser')->first();
    expect($user->hasRole('karyawan'))->toBeTrue();
});

test('create user validates required fields', function () {
    Livewire::actingAs($this->adminUser)
        ->test(ManageUsers::class)
        ->call('createUser')
        ->assertHasErrors(['name', 'username', 'email', 'password']);
});

test('create user validates unique username and email', function () {
    Livewire::actingAs($this->adminUser)
        ->test(ManageUsers::class)
        ->set('name', 'Test User')
        ->set('username', $this->adminUser->username)
        ->set('email', $this->adminUser->email)
        ->set('password', 'password123')
        ->set('password_confirmation', 'password123')
        ->call('createUser')
        ->assertHasErrors(['username', 'email']);
});

test('admin can edit user', function () {
    Livewire::actingAs($this->adminUser)
        ->test(ManageUsers::class)
        ->call('setEditUser', $this->karyawanUser->id)
        ->assertSet('name', $this->karyawanUser->name)
        ->assertSet('username', $this->karyawanUser->username)
        ->assertSet('email', $this->karyawanUser->email)
        ->set('name', 'Updated Name')
        ->call('updateUser')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('users', [
        'id' => $this->karyawanUser->id,
        'name' => 'Updated Name',
    ]);
});

test('admin can update user password', function () {
    Livewire::actingAs($this->adminUser)
        ->test(ManageUsers::class)
        ->call('setEditUser', $this->karyawanUser->id)
        ->set('password', 'newpassword123')
        ->set('password_confirmation', 'newpassword123')
        ->call('updateUser')
        ->assertHasNoErrors();

    $this->karyawanUser->refresh();
    expect(Hash::check('newpassword123', $this->karyawanUser->password))->toBeTrue();
});

test('admin can delete user', function () {
    $userToDelete = User::factory()->create();
    $userToDelete->assignRole('karyawan');

    Livewire::actingAs($this->adminUser)
        ->test(ManageUsers::class)
        ->call('setDeleteUser', $userToDelete->id)
        ->call('deleteUser');

    $this->assertDatabaseMissing('users', ['id' => $userToDelete->id]);
});

test('admin cannot delete themselves', function () {
    $initialUserCount = User::count();

    Livewire::actingAs($this->adminUser)
        ->test(ManageUsers::class)
        ->call('setDeleteUser', $this->adminUser->id)
        ->call('deleteUser');

    // User count should remain the same (user not deleted)
    expect(User::count())->toBe($initialUserCount);

    // Admin user should still exist
    expect(User::find($this->adminUser->id))->not->toBeNull();
});

test('admin can manage user roles', function () {
    Livewire::actingAs($this->adminUser)
        ->test(ManageUsers::class)
        ->call('setSelectedUser', $this->karyawanUser->id)
        ->assertSet('selectedRoles', ['karyawan'])
        ->set('selectedRoles', ['admin', 'karyawan'])
        ->call('updateUserRoles');

    $this->karyawanUser->refresh();
    expect($this->karyawanUser->hasRole(['admin', 'karyawan']))->toBeTrue();
});

test('reset form clears all fields', function () {
    Livewire::actingAs($this->adminUser)
        ->test(ManageUsers::class)
        ->set('name', 'Test')
        ->set('username', 'test')
        ->set('email', 'test@example.com')
        ->set('password', 'password')
        ->call('resetForm')
        ->assertSet('name', '')
        ->assertSet('username', '')
        ->assertSet('email', '')
        ->assertSet('password', '');
});

<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Volt\Volt;

test('user can login with username using livewire', function () {
    // Create user with username
    $user = User::factory()->create([
        'username' => 'testuser',
        'password' => Hash::make('password'),
    ]);

    // Test login with username using Livewire
    Volt::test('auth.login')
        ->set('username', 'testuser')
        ->set('password', 'password')
        ->call('login')
        ->assertRedirect('/dashboard');

    // Should be authenticated
    expect(Auth::check())->toBeTrue();
    expect(Auth::user()->username)->toBe('testuser');
});

test('user cannot login with incorrect username using livewire', function () {
    // Create user
    User::factory()->create([
        'username' => 'testuser',
        'password' => Hash::make('password'),
    ]);

    // Attempt login with wrong username
    Volt::test('auth.login')
        ->set('username', 'wronguser')
        ->set('password', 'password')
        ->call('login')
        ->assertHasErrors('username');

    // Should not be authenticated
    expect(Auth::check())->toBeFalse();
});

test('user cannot login with incorrect password using livewire', function () {
    // Create user
    User::factory()->create([
        'username' => 'testuser',
        'password' => Hash::make('password'),
    ]);

    // Attempt login with wrong password
    Volt::test('auth.login')
        ->set('username', 'testuser')
        ->set('password', 'wrongpassword')
        ->call('login')
        ->assertHasErrors('username');

    // Should not be authenticated
    expect(Auth::check())->toBeFalse();
});

test('login form uses username field', function () {
    $response = $this->get('/login');
    
    $response->assertStatus(200);
    $response->assertSee('Username');
    $response->assertSee('Enter your username and password below to log in');
});

test('register form includes username field', function () {
    $response = $this->get('/register');
    
    $response->assertStatus(200);
    $response->assertSee('Username');
    $response->assertSee('Create an account');
});

test('livewire login component works with username', function () {
    // Create user
    $user = User::factory()->create([
        'username' => 'testuser',
        'password' => Hash::make('password'),
    ]);

    // Test Livewire login component
    Volt::test('auth.login')
        ->set('username', 'testuser')
        ->set('password', 'password')
        ->call('login')
        ->assertRedirect('/dashboard');

    // Verify user is authenticated
    expect(Auth::check())->toBeTrue();
    expect(Auth::user()->username)->toBe('testuser');
});

test('livewire register component includes username', function () {
    Volt::test('auth.register')
        ->set('name', 'Test User')
        ->set('username', 'newuser')
        ->set('email', 'test@example.com')
        ->set('password', 'password')
        ->set('password_confirmation', 'password')
        ->call('register')
        ->assertRedirect('/dashboard');

    // Verify user was created with username
    expect(Auth::check())->toBeTrue();
    expect(Auth::user()->username)->toBe('newuser');
    
    // Verify user exists in database
    $user = User::where('username', 'newuser')->first();
    expect($user)->not->toBeNull();
    expect($user->email)->toBe('test@example.com');
});

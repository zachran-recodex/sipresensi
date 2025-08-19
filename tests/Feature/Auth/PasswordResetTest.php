<?php

test('forgot password screen can be rendered', function () {
    $response = $this->get('/forgot-password');

    $response->assertStatus(200);
    $response->assertSee('Lupa kata sandi');
    $response->assertSee('Hubungi administrator untuk reset kata sandi Anda');
});

test('forgot password shows admin contact information', function () {
    $response = $this->get('/forgot-password');

    $response->assertStatus(200);
    $response->assertSee('Kontak Administrator');
    $response->assertSee('admin@company.com');  // Default from config
});

test('forgot password component can be instantiated', function () {
    // Test that the component can be rendered without errors
    $component = \Livewire\Volt\Volt::test('auth.forgot-password');
    
    $component->assertOk();
});

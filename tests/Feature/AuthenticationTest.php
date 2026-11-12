<?php

use App\Models\User;

test('login screen can be rendered', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
});

test('users can authenticate using the login screen', function () {
    $user = User::factory()->create();

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('home', absolute: false));
});

test('users cannot authenticate with invalid password', function () {
    $user = User::factory()->create();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
});

test('admin login reaches the dashboard while ordinary users remain outside management', function () {
    (new \Database\Seeders\SalesFoundationSeeder)->run();
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $response = $this->post('/login', ['email' => $admin->email, 'password' => 'password']);
    $response->assertRedirect(route('dashboard', absolute: false));
    $this->assertAuthenticatedAs($admin);
    $this->post('/logout');
    $customer = User::factory()->create();
    $customer->assignRole('pelanggan');
    $this->post('/login', ['email' => $customer->email, 'password' => 'password'])->assertRedirect(route('home', absolute: false));
    $this->get(route('stb-analysis.index'))->assertForbidden();
});

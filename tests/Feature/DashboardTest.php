<?php

use App\Models\User;

test('guests are redirected to the login page from the dashboard', function () {
    $this->get('/dashboard')->assertRedirect('/login');
});

test('guests cannot access protected settings pages', function () {
    $this->get('/settings/profile')->assertRedirect('/login');
    $this->get('/settings/password')->assertRedirect('/login');
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/dashboard')->assertOk();
});

test('authenticated users can visit the profile settings', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/settings/profile')->assertOk();
});

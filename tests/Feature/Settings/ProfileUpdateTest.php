<?php

use App\Models\User;

test('profile page is displayed', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/settings/profile')->assertOk();
});

test('profile information can be updated', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->patch('/settings/profile', [
        'name' => 'Budi Santoso',
        'email' => 'budi@example.com',
    ]);

    $response->assertSessionHasNoErrors()->assertRedirect('/settings/profile');

    $user->refresh();

    expect($user->name)->toBe('Budi Santoso')
        ->and($user->email)->toBe('budi@example.com');
});

test('email must be unique when updating profile', function () {
    User::factory()->create(['email' => 'dipakai@example.com']);
    $user = User::factory()->create();

    $response = $this->actingAs($user)->patch('/settings/profile', [
        'name' => 'Nama Baru',
        'email' => 'dipakai@example.com',
    ]);

    $response->assertSessionHasErrors('email');
});

test('user can delete their account', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->delete('/settings/profile', [
        'password' => 'password',
    ]);

    $response->assertSessionHasNoErrors()->assertRedirect('/');

    $this->assertGuest();

    expect($user->fresh())->toBeNull();
});

test('correct password must be provided to delete account', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->from('/settings/profile')->delete('/settings/profile', [
        'password' => 'wrong-password',
    ]);

    $response->assertSessionHasErrors('password')->assertRedirect('/settings/profile');

    expect($user->fresh())->not->toBeNull();
});

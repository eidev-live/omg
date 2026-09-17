<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('password can be updated', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->from('/settings/password')->put('/settings/password', [
        'current_password' => 'password',
        'password' => 'password-baru',
        'password_confirmation' => 'password-baru',
    ]);

    $response->assertSessionHasNoErrors();

    expect(Hash::check('password-baru', $user->refresh()->password))->toBeTrue();
});

test('correct password must be provided to update password', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->from('/settings/password')->put('/settings/password', [
        'current_password' => 'wrong-password',
        'password' => 'password-baru',
        'password_confirmation' => 'password-baru',
    ]);

    $response->assertSessionHasErrors('current_password');
});

test('new password must be at least 8 characters', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->from('/settings/password')->put('/settings/password', [
        'current_password' => 'password',
        'password' => 'pendek',
        'password_confirmation' => 'pendek',
    ]);

    $response->assertSessionHasErrors('password');
});

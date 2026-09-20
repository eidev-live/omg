<?php

use App\Models\Lead;
use App\Models\User;

test('guests cannot view the leads page', function () {
    $this->get('/leads')->assertRedirect('/login');
});

test('a guest can submit a lead and is redirected to whatsapp', function () {
    $waUrl = 'https://wa.me/6285770722652?text='.rawurlencode('saya tertarik untuk membeli telur di oh my egg');

    $response = $this->post('/leads', [
        'name' => 'Calon Pembeli',
        'email' => 'calon@example.com',
        'phone' => '08123456789',
    ]);

    $response->assertRedirect($waUrl);

    $this->assertDatabaseHas('leads', [
        'name' => 'Calon Pembeli',
        'email' => 'calon@example.com',
        'phone' => '08123456789',
    ]);
});

test('an inertia lead submission returns the whatsapp location', function () {
    $response = $this->withHeaders(['X-Inertia' => 'true'])->post('/leads', [
        'name' => 'Calon Pembeli',
        'email' => 'calon@example.com',
        'phone' => '08123456789',
    ]);

    $response->assertStatus(409);

    expect($response->headers->get('X-Inertia-Location'))
        ->toContain('https://wa.me/6285770722652')
        ->toContain(rawurlencode('saya tertarik untuk membeli telur di oh my egg'));
});

test('lead submission validates required fields', function () {
    $this->post('/leads', ['name' => '', 'email' => 'not-an-email', 'phone' => ''])
        ->assertSessionHasErrors(['name', 'email', 'phone']);

    expect(Lead::query()->count())->toBe(0);
});

test('the honeypot rejects bot submissions', function () {
    $this->post('/leads', [
        'name' => 'Bot',
        'email' => 'bot@example.com',
        'phone' => '0800000000',
        'website' => 'http://spam.example.com',
    ])->assertSessionHasErrors('website');

    expect(Lead::query()->count())->toBe(0);
});

test('authenticated users can view and export leads', function () {
    $user = User::factory()->create();
    Lead::query()->create(['name' => 'Budi', 'email' => 'budi@example.com', 'phone' => '0811']);

    $this->actingAs($user)
        ->get('/leads')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('leads/Index')->has('leads.data', 1));

    $content = $this->actingAs($user)->get('/leads/export')->streamedContent();

    expect($content)->toContain('budi@example.com');
});

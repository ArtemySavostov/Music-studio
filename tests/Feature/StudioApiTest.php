<?php

use App\Models\Booking;
use App\Models\Studio;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

test('guests cannot mutate studios through the API', function () {
    $studio = Studio::factory()->create();
    $this->postJson('/api/studios', ['name' => 'Новая', 'price_per_hour' => 600])->assertUnauthorized();
    $this->patchJson('/api/studios/'.$studio->id, ['name' => 'Изменено'])->assertUnauthorized();
    $this->deleteJson('/api/studios/'.$studio->id)->assertUnauthorized();
    expect($studio->fresh()->name)->toBe($studio->name);
});

test('clients can read studios but cannot manage them', function () {
    Sanctum::actingAs(User::factory()->create());
    $studio = Studio::factory()->create();
    $this->getJson('/api/studios')->assertOk();
    $this->postJson('/api/studios', ['name' => 'Новая', 'price_per_hour' => 600])->assertForbidden();
    $this->patchJson('/api/studios/'.$studio->id, ['name' => 'Изменено'])->assertForbidden();
    $this->deleteJson('/api/studios/'.$studio->id)->assertForbidden();
});

test('admins can create update and delete studios with feature flags', function () {
    Sanctum::actingAs(User::factory()->create(['role' => 'admin']));
    $response = $this->postJson('/api/studios', [
        'name' => 'Новая студия', 'price_per_hour' => '600.25', 'has_piano' => true,
    ])->assertCreated()->assertJsonPath('has_piano', true);
    $id = $response->json('id');
    $this->patchJson('/api/studios/'.$id, ['is_active' => false])
        ->assertOk()->assertJsonPath('is_active', false);
    $this->deleteJson('/api/studios/'.$id)->assertNoContent();
});

test('studio prices cannot be silently rounded or overflow the database column', function () {
    Sanctum::actingAs(User::factory()->create(['role' => 'admin']));
    foreach (['600.001', '100000000.00'] as $price) {
        $this->postJson('/api/studios', ['name' => 'Новая', 'price_per_hour' => $price])
            ->assertUnprocessable()->assertJsonValidationErrors('price_per_hour');
    }
});

test('registration cannot grant an admin role', function () {
    $this->postJson('/api/register', [
        'name' => 'Музыкант', 'email' => 'client@example.test',
        'password' => 'Studio-test-123', 'password_confirmation' => 'Studio-test-123',
        'role' => 'admin',
    ])->assertCreated();
    expect(User::where('email', 'client@example.test')->first()->role)->toBe('client');
});

test('studio deletion preserves booking history with a useful conflict response', function () {
    Sanctum::actingAs(User::factory()->create(['role' => 'admin']));
    $booking = Booking::factory()->create();
    $this->deleteJson('/api/studios/'.$booking->studio_id)
        ->assertConflict()->assertJsonStructure(['message']);
    $this->assertDatabaseHas('studios', ['id' => $booking->studio_id]);
    $this->assertDatabaseHas('bookings', ['id' => $booking->id]);
});

test('API login attempts are throttled', function () {
    for ($attempt = 0; $attempt < 10; $attempt++) {
        $this->postJson('/api/login', ['email' => 'missing@example.test', 'password' => 'wrong'])
            ->assertUnprocessable();
    }
    $this->postJson('/api/login', ['email' => 'missing@example.test', 'password' => 'wrong'])
        ->assertTooManyRequests();
});

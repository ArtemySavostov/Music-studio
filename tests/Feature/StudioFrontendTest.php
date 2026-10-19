<?php

use App\Livewire\AuthForm;
use App\Livewire\MyBookings;
use App\Livewire\StudioBooking;
use App\Livewire\StudioCatalog;
use App\Models\Studio;
use App\Models\User;
use Database\Seeders\StudioSeeder;
use Livewire\Livewire;

test('catalog searches active studios and filters by piano', function () {
    $this->seed(StudioSeeder::class);
    Studio::create(['name' => 'Скрытая', 'price_per_hour' => 500, 'is_active' => false]);

    Livewire::test(StudioCatalog::class)
        ->assertSee('Тихая комната')
        ->assertDontSee('Скрытая')
        ->set('piano', true)
        ->assertSee('Пиано')
        ->assertDontSee('Тихая комната')
        ->set('search', 'несуществующая студия')
        ->assertSee('Студии не найдены');
});

test('demo studios can be seeded repeatedly without replacing edits', function () {
    $this->seed(StudioSeeder::class);
    Studio::where('name', 'Пиано')->update(['price_per_hour' => 950]);
    $this->seed(StudioSeeder::class);

    expect(Studio::count())->toBe(3);
    expect(Studio::where('name', 'Пиано')->first()->price_per_hour)->toBe('950.00');
});

test('moving the start hour forward keeps a valid one hour booking', function () {
    $this->seed(StudioSeeder::class);
    $studio = Studio::where('name', 'Пиано')->first();

    Livewire::test(StudioBooking::class, ['studio' => $studio])
        ->set('start', 21)
        ->assertSet('end', 22)
        ->assertSee('900,00 ₽');
});

test('booking display refreshes the current hourly price', function () {
    $this->seed(StudioSeeder::class);
    $studio = Studio::where('name', 'Пиано')->first();
    $component = Livewire::test(StudioBooking::class, ['studio' => $studio]);
    $studio->update(['price_per_hour' => 1000]);

    $component->call('$refresh')->assertSee('1 000,00 ₽');
});

test('guest is sent to login before booking', function () {
    $this->seed(StudioSeeder::class);

    Livewire::test(StudioBooking::class, ['studio' => Studio::first()])
        ->call('book')->assertRedirect(route('login'));
    $this->assertDatabaseCount('bookings', 0);
});

test('selected session is restored after signing in', function () {
    $studio = Studio::factory()->create();
    $date = now()->addDays(3)->format('Y-m-d');
    Livewire::test(StudioBooking::class, ['studio' => $studio])
        ->set('date', $date)->set('start', 18)->set('end', 20)
        ->call('book')->assertRedirect(route('login'));

    $user = User::factory()->create(['password' => 'Studio-test-123']);
    Livewire::test(AuthForm::class)
        ->set('email', $user->email)->set('password', 'Studio-test-123')
        ->call('submit')->assertHasNoErrors()
        ->assertRedirect(route('studios.show', $studio));
    $this->assertAuthenticatedAs($user);
    Livewire::test(StudioBooking::class, ['studio' => $studio])
        ->assertSet('date', $date)->assertSet('start', 18)->assertSet('end', 20);
    expect(session()->has('booking.draft'))->toBeFalse();
});

test('a draft is not applied to another studio', function () {
    $studio = Studio::factory()->create();
    Livewire::test(StudioBooking::class, ['studio' => $studio])
        ->set('start', 18)->set('end', 20)->call('book');

    $this->actingAs(User::factory()->create());
    Livewire::test(StudioBooking::class, ['studio' => Studio::factory()->create()])
        ->assertSet('start', 10)->assertSet('end', 11);
    expect(session()->has('booking.draft'))->toBeTrue();
});

test('registration signs the user in', function () {
    Livewire::test(AuthForm::class, ['register' => true])
        ->set('name', 'Музыкант')
        ->set('email', 'musician@example.test')
        ->set('password', 'Studio-test-123')
        ->set('password_confirmation', 'Studio-test-123')
        ->call('submit')->assertHasNoErrors()->assertRedirect(route('home'));

    $this->assertAuthenticated();
});

test('booking is saved, appears in the account and cannot overlap', function () {
    $this->seed(StudioSeeder::class);
    $user = User::factory()->create();
    $studio = Studio::where('name', 'Пиано')->first();
    $this->actingAs($user);

    Livewire::test(StudioBooking::class, ['studio' => $studio])
        ->set('start', 14)->set('end', 16)
        ->call('book')->assertHasNoErrors()->assertRedirect(route('bookings'));

    $this->assertDatabaseHas('bookings', ['user_id' => $user->id, 'studio_id' => $studio->id, 'total_price' => 1800]);
    Livewire::test(MyBookings::class)->assertSee('Пиано')->assertSee('1 800,00 ₽');
    Livewire::test(StudioBooking::class, ['studio' => $studio])
        ->set('start', 15)->set('end', 17)
        ->call('book')->assertHasErrors('starts_at');
    $this->assertDatabaseCount('bookings', 1);
});

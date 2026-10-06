<?php

use App\Livewire\AuthForm;
use App\Livewire\MyBookings;
use App\Livewire\StudioBooking;
use App\Livewire\StudioCatalog;
use App\Models\Booking;
use App\Models\Studio;
use App\Models\User;
use App\Services\AvailabilityService;
use Livewire\Livewire;

beforeEach(function () {
    $this->travelTo(now()->setTime(12, 30));
});

test('catalog restores query filters and orders prices in both directions', function () {
    Studio::factory()->create(['name' => 'Дешёвая', 'price_per_hour' => 500]);
    Studio::factory()->create(['name' => 'Средняя', 'price_per_hour' => 900, 'has_piano' => true]);
    Studio::factory()->create(['name' => 'Дорогая', 'price_per_hour' => 1200, 'has_piano' => true]);
    Studio::factory()->create(['name' => 'Скрытая', 'price_per_hour' => 1000, 'is_active' => false]);

    Livewire::withQueryParams(['minPrice' => '800', 'maxPrice' => '1300', 'sort' => 'price_desc', 'piano' => '1'])
        ->test(StudioCatalog::class)->assertSet('minPrice', '800')->assertSet('maxPrice', '1300')
        ->assertSet('sort', 'price_desc')->assertSet('piano', true)
        ->assertViewHas('studios', fn ($studios) => $studios->pluck('name')->all() === ['Дорогая', 'Средняя'])->assertDontSee('Дешёвая')->assertDontSee('Скрытая')
        ->set('sort', 'price')->assertViewHas('studios', fn ($studios) => $studios->pluck('name')->all() === ['Средняя', 'Дорогая'])
        ->call('clearFilters')->assertSet('minPrice', '')->assertSet('maxPrice', '')->assertSet('piano', false)
        ->assertSee('Дешёвая');
});

test('invalid price bounds show a recoverable error without filtering incorrectly', function () {
    Studio::factory()->create(['name' => 'Студия', 'price_per_hour' => 900]);
    Livewire::test(StudioCatalog::class)->set('minPrice', '1000')->set('maxPrice', '500')
        ->assertHasErrors('maxPrice')->assertSee('Цена «до» должна быть не меньше цены «от».')
        ->set('maxPrice', '1200')->assertHasNoErrors()->assertSee('Студии не найдены')
        ->call('clearFilters')->assertSee('Студия')->assertHasNoErrors();
});

test('catalog search resets pagination and query page can be restored', function () {
    Studio::factory()->count(12)->create();
    Livewire::withQueryParams(['page' => '2'])->test(StudioCatalog::class)
        ->assertSee('2 / 2')->set('search', 'нет такой студии')->assertSet('paginators.page', 1)
        ->assertSee('Студии не найдены');
});

test('availability excludes past and occupied hours and ignores cancelled bookings', function () {
    $studio = Studio::factory()->create();
    Booking::factory()->create(['studio_id' => $studio->id, 'starts_at' => now()->setTime(14, 0), 'ends_at' => now()->setTime(16, 0)]);
    Booking::factory()->create(['studio_id' => $studio->id, 'starts_at' => now()->setTime(17, 0), 'ends_at' => now()->setTime(18, 0), 'status' => 'cancelled']);
    $service = app(AvailabilityService::class);
    $hours = $service->hours($studio, now()->format('Y-m-d'));
    expect($hours[12])->toBe('past')->and($hours[13])->toBe('free')->and($hours[14])->toBe('occupied')
        ->and($hours[15])->toBe('occupied')->and($hours[16])->toBe('free')->and($hours[17])->toBe('free');
    expect($service->allows($hours, 13, 14))->toBeTrue()->and($service->allows($hours, 13, 15))->toBeFalse()
        ->and($service->allows($hours, 16, 18))->toBeTrue()->and($service->allows($hours, 21, 23))->toBeFalse();
});

test('invalid dates and inactive studios never offer hours', function () {
    $studio = Studio::factory()->create();
    $service = app(AvailabilityService::class);
    foreach (['', 'not-a-date', '2026-02-30', now()->subDay()->format('Y-m-d')] as $date) {
        expect(array_unique(array_values($service->hours($studio, $date))))->toBe(['unavailable']);
    }
    $studio->update(['is_active' => false]);
    expect(array_unique(array_values($service->hours($studio, now()->addDay()->format('Y-m-d')))))->toBe(['unavailable']);
});

test('date change and duration update the selected range and enforce the whole interval', function () {
    $studio = Studio::factory()->create(['price_per_hour' => '900.25']);
    Booking::factory()->create(['studio_id' => $studio->id, 'starts_at' => now()->addDay()->setTime(15, 0), 'ends_at' => now()->addDay()->setTime(16, 0)]);
    Livewire::test(StudioBooking::class, ['studio' => $studio])->set('duration', 2)
        ->call('selectHour', 14)->assertSet('start', 10)
        ->call('selectHour', 16)->assertSet('start', 16)->assertSet('end', 18)->assertSee('1 800,50 ₽')
        ->call('selectHour', 21)->assertSet('start', 16)
        ->set('date', now()->addDays(2)->format('Y-m-d'))->call('selectHour', 14)
        ->assertSet('start', 14)->assertSet('end', 16)
        ->set('date', 'invalid')->assertSee('Выбранный интервал недоступен');
});

test('confirmation conflict refreshes hours without losing the selected date', function () {
    $studio = Studio::factory()->create();
    $this->actingAs(User::factory()->create());
    $component = Livewire::test(StudioBooking::class, ['studio' => $studio])->call('selectHour', 14);
    Booking::factory()->create(['studio_id' => $studio->id, 'starts_at' => now()->addDay()->setTime(14, 0), 'ends_at' => now()->addDay()->setTime(15, 0)]);
    $component->call('book')->assertHasErrors('starts_at')->assertSee('Выбранное время уже занято.')
        ->assertSee('Занято')->assertSet('date', now()->addDay()->format('Y-m-d'))
        ->call('selectHour', 16)->assertHasNoErrors()->call('book')->assertRedirect(route('bookings'));
    $this->assertDatabaseCount('bookings', 2);
});

test('poll refresh invalidates a selection taken by another client', function () {
    $studio = Studio::factory()->create();
    $component = Livewire::test(StudioBooking::class, ['studio' => $studio])->call('selectHour', 18);
    Booking::factory()->create(['studio_id' => $studio->id, 'starts_at' => now()->addDay()->setTime(18, 0), 'ends_at' => now()->addDay()->setTime(19, 0)]);
    $component->call('$refresh')->assertSee('Выбранный интервал недоступен')->assertSee('Занято');
});

test('login shows the saved rehearsal and restores its duration after authentication', function () {
    $studio = Studio::factory()->create(['name' => 'Репетиционная']);
    Livewire::test(StudioBooking::class, ['studio' => $studio])->set('duration', 3)->call('selectHour', 16)->call('book');
    Livewire::test(AuthForm::class)->assertSee('Репетиционная')->assertSee('16:00–19:00')->assertSee('Выбор сохранён');
    $this->actingAs(User::factory()->create());
    Livewire::test(StudioBooking::class, ['studio' => $studio])->assertSet('duration', 3)->assertSet('start', 16)->assertSet('end', 19);
});

test('account isolates the owner and sorts upcoming and historical bookings', function () {
    $owner = User::factory()->create();
    $soon = Studio::factory()->create(['name' => 'Ближайшая']);
    $later = Studio::factory()->create(['name' => 'Поздняя']);
    $old = Studio::factory()->create(['name' => 'Старая']);
    $recent = Studio::factory()->create(['name' => 'Недавняя', 'is_active' => false]);
    Booking::factory()->create(['user_id' => $owner->id, 'studio_id' => $later->id, 'starts_at' => now()->addDays(4)->setTime(10, 0), 'ends_at' => now()->addDays(4)->setTime(11, 0)]);
    Booking::factory()->create(['user_id' => $owner->id, 'studio_id' => $soon->id]);
    Booking::factory()->create(['user_id' => $owner->id, 'studio_id' => $old->id, 'starts_at' => now()->subDays(5)->setTime(10, 0), 'ends_at' => now()->subDays(5)->setTime(11, 0)]);
    Booking::factory()->create(['user_id' => $owner->id, 'studio_id' => $recent->id, 'starts_at' => now()->subDay()->setTime(10, 0), 'ends_at' => now()->subDay()->setTime(11, 0)]);
    Booking::factory()->create(['studio_id' => Studio::factory()->create(['name' => 'Чужая'])->id]);
    $this->actingAs($owner);
    Livewire::test(MyBookings::class)->assertViewHas('bookings', fn ($bookings) => $bookings->pluck('studio.name')->all() === ['Ближайшая', 'Поздняя'])->assertDontSee('Старая')
        ->assertDontSee('Чужая')->set('tab', 'history')->assertViewHas('bookings', fn ($bookings) => $bookings->pluck('studio.name')->all() === ['Недавняя', 'Старая'])
        ->assertDontSee('Поздняя')->assertDontSee('Чужая');
    $this->get(route('bookings', ['tab' => 'history']))->assertOk()->assertSee('Недавняя');
});

test('repeat booking opens an active studio and never creates a booking', function () {
    $user = User::factory()->create();
    $studio = Studio::factory()->create();
    Booking::factory()->create(['user_id' => $user->id, 'studio_id' => $studio->id]);
    $this->actingAs($user);
    Livewire::test(MyBookings::class)->assertSee('Забронировать снова');
    $this->get(route('studios.show', $studio))->assertOk();
    $this->assertDatabaseCount('bookings', 1);
    $studio->update(['is_active' => false]);
    Livewire::test(MyBookings::class)->assertDontSee('Забронировать снова');
});

test('guest cannot save an unavailable interval as a login draft', function () {
    $studio = Studio::factory()->create();
    Booking::factory()->create(['studio_id' => $studio->id]);
    Livewire::test(StudioBooking::class, ['studio' => $studio])->call('book')
        ->assertHasErrors('starts_at')->assertSee('Выбранное время недоступно.');
    expect(session()->has('booking.draft'))->toBeFalse();
    $this->assertDatabaseCount('bookings', 1);
});

test('current sessions remain upcoming and future cancelled sessions belong to history', function () {
    $user = User::factory()->create();
    $current = Studio::factory()->create(['name' => 'Сейчас играем']);
    $cancelled = Studio::factory()->create(['name' => 'Отменённая']);
    Booking::factory()->create(['user_id' => $user->id, 'studio_id' => $current->id, 'starts_at' => now()->setTime(12, 0), 'ends_at' => now()->setTime(13, 0)]);
    Booking::factory()->create(['user_id' => $user->id, 'studio_id' => $cancelled->id, 'status' => 'cancelled']);
    $this->actingAs($user);
    Livewire::test(MyBookings::class)->assertSee('РЕПЕТИЦИЯ ИДЁТ')->assertSee('Сейчас играем')
        ->assertDontSee('Отменённая')->set('tab', 'history')->assertSee('Отменённая')->assertSee('Отменено');
});

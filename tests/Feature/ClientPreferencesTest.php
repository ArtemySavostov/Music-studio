<?php

use App\Livewire\StudioCatalog;
use App\Models\Booking;
use App\Models\Studio;
use App\Models\User;
use Livewire\Livewire;

test('favorites filter uses the whole catalog and preserves saved ids when resetting filters', function () {
    $studios = Studio::factory()->count(12)->create();
    $hidden = Studio::factory()->create(['is_active' => false]);
    $ids = [...$studios->pluck('id')->all(), $hidden->id];

    Livewire::withQueryParams(['favoritesOnly' => '1', 'page' => '2'])->test(StudioCatalog::class)
        ->call('restoreFavorites', $ids)->assertSet('paginators.page', 2)
        ->assertViewHas('studios', fn ($result) => $result->count() === 3);

    Livewire::withQueryParams(['page' => '2'])->test(StudioCatalog::class)
        ->set('favoriteIds', $ids)->assertSet('paginators.page', 2);

    Livewire::withQueryParams(['favoritesOnly' => '1'])->test(StudioCatalog::class)
        ->assertSet('favoritesOnly', true)->assertSee('В избранном ничего не найдено')
        ->set('favoriteIds', $ids)
        ->assertViewHas('studios', fn ($result) => $result->total() === 12 && $result->count() === 9)
        ->assertDontSee($hidden->name)->call('setPage', 2)
        ->assertViewHas('studios', fn ($result) => $result->count() === 3)
        ->set('favoriteIds', [$studios->first()->id])->assertSet('paginators.page', 1)
        ->assertViewHas('studios', fn ($result) => $result->total() === 1)
        ->set('minPrice', '99999999')->assertViewHas('studios', fn ($result) => $result->total() === 0)
        ->call('clearFilters')->assertSet('favoritesOnly', false)
        ->assertSet('favoriteIds', [$studios->first()->id])
        ->assertViewHas('studios', fn ($result) => $result->total() === 12);
});

test('invalid or oversized favorite lists do not expose studios or break rendering', function () {
    Studio::factory()->create(['name' => 'Активная']);
    foreach ([[-1], ['invalid'], [[1]], range(1, 201)] as $ids) {
        Livewire::test(StudioCatalog::class)->set('favoritesOnly', true)->set('favoriteIds', $ids)
            ->assertViewHas('studios', fn ($result) => $result->total() === 0)
            ->assertSee('В избранном ничего не найдено');
    }
});

test('catalog rehearsal shortcut only shows the signed in users nearest confirmed rehearsal', function () {
    $this->travelTo(now()->setTime(12, 0));
    $user = User::factory()->create();
    $own = Booking::factory()->create(['user_id' => $user->id, 'starts_at' => now()->addDay()->setTime(14, 0), 'ends_at' => now()->addDay()->setTime(15, 0)]);
    Booking::factory()->create(['user_id' => $user->id, 'starts_at' => now()->addDays(2)->setTime(10, 0), 'ends_at' => now()->addDays(2)->setTime(11, 0)]);
    Booking::factory()->create(['user_id' => $user->id, 'starts_at' => now()->setTime(13, 0), 'ends_at' => now()->setTime(14, 0), 'status' => 'cancelled']);
    Booking::factory()->create(['starts_at' => now()->setTime(13, 0), 'ends_at' => now()->setTime(14, 0)]);

    Livewire::test(StudioCatalog::class)->assertViewHas('nextBooking', null)->assertDontSee('СКОРО ИГРАЕМ');
    $this->actingAs($user);
    Livewire::test(StudioCatalog::class)->assertViewHas('nextBooking', fn ($booking) => $booking->is($own))
        ->assertSee('СКОРО ИГРАЕМ')->assertSee('К бронированиям');
    $own->update(['starts_at' => now()->setTime(11, 0), 'ends_at' => now()->setTime(13, 0)]);
    Livewire::test(StudioCatalog::class)->assertSee('ВАША РЕПЕТИЦИЯ ИДЁТ');
});

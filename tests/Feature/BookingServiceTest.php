<?php

use App\Models\Booking;
use App\Models\Studio;
use App\Models\User;
use App\Services\BookingService;
use Illuminate\Validation\ValidationException;

test('preview and confirmed price preserve kopecks', function () {
    $studio = Studio::factory()->create(['price_per_hour' => '600.25']);
    expect($studio->priceForHours(3))->toBe('1800.75');
    $date = now()->addDay()->format('Y-m-d');
    $booking = app(BookingService::class)->create(User::factory()->create(), [
        'studio_id' => $studio->id, 'starts_at' => $date.' 14:00:00', 'ends_at' => $date.' 17:00:00',
    ]);
    expect($booking->total_price)->toBe('1800.75');
});

test('adjacent sessions are allowed without treating their boundary as an overlap', function () {
    $studio = Studio::factory()->create();
    $user = User::factory()->create();
    $date = now()->addDay()->format('Y-m-d');
    Booking::factory()->create(['studio_id' => $studio->id, 'starts_at' => $date.' 19:00:00', 'ends_at' => $date.' 21:00:00']);
    $booking = app(BookingService::class)->create($user, [
        'studio_id' => $studio->id, 'starts_at' => $date.' 21:00:00', 'ends_at' => $date.' 22:00:00',
    ]);
    expect($booking->status)->toBe('confirmed');
});

test('unavailable studios cannot be booked', function () {
    $studio = Studio::factory()->create(['is_active' => false]);
    $date = now()->addDay()->format('Y-m-d');
    app(BookingService::class)->create(User::factory()->create(), [
        'studio_id' => $studio->id, 'starts_at' => $date.' 10:00:00', 'ends_at' => $date.' 11:00:00',
    ]);
})->throws(ValidationException::class);

test('sessions must stay within opening hours and use whole hours', function (string $start, string $end) {
    $studio = Studio::factory()->create();
    $date = now()->addDay()->format('Y-m-d');
    app(BookingService::class)->create(User::factory()->create(), [
        'studio_id' => $studio->id, 'starts_at' => $date.' '.$start, 'ends_at' => $date.' '.$end,
    ]);
})->with([
    ['09:00:00', '11:00:00'],
    ['21:00:00', '23:00:00'],
    ['10:30:00', '12:00:00'],
    ['12:00:00', '11:00:00'],
])->throws(ValidationException::class);

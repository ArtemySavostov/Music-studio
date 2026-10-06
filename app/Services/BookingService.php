<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Studio;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BookingService
{
    public function create(User $user, array $data): Booking
    {
        $startsAt = Carbon::parse($data['starts_at']);
        $endsAt = Carbon::parse($data['ends_at']);

        if (
            !$startsAt->isSameDay($endsAt)
            || $startsAt->hour < 10
            || $endsAt->hour > 22
            || $startsAt->minute !== 0
            || $startsAt->second !== 0
            || $endsAt->minute !== 0
            || $endsAt->second !== 0
            || $startsAt->lte(now())
            || $endsAt->lte($startsAt)
        ) {
            throw ValidationException::withMessages([
                'starts_at' => [
                    'Выберите будущий интервал в пределах одного дня '
                    .'с 10:00 до 22:00, по целым часам.',
                ],
            ]);
        }

        return DB::transaction(function () use ($user, $data, $startsAt, $endsAt) {
            $studio = Studio::whereKey($data['studio_id'])
                ->lockForUpdate()
                ->firstOrFail();

            if (!$studio->is_active) {
                throw ValidationException::withMessages([
                    'studio_id' => ['Комната недоступна для бронирования.'],
                ]);
            }

            $isOccupied = Booking::where('studio_id', $studio->id)
                ->where('status', 'confirmed')
                ->where('starts_at', '<', $endsAt)
                ->where('ends_at', '>', $startsAt)
                ->exists();

            if ($isOccupied) {
                throw ValidationException::withMessages([
                    'starts_at' => ['Выбранное время уже занято.'],
                ]);
            }

            $hours = (int) $startsAt->diffInHours($endsAt);

            [$rubles, $kopecks] = explode('.', $studio->price_per_hour);
            $hourlyPrice = (int) $rubles * 100 + (int) $kopecks;
            $totalKopecks = $hourlyPrice * $hours;

            $totalPrice = intdiv($totalKopecks, 100)
                .'.'.str_pad((string) ($totalKopecks % 100), 2, '0', STR_PAD_LEFT);

            return Booking::create([
                'user_id' => $user->id,
                'studio_id' => $studio->id,
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                'total_price' => $totalPrice,
                'status' => 'confirmed',
            ]);
        });
    }
}
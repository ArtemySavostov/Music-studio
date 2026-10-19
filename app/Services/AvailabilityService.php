<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Studio;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;

class AvailabilityService
{
    public const OPEN_HOUR = 10;

    public const CLOSE_HOUR = 22;

    /** @return array<int, string> Hour start => free, occupied, past or unavailable. */
    public function hours(Studio $studio, string $date): array
    {
        $hours = array_fill_keys(range(self::OPEN_HOUR, self::CLOSE_HOUR - 1), 'unavailable');
        if (! $studio->is_active || Validator::make(['date' => $date], ['date' => 'required|date_format:Y-m-d|after_or_equal:today'])->fails()) {
            return $hours;
        }

        $day = Carbon::parse($date)->startOfDay();
        $bookings = Booking::where('studio_id', $studio->id)->where('status', 'confirmed')
            ->where('starts_at', '<', $day->copy()->addDay())
            ->where('ends_at', '>', $day)->get(['starts_at', 'ends_at']);
        foreach ($hours as $hour => $status) {
            $start = $day->copy()->setTime($hour, 0);
            $end = $start->copy()->addHour();
            $hours[$hour] = $start->lte(now()) ? 'past' : 'free';
            foreach ($bookings as $booking) {
                if ($booking->starts_at->lt($end) && $booking->ends_at->gt($start)) {
                    $hours[$hour] = 'occupied';
                    break;
                }
            }
        }

        return $hours;
    }

    /** @param array<int, string> $hours */
    public function allows(array $hours, int $start, int $end): bool
    {
        if ($start < self::OPEN_HOUR || $end > self::CLOSE_HOUR || $end <= $start) {
            return false;
        }
        foreach (range($start, $end - 1) as $hour) {
            if (($hours[$hour] ?? null) !== 'free') {
                return false;
            }
        }

        return true;
    }
}

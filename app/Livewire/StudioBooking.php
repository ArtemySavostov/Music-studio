<?php

namespace App\Livewire;

use App\Models\Studio;
use App\Services\AvailabilityService;
use App\Services\BookingService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class StudioBooking extends Component
{
    public Studio $studio;

    public string $date = '';

    public int $start = 10;

    public int $end = 11;

    public int $duration = 1;

    public function mount(Studio $studio): void
    {
        abort_unless($studio->is_active, 404);
        $this->studio = $studio;
        $this->date = now()->addDay()->format('Y-m-d');
        $draft = session()->get('booking.draft');
        if (Auth::check() && is_array($draft) && ($draft['studio_id'] ?? null) === $studio->id) {
            $this->date = $draft['date'];
            $this->start = $draft['start'];
            $this->end = $draft['end'];
            $this->duration = $this->end - $this->start;
            session()->forget('booking.draft');
        }
    }

    public function book(BookingService $service): void
    {
        $this->validate([
            'date' => 'required|date_format:Y-m-d|after_or_equal:today',
            'start' => 'required|integer|between:10,21',
            'end' => 'required|integer|between:11,22|gt:start',
        ], [
            'date.*' => 'Выберите сегодняшнюю или будущую дату.',
            'start.*' => 'Выберите час начала с 10:00 до 21:00.',
            'end.*' => 'Окончание должно быть позже начала, до 22:00.',
        ]);
        if (! Auth::check()) {
            $studio = $this->studio->fresh();
            abort_unless($studio?->is_active, 404);
            $availability = app(AvailabilityService::class);
            if (! $availability->allows($availability->hours($studio, $this->date), $this->start, $this->end)) {
                $this->addError('starts_at', 'Выбранное время недоступно. Выберите свободный интервал.');

                return;
            }
            session()->put('booking.draft', [
                'studio_id' => $this->studio->id,
                'date' => $this->date,
                'start' => $this->start,
                'end' => $this->end,
            ]);
            session()->put('url.intended', route('studios.show', $this->studio));
            $this->redirectRoute('login');

            return;
        }
        try {
            $service->create(Auth::user(), [
                'studio_id' => $this->studio->id,
                'starts_at' => sprintf('%s %02d:00:00', $this->date, $this->start),
                'ends_at' => sprintf('%s %02d:00:00', $this->date, $this->end),
            ]);
        } catch (ValidationException $exception) {
            $this->setErrorBag($exception->errors());

            return;
        }
        session()->flash('success', 'Студия забронирована. Ждём вас на репетиции!');
        $this->redirectRoute('bookings');
    }

    public function updatedStart(): void
    {
        // Keep at least one hour selected when moving the start time forward.
        if ($this->end <= $this->start && $this->start >= 10 && $this->start <= 21) {
            $this->end = $this->start + 1;
        }
        $this->duration = max(1, min(12, $this->end - $this->start));
        $this->resetValidation();
    }

    public function updatedEnd(): void
    {
        $this->duration = max(1, min(12, $this->end - $this->start));
        $this->resetValidation();
    }

    public function updatedDuration(): void
    {
        $this->duration = max(1, min(12, $this->duration));
        $this->end = $this->start + $this->duration;
        $this->resetValidation();
    }

    public function updatedDate(): void
    {
        $this->resetValidation();
    }

    public function selectHour(int $hour, AvailabilityService $availability): void
    {
        $studio = $this->studio->fresh();
        abort_unless($studio?->is_active, 404);
        if ($availability->allows($availability->hours($studio, $this->date), $hour, $hour + $this->duration)) {
            $this->start = $hour;
            $this->end = $hour + $this->duration;
            $this->resetValidation();
        }
    }

    public function render(): View
    {
        $studio = $this->studio->fresh();
        abort_unless($studio?->is_active, 404);
        $this->studio = $studio;
        $availability = app(AvailabilityService::class);
        $hours = $availability->hours($studio, $this->date);
        $canBook = $availability->allows($hours, $this->start, $this->end);
        $total = $this->studio->priceForHours(max(0, min(12, $this->end - $this->start)));

        return view('livewire.studio-booking', compact('hours', 'availability', 'canBook', 'total'))
            ->layout('layouts.app', ['title' => $this->studio->name]);
    }
}

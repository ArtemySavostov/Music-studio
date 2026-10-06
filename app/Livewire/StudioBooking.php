<?php

namespace App\Livewire;

use App\Models\Booking;
use App\Models\Studio;
use App\Services\BookingService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class StudioBooking extends Component
{
    public Studio $studio;

    public string $date = '';

    public int $start = 10;

    public int $end = 11;

    public function mount(Studio $studio): void
    {
        abort_unless($studio->is_active, 404);
        $this->studio = $studio;
        $this->date = now()->addDay()->format('Y-m-d');
    }

    public function book(BookingService $service): void
    {
        if (! Auth::check()) {
            session()->put('url.intended', route('studios.show', $this->studio));
            $this->redirectRoute('login');

            return;
        }
        $this->validate([
            'date' => 'required|date_format:Y-m-d|after_or_equal:today',
            'start' => 'required|integer|between:10,21',
            'end' => 'required|integer|between:11,22|gt:start',
        ], [
            'date.*' => 'Выберите сегодняшнюю или будущую дату.',
            'start.*' => 'Выберите час начала с 10:00 до 21:00.',
            'end.*' => 'Окончание должно быть позже начала, до 22:00.',
        ]);
        $service->create(Auth::user(), [
            'studio_id' => $this->studio->id,
            'starts_at' => sprintf('%s %02d:00:00', $this->date, $this->start),
            'ends_at' => sprintf('%s %02d:00:00', $this->date, $this->end),
        ]);
        session()->flash('success', 'Студия забронирована. Ждём вас на репетиции!');
        $this->redirectRoute('bookings');
    }

    public function updatedStart(): void
    {
        // Keep at least one hour selected when moving the start time forward.
        if ($this->end <= $this->start && $this->start >= 10 && $this->start <= 21) {
            $this->end = $this->start + 1;
        }
    }

    public function render(): View
    {
        $studio = $this->studio->fresh();
        abort_unless($studio?->is_active, 404);
        $this->studio = $studio;
        $occupied = Booking::where('studio_id', $this->studio->id)
            ->where('status', 'confirmed')->whereDate('starts_at', $this->date)
            ->orderBy('starts_at')->get();
        $total = max(0, min(12, $this->end - $this->start)) * (float) $this->studio->price_per_hour;

        return view('livewire.studio-booking', compact('occupied', 'total'))
            ->layout('layouts.app', ['title' => $this->studio->name]);
    }
}

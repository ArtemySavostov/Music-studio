<?php

namespace App\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class MyBookings extends Component
{
    use WithPagination;

    #[Url(history: true)]
    public string $tab = 'upcoming';

    public function updatedTab(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        abort_unless(Auth::check(), 401);

        $upcoming = Auth::user()->booking()->where('status', 'confirmed')->where('ends_at', '>', now());
        $history = Auth::user()->booking()->where(fn ($query) => $query->where('ends_at', '<=', now())->orWhere('status', '!=', 'confirmed'));

        return view('livewire.my-bookings', [
            'nextBooking' => (clone $upcoming)->with('studio')->orderBy('starts_at')->first(),
            'upcomingCount' => (clone $upcoming)->count(),
            'historyCount' => (clone $history)->count(),
            'bookings' => ($this->tab === 'history' ? $history->orderByDesc('starts_at') : $upcoming->orderBy('starts_at'))
                ->with('studio')->orderBy('id')->paginate(10),
        ])->layout('layouts.app', ['title' => 'Мои бронирования']);
    }
}

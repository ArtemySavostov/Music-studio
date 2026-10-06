<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class MyBookings extends Component
{
    use WithPagination;

    public function render()
    {
        abort_unless(Auth::check(), 401);

        return view('livewire.my-bookings', [
            'bookings' => Auth::user()->booking()->with('studio')->orderByDesc('starts_at')->paginate(10),
        ])->layout('layouts.app', ['title' => 'Мои бронирования']);
    }
}

<?php

namespace App\Livewire;

use App\Models\Studio;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class StudioCatalog extends Component
{
    use WithPagination;

    public string $search = '';

    public bool $piano = false;

    public string $sort = 'name';

    public function updated(string $property): void
    {
        if (in_array($property, ['search', 'piano', 'sort'])) {
            $this->resetPage();
        }
    }

    public function render(): View
    {
        $studios = Studio::query()->where('is_active', true)
            ->when($this->search !== '', fn ($q) => $q->where('name', 'like', '%'.$this->search.'%'))
            ->when($this->piano, fn ($q) => $q->where('has_piano', true))
            ->orderBy($this->sort === 'price' ? 'price_per_hour' : 'name')
            ->paginate(9);

        return view('livewire.studio-catalog', compact('studios'))->layout('layouts.app', ['title' => 'Студии']);
    }
}

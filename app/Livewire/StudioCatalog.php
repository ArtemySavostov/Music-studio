<?php

namespace App\Livewire;

use App\Models\Studio;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class StudioCatalog extends Component
{
    use WithPagination;

    #[Url(history: true)]
    public string $search = '';

    #[Url(history: true)]
    public bool $piano = false;

    #[Url(history: true)]
    public string $sort = 'name';

    #[Url(history: true)]
    public string $minPrice = '';

    #[Url(history: true)]
    public string $maxPrice = '';

    public function clearFilters(): void
    {
        $this->reset('search', 'piano', 'sort', 'minPrice', 'maxPrice');
        $this->resetValidation();
        $this->resetPage();
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['search', 'piano', 'sort', 'minPrice', 'maxPrice'])) {
            $this->resetPage();
        }
    }

    public function render(): View
    {
        $validator = Validator::make(
            ['minPrice' => $this->minPrice, 'maxPrice' => $this->maxPrice],
            [
                'minPrice' => 'nullable|numeric|decimal:0,2|min:0|max:99999999.99',
                'maxPrice' => 'nullable|numeric|decimal:0,2|min:0|max:99999999.99'
                    .($this->minPrice !== '' && is_numeric($this->minPrice) ? '|gte:minPrice' : ''),
            ],
            ['numeric' => 'Введите цену числом.', 'decimal' => 'Не более двух знаков после точки.',
                'min' => 'Цена не может быть отрицательной.', 'max' => 'Слишком большая цена.',
                'gte' => 'Цена «до» должна быть не меньше цены «от».'],
        );
        $validPrices = $validator->passes();
        $this->setErrorBag($validator->errors());
        $studios = Studio::query()->where('is_active', true)
            ->when($this->search !== '', fn ($q) => $q->where('name', 'like', '%'.mb_substr($this->search, 0, 100).'%'))
            ->when($this->piano, fn ($q) => $q->where('has_piano', true))
            ->when($validPrices && $this->minPrice !== '', fn ($q) => $q->where('price_per_hour', '>=', $this->minPrice))
            ->when($validPrices && $this->maxPrice !== '', fn ($q) => $q->where('price_per_hour', '<=', $this->maxPrice))
            ->orderBy(in_array($this->sort, ['price', 'price_desc'], true) ? 'price_per_hour' : 'name', $this->sort === 'price_desc' ? 'desc' : 'asc')
            ->orderBy('id')
            ->paginate(9);

        return view('livewire.studio-catalog', compact('studios'))->layout('layouts.app', ['title' => 'Студии']);
    }
}

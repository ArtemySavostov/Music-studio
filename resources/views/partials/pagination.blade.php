@if($paginator->hasPages())
    <nav class="pagination" aria-label="Страницы результатов">
        <button class="button secondary small" wire:click="previousPage" @disabled($paginator->onFirstPage())>← Назад</button>
        <span>{{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}</span>
        <button class="button secondary small" wire:click="nextPage" @disabled(! $paginator->hasMorePages())>Далее →</button>
    </nav>
@endif

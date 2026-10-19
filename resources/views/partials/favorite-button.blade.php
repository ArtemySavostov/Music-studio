<button type="button" class="{{ ($compact ?? false) ? 'favorite-toggle glass' : 'button secondary favorite-button' }}" x-data
    aria-label="Избранное: {{ $studio->name }}" :aria-pressed="$store.favorites.has({{ $studio->id }})"
    :class="{ 'is-saved': $store.favorites.has({{ $studio->id }}) }" @click="$store.favorites.toggle({{ $studio->id }})">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8L12 21l8.8-8.6a5.5 5.5 0 0 0 0-7.8Z"/></svg>
    @unless($compact ?? false)<span x-text="$store.favorites.has({{ $studio->id }}) ? 'В избранном' : 'В избранное'">В избранное</span>@endunless
</button>

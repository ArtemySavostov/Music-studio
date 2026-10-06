<div class="bookings-page">
    <p class="eyebrow">ЛИЧНЫЙ КАБИНЕТ</p>
    <div class="section-heading"><h1>Мои бронирования</h1><a class="button small" href="{{ route('home') }}">Выбрать студию ↗</a></div>
    <p class="muted">Ваши планы и история репетиций · {{ config('app.timezone') }}</p>
    @if($nextBooking)
        <section class="next-booking"><div><p class="eyebrow">{{ $nextBooking->starts_at->isPast() ? 'РЕПЕТИЦИЯ ИДЁТ' : 'БЛИЖАЙШАЯ РЕПЕТИЦИЯ' }}</p><h2>{{ $nextBooking->studio?->name ?? 'Студия недоступна' }}</h2><p>{{ $nextBooking->starts_at->locale('ru')->translatedFormat('j F, D') }} · {{ $nextBooking->starts_at->format('H:i') }}–{{ $nextBooking->ends_at->format('H:i') }}</p></div><strong>{{ number_format((float) $nextBooking->total_price, 2, ',', ' ') }} ₽</strong></section>
    @endif
    <div class="booking-tabs glass" role="group" aria-label="Разделы бронирований">
        <button type="button" class="tab-button {{ $tab !== 'history' ? 'selected' : '' }}" wire:click="$set('tab', 'upcoming')" aria-pressed="{{ $tab !== 'history' ? 'true' : 'false' }}">Предстоящие <span>{{ $upcomingCount }}</span></button>
        <button type="button" class="tab-button {{ $tab === 'history' ? 'selected' : '' }}" wire:click="$set('tab', 'history')" aria-pressed="{{ $tab === 'history' ? 'true' : 'false' }}">История <span>{{ $historyCount }}</span></button>
    </div>
    <div class="loading-status" role="status"><span wire:loading.delay wire:target="tab,nextPage,previousPage">Обновляем бронирования…</span></div>
    <div class="booking-list" wire:loading.class="is-loading" wire:target="tab,nextPage,previousPage">
        @forelse($bookings as $booking)
            <article class="booking-row" wire:key="booking-{{ $booking->id }}">
                <div class="date-tile"><strong>{{ $booking->starts_at->format('d') }}</strong><span>{{ $booking->starts_at->locale('ru')->translatedFormat('M Y') }}</span></div>
                <div class="booking-info"><h3>{{ $booking->studio?->name ?? 'Студия удалена' }}</h3><span class="muted">{{ $booking->starts_at->format('H:i') }}–{{ $booking->ends_at->format('H:i') }} · Бронь №{{ $booking->id }}</span></div>
                <span class="badge {{ $booking->status === 'cancelled' ? 'cancelled' : '' }}">{{ $booking->status === 'confirmed' ? ($booking->ends_at->isPast() ? 'Завершено' : ($booking->starts_at->isPast() ? 'Идёт сейчас' : 'Подтверждено')) : ($booking->status === 'cancelled' ? 'Отменено' : $booking->status) }}</span>
                <div class="booking-row-actions"><strong>{{ number_format((float) $booking->total_price, 2, ',', ' ') }} ₽</strong>@if($booking->studio?->is_active)<a class="text-button" href="{{ route('studios.show', $booking->studio) }}">Забронировать снова ↗</a>@endif</div>
            </article>
        @empty
            <div class="empty"><span class="empty-symbol" aria-hidden="true">♫</span><h2>{{ $tab === 'history' ? 'История ещё впереди' : 'Время для новой репетиции' }}</h2><p>{{ $tab === 'history' ? 'Завершённые и отменённые бронирования появятся здесь.' : 'Выберите студию и свободные часы. Здесь будут ваши ближайшие репетиции.' }}</p><a class="button" href="{{ route('home') }}">Посмотреть студии ↗</a></div>
        @endforelse
    </div>
    @include('partials.pagination', ['paginator' => $bookings])
</div>

<div class="bookings-page">
    <p class="eyebrow">ЛИЧНЫЙ КАБИНЕТ</p>
    <div class="section-heading">
        <h1>Мои бронирования</h1>
        <a class="button small" href="{{ route('home') }}">Выбрать студию ↗</a>
    </div>
    <p class="muted">Ваши планы и история репетиций · {{ config('app.timezone') }}</p>
    <div class="booking-list">
        @forelse($bookings as $booking)
            <article class="booking-row" wire:key="booking-{{ $booking->id }}">
                <div class="date-tile">
                    <strong>{{ $booking->starts_at->format('d') }}</strong>
                    <span>{{ $booking->starts_at->locale('ru')->translatedFormat('M Y') }}</span>
                </div>
                <div class="booking-info">
                    <h3>{{ $booking->studio?->name ?? 'Студия удалена' }}</h3>
                    <span class="muted">{{ $booking->starts_at->format('H:i') }}–{{ $booking->ends_at->format('H:i') }} · Бронь №{{ $booking->id }}</span>
                </div>
                <span class="badge">{{ $booking->status === 'confirmed' ? ($booking->ends_at->isPast() ? 'Завершено' : 'Подтверждено') : ($booking->status === 'cancelled' ? 'Отменено' : $booking->status) }}</span>
                <strong>{{ number_format((float) $booking->total_price, 2, ',', ' ') }} ₽</strong>
            </article>
        @empty
            <div class="empty">
                <span class="empty-symbol">♫</span>
                <h2>Всё начинается с первой репетиции</h2>
                <p>Выберите студию и удобное время. Здесь появятся ваши бронирования.</p>
                <a class="button" href="{{ route('home') }}">Посмотреть студии ↗</a>
            </div>
        @endforelse
    </div>
    @include('partials.pagination', ['paginator' => $bookings])
</div>

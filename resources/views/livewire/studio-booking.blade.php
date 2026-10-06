<div class="detail-page">
    <a class="back-link" href="{{ route('home') }}">← Все студии</a>
    <div class="detail-grid">
        <section>
            <div class="studio-art detail-art art-{{ $studio->id % 3 }}" aria-hidden="true">
                <div class="keys">
                    @for($i=0;$i<9;$i++)
                        <i></i>
                    @endfor
                </div>
                <span class="art-word">{{ $studio->has_piano ? 'piano.' : 'sound.' }}</span>
            </div>
            <p class="eyebrow">ПРОСТРАНСТВО ДЛЯ МУЗЫКИ</p>
            <h1>{{ $studio->name }}</h1>
            <p class="detail-description">{{ $studio->description ?: 'Приходите репетировать, сочинять и искать своё звучание.' }}</p>
            <div class="features">
                <span>◷ 10:00–22:00</span>
                <span>♫ {{ $studio->has_piano ? 'Есть пианино' : 'Без пианино' }}</span>
                <span>От 1 часа</span>
            </div>
        </section>
        <aside class="booking-panel">
            <p class="eyebrow">ВАША РЕПЕТИЦИЯ</p>
            <h2>
                {{ number_format((float) $studio->price_per_hour, 2, ',', ' ') }} ₽
                <small>/ час</small>
            </h2>
            <p class="muted">Выберите удобное время</p>
            <form wire:submit="book" class="form-stack">
                <label>
                    Дата
                    <input type="date" wire:model.live="date" min="{{ now()->format('Y-m-d') }}" required>
                </label>
                <div class="time-fields">
                    <label>
                        Начало
                        <select wire:model.live="start">
                            @for($hour=10;$hour<=21;$hour++)
                                <option value="{{ $hour }}">{{ $hour }}:00</option>
                            @endfor
                        </select>
                    </label>
                    <label>
                        Окончание
                        <select wire:model.live="end">
                            @for($hour=11;$hour<=22;$hour++)
                                <option value="{{ $hour }}">{{ $hour }}:00</option>
                            @endfor
                        </select>
                    </label>
                </div>
                @if($errors->any())
                    <div class="error-box" role="alert">
                        @foreach($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif
                <div class="availability">
                    <strong>Занято в этот день</strong>
                    <div class="slots">
                        @forelse($occupied as $booking)
                            <span>{{ $booking->starts_at->format('H:i') }}–{{ $booking->ends_at->format('H:i') }}</span>
                        @empty
                            <p>Пока всё свободно</p>
                        @endforelse
                    </div>
                </div>
                <div class="total">
                    <span>Итого</span>
                    <strong>{{ number_format($total, 2, ',', ' ') }} ₽</strong>
                </div>
                <button class="button" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="book">
                        @auth
                            Забронировать
                        @else
                            Войти и забронировать
                        @endauth
                        ↗
                    </span>
                    <span wire:loading wire:target="book">Подождите…</span>
                </button>
                <p class="form-hint">Сеансы по целым часам. Время указано в часовом поясе {{ config('app.timezone') }}. Доступность проверяется при подтверждении.</p>
            </form>
        </aside>
    </div>
</div>

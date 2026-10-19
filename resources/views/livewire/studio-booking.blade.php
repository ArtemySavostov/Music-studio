<div class="detail-page" wire:poll.30s x-data="{ formVisible: false, observer: null, init() { this.observer = new IntersectionObserver(entries => { this.formVisible = entries[0].isIntersecting }, { rootMargin: '0px 0px -100px 0px' }); this.observer.observe(this.$refs.panel) }, destroy() { this.observer?.disconnect() } }">
    <a class="back-link" href="{{ route('home') }}">← Все студии</a>
    <div class="detail-grid">
        <section>
            <div class="studio-art detail-art art-{{ $studio->id % 3 }} {{ $studio->has_piano ? 'has-piano' : 'no-piano' }}" aria-hidden="true">
                <div class="keys">@for($i=0;$i<9;$i++)<i></i>@endfor</div>
                <span class="art-word">{{ $studio->has_piano ? 'piano.' : 'sound.' }}</span>
            </div>
            <p class="eyebrow">ПРОСТРАНСТВО ДЛЯ МУЗЫКИ</p>
            <h1>{{ $studio->name }}</h1>
            <p class="detail-description">{{ $studio->description ?: 'Приходите репетировать, сочинять и искать своё звучание.' }}</p>
            <div class="detail-actions">@include('partials.favorite-button', ['studio' => $studio])</div>
            <div class="features"><span>◷ 10:00–22:00</span><span>♫ {{ $studio->has_piano ? 'Есть пианино' : 'Без пианино' }}</span><span>От 1 часа</span></div>
            <div class="detail-note"><h2>Время для вашего звучания</h2><p>Выберите дату и свободные часы. Итоговая стоимость появится сразу, а подтверждённая репетиция сохранится в вашем кабинете.</p></div>
        </section>
        <aside class="booking-panel glass glass--dense" id="booking-form" x-ref="panel" aria-label="Выбор времени репетиции">
            <p class="eyebrow">ВАША РЕПЕТИЦИЯ</p>
            <h2>{{ number_format((float) $studio->price_per_hour, 2, ',', ' ') }} ₽ <small>/ час</small></h2>
            <p class="muted">Свободные часы — перед вами</p>
            <form wire:submit="book" class="form-stack">
                <fieldset class="date-picker">
                    <legend>01 · Выберите дату</legend>
                    <div class="quick-dates" @focusin="$event.target.scrollIntoView({ block: 'nearest', inline: 'nearest', behavior: 'instant' })">
                        @for($offset=0;$offset<7;$offset++)
                            @php
                                $day = now()->addDays($offset);
                            @endphp
                            <button type="button" class="date-choice {{ $date === $day->format('Y-m-d') ? 'selected' : '' }}" wire:click="$set('date', '{{ $day->format('Y-m-d') }}')" aria-pressed="{{ $date === $day->format('Y-m-d') ? 'true' : 'false' }}" aria-label="{{ $day->locale('ru')->translatedFormat('j F, l') }}">
                                <span>{{ $offset === 0 ? 'Сегодня' : ($offset === 1 ? 'Завтра' : $day->locale('ru')->translatedFormat('D')) }}</span><strong>{{ $day->format('d') }}</strong>
                            </button>
                        @endfor
                    </div>
                    <label class="calendar-label">Или другая дата<input type="date" wire:model.live="date" min="{{ now()->format('Y-m-d') }}" required aria-describedby="booking-errors"></label>
                </fieldset>
                <label>02 · Длительность<select wire:model.live="duration">@for($length=1;$length<=12;$length++)<option value="{{ $length }}">{{ $length }} {{ $length === 1 ? 'час' : ($length <= 4 ? 'часа' : 'часов') }}</option>@endfor</select></label>
                <fieldset class="hour-picker">
                    <legend>03 · Начало репетиции</legend>
                    <p class="form-hint">Выберите начало — выделим все {{ $duration }} ч. вашей репетиции.</p>
                    <div class="hour-grid" wire:loading.class="is-loading" wire:target="date,duration,selectHour">
                        @foreach($hours as $hour => $status)
                            @php
                                $allowed = $availability->allows($hours, $hour, $hour + $duration);
                                $selected = $canBook && $hour >= $start && $hour < $end;
                                $label = $status === 'occupied' ? 'Занято' : ($status === 'past' ? 'Прошло' : ($status === 'unavailable' ? 'Недоступно' : ($selected ? 'Выбрано' : ($allowed ? 'Свободно' : 'Не хватает времени'))));
                            @endphp
                            <button type="button" class="hour-choice {{ $selected ? 'selected' : '' }} {{ $status }}" wire:click="selectHour({{ $hour }})" @disabled(! $allowed) aria-pressed="{{ $selected ? 'true' : 'false' }}" aria-label="{{ $hour }}:00–{{ $hour + 1 }}:00: {{ $label }}"><strong>{{ $hour }}:00</strong><span>{{ $label }}</span></button>
                        @endforeach
                    </div>
                </fieldset>
                <div id="booking-errors" aria-live="polite">
                    @if($errors->any())<div class="error-box" role="alert">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>
                    @elseif(! $canBook)<div class="error-box">Выбранный интервал недоступен. Выберите свободное начало или уменьшите длительность.</div>@endif
                </div>
                <div class="booking-summary" aria-live="polite">
                    <span class="eyebrow">ВАШ ВЫБОР</span>
                    @if($canBook)<strong>{{ \Illuminate\Support\Carbon::parse($date)->locale('ru')->translatedFormat('j F') }} · {{ $start }}:00–{{ $end }}:00</strong><span class="muted">{{ $end - $start }} ч. · {{ $studio->name }}</span>@else<strong>Выберите свободное время</strong>@endif
                    <div class="total"><span>Итого</span><strong>{{ $canBook ? number_format((float) $total, 2, ',', ' ').' ₽' : '—' }}</strong></div>
                </div>
                <button class="button" wire:loading.attr="disabled" @disabled(! $canBook)><span wire:loading.remove wire:target="book">@auth Забронировать @else Войти и забронировать @endauth ↗</span><span wire:loading wire:target="book">Подтверждаем…</span></button>
                <p class="form-hint">По целым часам · {{ config('app.timezone') }}. Расписание обновляется каждые 30 секунд. При подтверждении ещё раз проверим время и стоимость.</p>
            </form>
        </aside>
    </div>
    <div class="mobile-booking-bar glass glass--dense" x-show="!formVisible" x-cloak><div><span class="muted">{{ $canBook ? $start.':00–'.$end.':00' : 'Выберите часы' }}</span><strong>{{ $canBook ? number_format((float) $total, 2, ',', ' ').' ₽' : number_format((float) $studio->price_per_hour, 2, ',', ' ').' ₽ / ч.' }}</strong></div><a class="button small" href="#booking-form">К выбору времени ↓</a></div>
</div>

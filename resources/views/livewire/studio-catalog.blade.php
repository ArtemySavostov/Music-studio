<div x-data x-init="$wire.favoritesOnly ? $wire.restoreFavorites([...$store.favorites.ids]) : $wire.set('favoriteIds', [...$store.favorites.ids], false)" @studio-favorites.window="$wire.set('favoriteIds', [...$store.favorites.ids], $wire.favoritesOnly)">
    <section class="hero">
        <div class="hero-copy">
            <p class="eyebrow">ВАША МУЗЫКА НАЧИНАЕТСЯ ЗДЕСЬ</p>
            <h1>
                Найдите свой
                <br>
                <em>тон.</em>
            </h1>
            <p class="intro">
                Пространство для репетиций, новых идей
                <br class="desktop">
                и музыки, которую хочется играть.
            </p>
            <a class="button cream" href="#studios">
                Выбрать студию
                <span aria-hidden="true">↗</span>
            </a>
            <p class="hero-note">От одного часа · Простое бронирование</p>
        </div>
        <div class="sound-art" data-glass-reactive aria-hidden="true">
            <div class="record">
                <div class="record-label">
                    тон
                    <span>PLAY YOUR OWN WAY</span>
                </div>
            </div>
            <span class="art-caption glass">НАСТРОЙТЕСЬ НА СВОЁ</span>
            <span class="art-number">01 / ∞</span>
        </div>
    </section>
    @if($nextBooking)
        <a class="return-banner glass glass--dense" href="{{ route('bookings') }}">
            <div><p class="eyebrow">{{ $nextBooking->starts_at->isPast() ? 'ВАША РЕПЕТИЦИЯ ИДЁТ' : 'СКОРО ИГРАЕМ' }}</p><strong>{{ $nextBooking->studio?->name ?? 'Ваша репетиция' }}</strong><span class="muted">{{ $nextBooking->starts_at->locale('ru')->translatedFormat('j F, D') }} · {{ $nextBooking->starts_at->format('H:i') }}–{{ $nextBooking->ends_at->format('H:i') }}</span></div>
            <span class="return-action">К бронированиям ↗</span>
        </a>
    @endif
    <section id="studios" class="catalog">
        <div class="section-heading">
            <div>
                <p class="eyebrow">ПРОСТРАНСТВА</p>
                <h2>Студии для ваших идей</h2>
            </div>
            <span class="result-count" role="status" aria-live="polite">Найдено: {{ $studios->total() }}</span>
        </div>
        <div class="filter-panel glass glass--dense">
            <div class="filters">
                <label class="search">
                    <span class="sr-only">Поиск студии</span>
                    <input type="search" wire:model.live.debounce.300ms="search" placeholder="Найти студию по названию…" maxlength="100">
                </label>
                <label class="check">
                    <input type="checkbox" wire:model.live="piano">
                    С пианино
                </label>
                <label class="sort">
                    <span class="sr-only">Сортировка</span>
                    <select wire:model.live="sort">
                        <option value="name">По названию</option>
                        <option value="price">Сначала дешевле</option>
                        <option value="price_desc">Сначала дороже</option>
                    </select>
                </label>
            </div>
            <div class="filter-details">
                <label class="check favorites-filter"><input type="checkbox" wire:model.live="favoritesOnly">Избранные <span aria-hidden="true">♡</span></label>
                <fieldset class="price-filter">
                    <legend>Стоимость за час, ₽</legend>
                    <label><span class="sr-only">Цена от</span><input type="number" min="0" max="99999999.99" step="0.01" wire:model.live.debounce.400ms="minPrice" placeholder="От" aria-describedby="price-errors"></label>
                    <span aria-hidden="true">—</span>
                    <label><span class="sr-only">Цена до</span><input type="number" min="0" max="99999999.99" step="0.01" wire:model.live.debounce.400ms="maxPrice" placeholder="До" aria-describedby="price-errors"></label>
                </fieldset>
                @if($search !== '' || $piano || $minPrice !== '' || $maxPrice !== '' || $sort !== 'name' || $favoritesOnly)
                    <div class="active-filters">
                        @if($search !== '')<span class="filter-chip">Поиск: {{ $search }}</span>@endif
                        @if($piano)<span class="filter-chip">С пианино</span>@endif
                        @if($favoritesOnly)<span class="filter-chip">Избранные</span>@endif
                        @if($minPrice !== '' || $maxPrice !== '')<span class="filter-chip">{{ $minPrice !== '' ? 'От '.$minPrice : '' }} {{ $maxPrice !== '' ? 'до '.$maxPrice : '' }} ₽</span>@endif
                        @if(in_array($sort, ['price', 'price_desc']))<span class="filter-chip">{{ $sort === 'price' ? 'Сначала дешевле' : 'Сначала дороже' }}</span>@endif
                        <button type="button" class="text-button" wire:click="clearFilters">Сбросить фильтры ×</button>
                    </div>
                @endif
            </div>
            <p class="favorites-note" x-show="$wire.favoritesOnly">Избранное хранится в этом браузере и доступно без входа.</p>
            <p class="error" role="status" x-cloak x-show="$store.favorites.message" x-text="$store.favorites.message"></p>
            <div id="price-errors" class="field-errors" role="status">
                @error('minPrice')<p class="error">{{ $message }}</p>@enderror
                @error('maxPrice')<p class="error">{{ $message }}</p>@enderror
            </div>
        </div>
        <div class="loading-status" role="status"><span wire:loading.delay wire:target="search,piano,sort,minPrice,maxPrice,clearFilters,nextPage,previousPage">Обновляем подборку…</span></div>
        <div class="studio-grid" wire:loading.class="is-loading" wire:target="search,piano,sort,minPrice,maxPrice,clearFilters,nextPage,previousPage">
            @forelse($studios as $studio)
                <article class="studio-card" wire:key="studio-{{ $studio->id }}">
                    @include('partials.favorite-button', ['studio' => $studio, 'compact' => true])
                    <a href="{{ route('studios.show', $studio) }}" class="studio-art art-{{ $studio->id % 3 }} {{ $studio->has_piano ? 'has-piano' : 'no-piano' }}" data-glass-reactive tabindex="-1" aria-hidden="true">
                        <span class="art-tag glass">{{ $studio->has_piano ? 'С ПИАНИНО' : 'ВАШЕ ПРОСТРАНСТВО' }}</span>
                        <div class="keys">
                            @for($i=0;$i<9;$i++)
                                <i></i>
                            @endfor
                        </div>
                        <span class="art-word">{{ $studio->has_piano ? 'piano.' : 'sound.' }}</span>
                    </a>
                    <div class="card-content">
                        <h3>
                            <a href="{{ route('studios.show', $studio) }}">{{ $studio->name }}</a>
                        </h3>
                        <p class="description">{{ $studio->description ?: 'Место для вашей следующей репетиции.' }}</p>
                        <p class="card-feature">{{ $studio->has_piano ? '♫ Пианино в студии' : '♫ Без пианино' }} · от 1 часа</p>
                        <div class="card-bottom">
                            <div>
                                <strong>{{ number_format((float) $studio->price_per_hour, 2, ',', ' ') }} ₽</strong>
                                <span class="muted">/ час</span>
                            </div>
                            <a class="button small card-action" href="{{ route('studios.show', $studio) }}" aria-label="Выбрать время в студии {{ $studio->name }}">Выбрать время <span aria-hidden="true">↗</span></a>
                        </div>
                    </div>
                </article>
            @empty
                <div class="empty">
                    <span class="empty-symbol">♫</span>
                    <h3>{{ $favoritesOnly ? 'В избранном ничего не найдено' : 'Студии не найдены' }}</h3>
                    <p>{{ $favoritesOnly ? 'Сохраните понравившиеся студии кнопкой с сердцем. Если они уже сохранены, попробуйте сбросить остальные условия.' : 'Попробуйте изменить поиск или отключить фильтр. Если каталог пуст, студии появятся здесь после добавления.' }}</p>
                    @if($search !== '' || $piano || $minPrice !== '' || $maxPrice !== '' || $favoritesOnly)
                        <button type="button" class="button secondary" wire:click="clearFilters">Сбросить фильтры</button>
                    @endif
                </div>
            @endforelse
        </div>
        @include('partials.pagination', ['paginator' => $studios])
    </section>
    <section class="steps">
        <div>
            <span>01</span>
            <h3>Выберите пространство</h3>
            <p>Найдите студию под своё звучание.</p>
        </div>
        <div>
            <span>02</span>
            <h3>Настройте время</h3>
            <p>Выберите дату и часы репетиции.</p>
        </div>
        <div>
            <span>03</span>
            <h3>Приходите играть</h3>
            <p>Бронь сохранится в вашем кабинете.</p>
        </div>
    </section>
</div>

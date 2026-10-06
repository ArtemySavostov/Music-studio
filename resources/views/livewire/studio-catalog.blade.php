<div>
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
        <div class="sound-art" aria-hidden="true">
            <div class="record">
                <div class="record-label">
                    тон
                    <span>PLAY YOUR OWN WAY</span>
                </div>
            </div>
            <span class="art-caption">НАСТРОЙТЕСЬ НА СВОЁ</span>
            <span class="art-number">01 / ∞</span>
        </div>
    </section>
    <section id="studios" class="catalog">
        <div class="section-heading">
            <div>
                <p class="eyebrow">ПРОСТРАНСТВА</p>
                <h2>Студии для ваших идей</h2>
            </div>
            <span class="muted">{{ $studios->total() }} в каталоге</span>
        </div>
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
                </select>
            </label>
        </div>
        <div class="studio-grid" wire:loading.class="is-loading" wire:target="search,piano,sort">
            @forelse($studios as $studio)
                <article class="studio-card" wire:key="studio-{{ $studio->id }}">
                    <a href="{{ route('studios.show', $studio) }}" class="studio-art art-{{ $studio->id % 3 }}" tabindex="-1" aria-hidden="true">
                        <span class="art-tag">{{ $studio->has_piano ? 'С ПИАНИНО' : 'ВАШЕ ПРОСТРАНСТВО' }}</span>
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
                        <div class="card-bottom">
                            <div>
                                <strong>{{ number_format((float) $studio->price_per_hour, 2, ',', ' ') }} ₽</strong>
                                <span class="muted">/ час</span>
                            </div>
                            <a class="circle-link" href="{{ route('studios.show', $studio) }}" aria-label="Выбрать {{ $studio->name }}">↗</a>
                        </div>
                    </div>
                </article>
            @empty
                <div class="empty">
                    <span class="empty-symbol">♫</span>
                    <h3>Студии не найдены</h3>
                    <p>Попробуйте изменить поиск или отключить фильтр. Если каталог пуст, студии появятся здесь после добавления.</p>
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

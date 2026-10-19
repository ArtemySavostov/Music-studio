<!DOCTYPE html>
<html lang="ru">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="theme-color" content="#183d36">
        <title>{{ $title ?? 'Студии' }} — Тон</title>
        <script src="{{ asset('js/appearance.js') }}?v={{ filemtime(public_path('js/appearance.js')) }}"></script>
        <link rel="stylesheet" href="{{ asset('css/studio.css') }}?v={{ filemtime(public_path('css/studio.css')) }}">
        @livewireStyles
    </head>
    <body x-data="{ connectionFailed: false }" @studio-connection.window="connectionFailed = $event.detail.failed">
        <a class="skip-link" href="#main">Перейти к содержимому</a>
        <header class="header glass">
            <a class="brand" href="{{ route('home') }}" aria-label="Тон — главная">
                <span class="brand-mark">т.</span>
                тон
                <span class="brand-caption">пространство для музыки</span>
            </a>
            <nav aria-label="Основная навигация">
                <a href="{{ route('home') }}" @if(request()->routeIs('home')) aria-current="page" @endif>Студии</a>
                @auth
                    <a href="{{ route('bookings') }}" @if(request()->routeIs('bookings')) aria-current="page" @endif>Мои бронирования</a>
                @endauth
            </nav>
            <div class="header-account">
                <details class="theme-menu" x-data @keydown.escape.prevent.stop="$el.open = false" @click.outside="$el.open = false">
                    <summary aria-label="Оформление" title="Оформление"><span aria-hidden="true" x-text="$store.appearance.resolved === 'dark' ? '☾' : '☀'">☀</span></summary>
                    <div class="theme-options glass glass--dense" role="group" aria-label="Тема интерфейса">
                        <p class="eyebrow">ОФОРМЛЕНИЕ</p>
                        @foreach(['light' => 'Светлая', 'dark' => 'Тёмная', 'system' => 'Как в системе'] as $mode => $label)
                            <button type="button" aria-label="{{ $label }}" :aria-pressed="$store.appearance.preference === '{{ $mode }}'" @click="$store.appearance.set('{{ $mode }}'); $el.closest('details').open = false">{{ $label }}<span aria-hidden="true" x-show="$store.appearance.preference === '{{ $mode }}'">✓</span></button>
                        @endforeach
                        <p class="form-hint" x-cloak x-show="!$store.appearance.persistent">Браузер не разрешил сохранение. Тема изменена только для этой страницы.</p>
                    </div>
                </details>
                @auth
                    <span class="user-name">{{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="button small secondary">Выйти</button>
                    </form>
                @else
                    <a class="button small secondary" href="{{ route('login') }}">
                        Войти
                        <span aria-hidden="true">↗</span>
                    </a>
                @endauth
            </div>
        </header>
        <main id="main" class="container">
            <div class="connection-notice" x-show="connectionFailed" x-cloak role="alert">
                <span>Не удалось связаться с сервером. Проверьте соединение и повторите действие. Перед повторной отправкой брони проверьте «Мои бронирования».</span>
                <button type="button" class="text-button" @click="connectionFailed = false" aria-label="Закрыть сообщение о соединении">Закрыть ×</button>
            </div>
            @if(session('success'))
                <div class="notice" role="status">{{ session('success') }}</div>
            @endif
            {{ $slot }}
        </main>
        <footer class="footer">
            <a class="brand" href="{{ route('home') }}">тон.</a>
            <span>Место, где звучат ваши идеи.</span>
            <span>Ежедневно · 10:00–22:00</span>
        </footer>
        <script src="{{ asset('js/studio.js') }}?v={{ filemtime(public_path('js/studio.js')) }}"></script>
        @livewireScripts
    </body>
</html>

<!DOCTYPE html>
<html lang="ru">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="theme-color" content="#183d36">
        <title>{{ $title ?? 'Студии' }} — Тон</title>
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

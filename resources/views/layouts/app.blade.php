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
    <body>
        <a class="skip-link" href="#main">Перейти к содержимому</a>
        <header class="header">
            <a class="brand" href="{{ route('home') }}" aria-label="Тон — главная">
                <span class="brand-mark">т.</span>
                тон
                <span class="brand-caption">пространство для музыки</span>
            </a>
            <nav aria-label="Основная навигация">
                <a href="{{ route('home') }}" @if(request()->routeIs('home')) aria-current="page" @endif>Студии</a>
                @auth
                    <a href="{{ route('bookings') }}" @if(request()->routeIs('bookings')) aria-current="page" @endif>Мои бронирования</a>
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
            </nav>
        </header>
        <main id="main" class="container">
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
        @livewireScripts
    </body>
</html>

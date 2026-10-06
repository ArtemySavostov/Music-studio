<div class="auth-layout">
    <section class="auth-story">
        <p class="eyebrow">БОЛЬШЕ ВРЕМЕНИ ДЛЯ МУЗЫКИ</p>
        <h1>
            Ваш следующий
            <br>
            сеанс —
            <br>
            <em>в пару кликов.</em>
        </h1>
        <p>Выбирайте студии, планируйте репетиции и храните все бронирования в одном месте.</p>
        <span class="auth-note" aria-hidden="true">♫</span>
    </section>
    <section class="auth-card">
        <p class="eyebrow">ЛИЧНЫЙ КАБИНЕТ</p>
        <h2>{{ $register ? 'Давайте знакомиться' : 'С возвращением' }}</h2>
        <p class="muted">{{ $register ? 'Создайте аккаунт для бронирования студий.' : 'Войдите, чтобы продолжить.' }}</p>
        <form wire:submit="submit" class="form-stack">
            @if($register)
                <label>
                    Ваше имя
                    <input wire:model="name" autocomplete="name" required maxlength="255">
                    @error('name')
                        <span class="error">{{ $message }}</span>
                    @enderror
                </label>
            @endif
            <label>
                Email
                <input type="email" wire:model="email" autocomplete="email" required maxlength="255">
                @error('email')
                    <span class="error" role="alert">{{ $message }}</span>
                @enderror
            </label>
            <label>
                Пароль
                <input type="password" wire:model="password" autocomplete="{{ $register ? 'new-password' : 'current-password' }}" required @if($register) minlength="8" @endif>
                @error('password')
                    <span class="error" role="alert">{{ $message }}</span>
                @enderror
            </label>
            @if($register)
                <label>
                    Повторите пароль
                    <input type="password" wire:model="password_confirmation" autocomplete="new-password" required minlength="8">
                </label>
            @endif
            <button class="button" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="submit">{{ $register ? 'Создать аккаунт' : 'Войти' }} ↗</span>
                <span wire:loading wire:target="submit">Подождите…</span>
            </button>
        </form>
        <p class="auth-switch">
            {{ $register ? 'Уже есть аккаунт?' : 'Впервые здесь?' }}
            <a href="{{ route($register ? 'login' : 'register') }}">{{ $register ? 'Войти' : 'Зарегистрироваться' }}</a>
        </p>
    </section>
</div>

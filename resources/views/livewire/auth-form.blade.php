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
        @if($draftStudio)
            <div class="auth-draft"><p class="eyebrow">ПРОДОЛЖИМ ПОСЛЕ ВХОДА</p><strong>{{ $draftStudio->name }}</strong><p>{{ \Illuminate\Support\Carbon::parse($draft['date'])->locale('ru')->translatedFormat('j F') }} · {{ $draft['start'] }}:00–{{ $draft['end'] }}:00</p><span class="muted">Выбор сохранён. Бронь подтвердите после входа.</span></div>
        @endif
        <form wire:submit="submit" class="form-stack">
            @if($register)
                <label>
                    <span id="name-label">Ваше имя</span>
                    <input wire:model="name" autocomplete="name" required maxlength="255" aria-labelledby="name-label" aria-invalid="{{ $errors->has('name') ? 'true' : 'false' }}" aria-describedby="name-error">
                    @error('name')
                        <span class="error" id="name-error" role="alert">{{ $message }}</span>
                    @enderror
                </label>
            @endif
            <label>
                <span id="email-label">Email</span>
                <input type="email" wire:model="email" autocomplete="email" required maxlength="255" aria-labelledby="email-label" aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}" aria-describedby="email-error">
                @error('email')
                    <span class="error" id="email-error" role="alert">{{ $message }}</span>
                @enderror
            </label>
            <label x-data="{ visible: false }">
                <span id="password-label">Пароль</span>
                <span class="password-field"><input type="password" :type="visible ? 'text' : 'password'" wire:model="password" autocomplete="{{ $register ? 'new-password' : 'current-password' }}" required @if($register) minlength="8" @endif aria-labelledby="password-label" aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}" aria-describedby="password-error"><button type="button" @click="visible = !visible" :aria-pressed="visible" :aria-label="visible ? 'Скрыть пароль' : 'Показать пароль'" x-text="visible ? 'Скрыть' : 'Показать'">Показать</button></span>
                @error('password')
                    <span class="error" id="password-error" role="alert">{{ $message }}</span>
                @enderror
            </label>
            @if($register)
                <label x-data="{ visible: false }">
                    <span id="confirmation-label">Повторите пароль</span>
                    <span class="password-field"><input type="password" :type="visible ? 'text' : 'password'" wire:model="password_confirmation" autocomplete="new-password" required minlength="8" aria-labelledby="confirmation-label"><button type="button" @click="visible = !visible" :aria-pressed="visible" aria-label="Показать или скрыть повтор пароля" x-text="visible ? 'Скрыть' : 'Показать'">Показать</button></span>
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

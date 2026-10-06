<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Component;

class AuthForm extends Component
{
    #[Locked]
    public bool $register = false;

    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function mount(bool $register = false): void
    {
        $this->register = $register;
    }

    public function submit(): void
    {
        $key = 'web-auth:'.request()->ip().':'.mb_strtolower($this->email);
        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages(['email' => 'Слишком много попыток. Попробуйте через минуту.']);
        }
        RateLimiter::hit($key, 60);
        $rules = ['email' => ['required', 'email', 'max:255'], 'password' => ['required', 'string']];
        if ($this->register) {
            $rules['name'] = ['required', 'string', 'max:255'];
            $rules['email'][] = 'unique:users,email';
            $rules['password'] = ['required', 'string', 'min:8', 'confirmed'];
        }
        $data = $this->validate($rules, [
            'required' => 'Заполните это поле.', 'email' => 'Введите корректный email.',
            'unique' => 'Этот email уже зарегистрирован.', 'min' => 'Нужно не менее :min символов.',
            'confirmed' => 'Пароли не совпадают.', 'max' => 'Не более :max символов.',
        ]);
        if ($this->register) {
            Auth::login(User::create($data));
        } elseif (! Auth::attempt(['email' => $this->email, 'password' => $this->password])) {
            $this->reset('password', 'password_confirmation');
            throw ValidationException::withMessages(['email' => 'Неверный email или пароль.']);
        }
        RateLimiter::clear($key);
        session()->regenerate();
        $this->redirectIntended(route('home'));
    }

    public function render()
    {
        return view('livewire.auth-form')->layout('layouts.app', ['title' => $this->register ? 'Регистрация' : 'Вход']);
    }
}

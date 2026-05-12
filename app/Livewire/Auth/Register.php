<?php

namespace App\Livewire\Auth;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Livewire\Component;

class Register extends Component
{
    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $passwordConfirmation = '';

    public function mount(): void
    {
        if (Auth::check()) {
            $this->redirectRoute('dashboard', navigate: true);
        }
    }

    public function register(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'same:passwordConfirmation', Password::min(8)],
            'passwordConfirmation' => ['required'],
        ], [
            'name.required' => 'Informe seu nome completo.',
            'email.required' => 'Informe seu e-mail.',
            'email.email' => 'Informe um e-mail válido.',
            'email.unique' => 'Este e-mail já está em uso.',
            'password.required' => 'Crie uma senha.',
            'password.same' => 'As senhas precisam ser iguais.',
            'password.min' => 'A senha precisa ter pelo menos 8 caracteres.',
            'passwordConfirmation.required' => 'Confirme sua senha.',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => UserRole::Setor,
            'sector_id' => null,
        ]);

        Auth::login($user);

        session()->regenerate();

        $this->redirectRoute('dashboard', navigate: true);
    }

    public function render()
    {
        return view('livewire.auth.register')
            ->layout('layouts.guest')
            ->title('Criar conta | SEDUC BI');
    }
}

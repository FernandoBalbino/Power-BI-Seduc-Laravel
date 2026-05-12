<?php

namespace App\Livewire\Auth;

use App\Enums\UserRole;
use App\Models\User;
use App\Services\SectorRegistrationCodeService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class Register extends Component
{
    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $passwordConfirmation = '';

    public string $registrationCode = '';

    public function mount(): void
    {
        if (Auth::check()) {
            $this->redirectRoute('dashboards.index', navigate: true);
        }
    }

    public function register(): void
    {
        $this->registrationCode = app(SectorRegistrationCodeService::class)->normalize($this->registrationCode);

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'same:passwordConfirmation', Password::min(8)],
            'passwordConfirmation' => ['required'],
            'registrationCode' => ['required', 'string', 'max:60'],
        ], [
            'name.required' => 'Informe seu nome completo.',
            'email.required' => 'Informe seu e-mail.',
            'email.email' => 'Informe um e-mail válido.',
            'email.unique' => 'Este e-mail já está em uso.',
            'password.required' => 'Crie uma senha.',
            'password.same' => 'As senhas precisam ser iguais.',
            'password.min' => 'A senha precisa ter pelo menos 8 caracteres.',
            'passwordConfirmation.required' => 'Confirme sua senha.',
            'registrationCode.required' => 'Informe o código do setor.',
            'registrationCode.max' => 'O código do setor deve ser mais curto.',
        ]);

        $sector = app(SectorRegistrationCodeService::class)->findActiveByCode($validated['registrationCode']);

        if (! $sector) {
            throw ValidationException::withMessages([
                'registrationCode' => 'Código inválido ou setor inativo. Confira o código recebido e tente novamente.',
            ]);
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => UserRole::Setor,
            'sector_id' => $sector->id,
        ]);

        Auth::login($user);

        session()->regenerate();

        $this->redirectRoute('dashboards.index', navigate: true);
    }

    public function render()
    {
        return view('livewire.auth.register')
            ->layout('layouts.guest')
            ->title('Criar conta | SEDUC BI');
    }
}

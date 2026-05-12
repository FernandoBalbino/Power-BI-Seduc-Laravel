<div class="mx-auto w-full max-w-[380px]">
    <div class="mb-5 text-center">
        <h1 class="text-[30px] font-bold leading-10 text-slate-950">Crie sua conta</h1>
        <p class="mt-2 text-sm leading-6 text-slate-400">Use o código recebido para acessar os dashboards do seu setor.</p>
    </div>

    <form wire:submit="register" class="space-y-3">
        <x-input
            label="Nome completo"
            name="name"
            placeholder="Seu nome"
            wire:model="name"
            :error="$errors->first('name')"
            autofocus
        />

        <x-input
            label="E-mail"
            name="email"
            type="email"
            placeholder="seu@email.com.br"
            wire:model="email"
            :error="$errors->first('email')"
        />

        <x-input
            label="Código do setor"
            name="registration_code"
            placeholder="Ex.: SEDUC-ABC12345"
            wire:model="registrationCode"
            :error="$errors->first('registrationCode')"
        />

        <x-input
            label="Senha"
            name="password"
            type="password"
            placeholder="Crie uma senha"
            wire:model="password"
            :error="$errors->first('password')"
        />

        <x-input
            label="Confirmar senha"
            name="passwordConfirmation"
            type="password"
            placeholder="Repita sua senha"
            wire:model="passwordConfirmation"
            :error="$errors->first('passwordConfirmation')"
        />

        <x-button type="submit" class="w-full bg-[#302EF4] hover:bg-[#2725d8]">
            Criar conta
        </x-button>
    </form>

    <div class="mt-5 text-center text-sm text-slate-500">
        Já tem conta?
        <a href="{{ route('login') }}" wire:navigate class="font-semibold text-seduc-primary hover:text-seduc-primary-hover">Entrar</a>
    </div>
</div>

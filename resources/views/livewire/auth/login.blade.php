<div class="mx-auto w-full max-w-[380px]">
    <div class="mb-9 text-center">
        <h1 class="text-[30px] font-bold leading-10 text-slate-950">Bem-vindo de volta</h1>
        <p class="mt-3 text-sm leading-6 text-slate-400">Informe seu e-mail e senha para acessar sua conta.</p>
    </div>

    <form wire:submit="login" class="space-y-5">
        <x-input
            label="E-mail"
            name="email"
            type="email"
            placeholder="seu@email.com.br"
            wire:model="email"
            :error="$errors->first('email')"
            autofocus
        />

        <x-input
            label="Senha"
            name="password"
            type="password"
            placeholder="Digite sua senha"
            wire:model="password"
            :error="$errors->first('password')"
        />

        <div class="flex items-center justify-between">
            <label class="inline-flex items-center gap-2 text-sm font-medium text-slate-600">
                <input type="checkbox" wire:model="remember" class="h-[18px] w-[18px] rounded border-slate-300 text-seduc-primary focus:ring-blue-100">
                Lembrar de mim
            </label>

            <span class="text-sm font-semibold text-seduc-primary">Esqueci minha senha</span>
        </div>

        <x-button type="submit" class="w-full bg-[#302EF4] hover:bg-[#2725d8]">
            Entrar
        </x-button>
    </form>

    <div class="mt-8 text-center text-sm text-slate-500">
        Ainda não tem conta?
        <a href="{{ route('register') }}" wire:navigate class="font-semibold text-seduc-primary hover:text-seduc-primary-hover">Cadastre-se</a>
    </div>
</div>

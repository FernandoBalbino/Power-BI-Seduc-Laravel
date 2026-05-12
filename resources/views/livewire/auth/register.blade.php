<div class="mx-auto max-w-md">
    <div class="mb-8 text-center">
        <x-logo :with-text="false" size="md" class="mb-5 justify-center" />
        <h1 class="text-[32px] font-bold leading-10 text-slate-950">Crie sua conta</h1>
        <p class="mt-3 text-sm leading-6 text-slate-500">Cadastre-se para acessar os dashboards do seu setor.</p>
    </div>

    <form wire:submit="register" class="space-y-[18px]">
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

        <div class="rounded-xl border border-blue-100 bg-seduc-primary-soft p-4 text-sm leading-6 text-slate-600">
            O vínculo por código do setor será ativado na próxima etapa. Por enquanto, sua conta fica preparada como usuário de setor.
        </div>

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

        <x-button type="submit" class="w-full">
            Criar conta
        </x-button>
    </form>

    <div class="mt-8 border-t border-slate-200 pt-7 text-center text-sm text-slate-600">
        Já tem conta?
        <a href="{{ route('login') }}" wire:navigate class="font-semibold text-seduc-primary hover:text-seduc-primary-hover">Entrar</a>
    </div>
</div>

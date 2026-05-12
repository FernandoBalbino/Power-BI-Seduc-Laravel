@props(['title' => 'SEDUC BI'])

@php
    $user = auth()->user();
@endphp

<header class="mb-5 flex h-16 items-center justify-between">
    <div>
        <p class="text-sm font-medium text-slate-500">Painel principal</p>
        <h1 class="text-[28px] font-bold leading-9 text-slate-950">{{ str_replace(' | SEDUC BI', '', $title) }}</h1>
    </div>

    <div class="flex items-center gap-3">
        <x-badge variant="{{ $user?->isAdmin() ? 'info' : 'neutral' }}">
            {{ $user?->role?->label() ?? 'Setor' }}
        </x-badge>

        <a href="{{ route('profile') }}" wire:navigate class="flex h-11 items-center gap-2 rounded-[10px] border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
            <x-icon name="user" class="h-5 w-5 text-slate-500" />
            Perfil
        </a>
    </div>
</header>

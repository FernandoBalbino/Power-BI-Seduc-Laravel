<x-app-layout title="Perfil | SEDUC BI">
    <x-card>
        <x-badge variant="info">Perfil</x-badge>
        <h2 class="mt-4 text-2xl font-bold text-slate-950">{{ auth()->user()->name }}</h2>
        <p class="mt-2 text-sm text-slate-600">{{ auth()->user()->email }}</p>

        <div class="mt-6 grid grid-cols-1 gap-4 md:grid-cols-2">
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <p class="text-sm font-semibold text-slate-600">Tipo de usuário</p>
                <p class="mt-1 text-lg font-bold text-slate-950">{{ auth()->user()->role->label() }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <p class="text-sm font-semibold text-slate-600">Setor</p>
                <p class="mt-1 text-lg font-bold text-slate-950">{{ auth()->user()->sector_id ? 'Vinculado' : 'Pendente' }}</p>
            </div>
        </div>
    </x-card>
</x-app-layout>

<div class="space-y-5">
    @if (session('status'))
        <div class="rounded-2xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-semibold text-green-800">
            {{ session('status') }}
        </div>
    @endif

    <x-card>
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <x-badge variant="info">Área administrativa</x-badge>
                <h2 class="mt-4 text-2xl font-bold text-slate-950">Setores</h2>
                <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600">
                    Cadastre setores, gere códigos de acesso e acompanhe os usuários vinculados.
                </p>
            </div>

            <a
                href="{{ route('admin.sectors.create') }}"
                wire:navigate
                class="inline-flex h-11 items-center justify-center gap-2 rounded-[10px] bg-seduc-primary px-[18px] text-sm font-semibold text-white shadow-seduc-button transition hover:bg-seduc-primary-hover"
            >
                <x-icon name="plus-circle" class="h-5 w-5" />
                Criar Setor
            </a>
        </div>
    </x-card>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
        <x-card>
            <p class="text-sm font-semibold text-slate-600">Total de setores</p>
            <p class="mt-2 text-2xl font-bold text-slate-950">{{ $summary['total'] }}</p>
        </x-card>

        <x-card>
            <p class="text-sm font-semibold text-slate-600">Setores ativos</p>
            <p class="mt-2 text-2xl font-bold text-green-700">{{ $summary['active'] }}</p>
        </x-card>

        <x-card>
            <p class="text-sm font-semibold text-slate-600">Setores inativos</p>
            <p class="mt-2 text-2xl font-bold text-amber-700">{{ $summary['inactive'] }}</p>
        </x-card>

        <x-card>
            <p class="text-sm font-semibold text-slate-600">Usuários vinculados</p>
            <p class="mt-2 text-2xl font-bold text-seduc-primary">{{ $summary['users'] }}</p>
        </x-card>
    </div>

    <x-card padding="p-0">
        <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
            <div>
                <h3 class="text-lg font-bold text-slate-950">Lista de setores</h3>
                <p class="mt-1 text-sm text-slate-500">Use o código para liberar o cadastro dos usuários de cada setor.</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[980px] text-left">
                <thead class="h-11 bg-slate-50 text-xs font-bold text-slate-700">
                    <tr>
                        <th class="px-4 py-3">Setor</th>
                        <th class="px-4 py-3">Código de cadastro</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Usuários</th>
                        <th class="px-4 py-3 text-right">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm text-slate-600">
                    @forelse ($sectors as $sector)
                        <tr wire:key="sector-{{ $sector->id }}" class="h-14 hover:bg-slate-50">
                            <td class="px-4 py-3">
                                <p class="font-semibold text-slate-950">{{ $sector->name }}</p>
                                <p class="mt-0.5 max-w-xs truncate text-xs text-slate-500">{{ $sector->description ?: 'Sem descrição' }}</p>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <code class="rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-700">{{ $sector->registration_code }}</code>

                                    <button
                                        type="button"
                                        x-data="{ copied: false }"
                                        @click="navigator.clipboard.writeText(@js($sector->registration_code)); copied = true; setTimeout(() => copied = false, 1400)"
                                        class="inline-flex h-8 items-center gap-1 rounded-lg px-2 text-xs font-semibold text-seduc-primary transition hover:bg-blue-50"
                                    >
                                        <x-icon name="copy" class="h-4 w-4" />
                                        <span x-text="copied ? 'Copiado' : 'Copiar'">Copiar</span>
                                    </button>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <x-badge :variant="$sector->is_active ? 'success' : 'warning'">
                                    {{ $sector->is_active ? 'Ativo' : 'Inativo' }}
                                </x-badge>
                            </td>
                            <td class="px-4 py-3">{{ $sector->users_count }}</td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap items-center justify-end gap-2">
                                    <a
                                        href="{{ route('admin.sectors.users', $sector) }}"
                                        wire:navigate
                                        class="inline-flex h-9 items-center gap-2 rounded-[10px] border border-slate-200 bg-white px-3 text-xs font-semibold text-slate-700 transition hover:bg-slate-50"
                                    >
                                        <x-icon name="eye" class="h-4 w-4" />
                                        Ver Usuários
                                    </a>

                                    <a
                                        href="{{ route('admin.sectors.edit', $sector) }}"
                                        wire:navigate
                                        class="inline-flex h-9 items-center gap-2 rounded-[10px] border border-slate-200 bg-white px-3 text-xs font-semibold text-slate-700 transition hover:bg-slate-50"
                                    >
                                        <x-icon name="pencil" class="h-4 w-4" />
                                        Editar
                                    </a>

                                    <button
                                        type="button"
                                        wire:click="regenerateCode({{ $sector->id }})"
                                        wire:loading.attr="disabled"
                                        class="inline-flex h-9 items-center gap-2 rounded-[10px] border border-blue-100 bg-blue-50 px-3 text-xs font-semibold text-seduc-primary transition hover:bg-blue-100 disabled:opacity-60"
                                    >
                                        <x-icon name="refresh-cw" class="h-4 w-4" />
                                        Regenerar Código
                                    </button>

                                    <button
                                        type="button"
                                        wire:click="toggleActive({{ $sector->id }})"
                                        wire:loading.attr="disabled"
                                        class="inline-flex h-9 items-center gap-2 rounded-[10px] px-3 text-xs font-semibold transition disabled:opacity-60 {{ $sector->is_active ? 'bg-amber-100 text-amber-800 hover:bg-amber-200' : 'bg-green-100 text-green-800 hover:bg-green-200' }}"
                                    >
                                        <x-icon name="power" class="h-4 w-4" />
                                        {{ $sector->is_active ? 'Desativar' : 'Ativar' }}
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-10 text-center text-sm text-slate-500">
                                Nenhum setor cadastrado ainda.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>
</div>

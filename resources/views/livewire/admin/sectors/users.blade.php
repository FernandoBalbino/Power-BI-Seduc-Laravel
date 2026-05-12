<div class="space-y-5">
    <x-card>
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <a href="{{ route('admin.sectors.index') }}" wire:navigate class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 transition hover:text-seduc-primary">
                    <x-icon name="arrow-left" class="h-4 w-4" />
                    Voltar para setores
                </a>
                <h2 class="mt-3 text-2xl font-bold text-slate-950">{{ $sector->name }}</h2>
                <p class="mt-1 text-sm text-slate-500">Usuários cadastrados com o código deste setor.</p>
            </div>

            <x-badge :variant="$sector->is_active ? 'success' : 'warning'">
                {{ $sector->is_active ? 'Setor ativo' : 'Setor inativo' }}
            </x-badge>
        </div>
    </x-card>

    <x-card padding="p-0">
        <div class="border-b border-slate-200 px-5 py-4">
            <h3 class="text-lg font-bold text-slate-950">Usuários vinculados</h3>
            <p class="mt-1 text-sm text-slate-500">Total: {{ $users->count() }}</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[720px] text-left">
                <thead class="h-11 bg-slate-50 text-xs font-bold text-slate-700">
                    <tr>
                        <th class="px-4 py-3">Nome</th>
                        <th class="px-4 py-3">E-mail</th>
                        <th class="px-4 py-3">Perfil</th>
                        <th class="px-4 py-3">Cadastro</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm text-slate-600">
                    @forelse ($users as $user)
                        <tr class="h-12 hover:bg-slate-50">
                            <td class="px-4 py-3 font-semibold text-slate-950">{{ $user->name }}</td>
                            <td class="px-4 py-3">{{ $user->email }}</td>
                            <td class="px-4 py-3"><x-badge>{{ $user->role->label() }}</x-badge></td>
                            <td class="px-4 py-3">{{ $user->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-10 text-center text-sm text-slate-500">
                                Nenhum usuário vinculado a este setor ainda.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>
</div>

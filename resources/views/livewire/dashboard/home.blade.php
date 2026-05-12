<div class="space-y-5">
    <x-card>
        <div class="flex items-start justify-between gap-6">
            <div>
                <x-badge variant="info">Base inicial pronta</x-badge>
                <h2 class="mt-4 text-2xl font-bold text-slate-950">Bem-vindo ao SEDUC BI</h2>
                <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600">
                    Esta é a estrutura inicial do sistema. Nas próximas etapas, o painel receberá setores, importações de planilhas e dashboards dinâmicos.
                </p>
            </div>

            <div class="hidden h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-seduc-primary-soft text-seduc-primary md:flex">
                <x-icon name="layout-dashboard" class="h-7 w-7" />
            </div>
        </div>
    </x-card>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
        <x-card>
            <div class="flex items-center gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-[14px] bg-blue-100 text-seduc-primary">
                    <x-icon name="shield-check" class="h-6 w-6" />
                </div>
                <div>
                    <p class="text-sm font-semibold text-slate-600">Autenticação</p>
                    <p class="mt-1 text-xl font-bold text-slate-950">Ativa</p>
                </div>
            </div>
        </x-card>

        <x-card>
            <div class="flex items-center gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-[14px] bg-slate-100 text-slate-600">
                    <x-icon name="users" class="h-6 w-6" />
                </div>
                <div>
                    <p class="text-sm font-semibold text-slate-600">Perfil do usuário</p>
                    <p class="mt-1 text-xl font-bold text-slate-950">{{ auth()->user()->role->label() }}</p>
                </div>
            </div>
        </x-card>

        <x-card>
            <div class="flex items-center gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-[14px] bg-green-100 text-green-600">
                    <x-icon name="building-2" class="h-6 w-6" />
                </div>
                <div>
                    <p class="text-sm font-semibold text-slate-600">Setor vinculado</p>
                    <p class="mt-1 text-xl font-bold text-slate-950">{{ auth()->user()->sector?->name ?? 'Pendente' }}</p>
                </div>
            </div>
        </x-card>
    </div>

    <x-card>
        <div class="mb-4 flex items-center justify-between">
            <div>
                <h3 class="text-lg font-bold text-slate-950">Próximos módulos</h3>
                <p class="mt-1 text-sm text-slate-500">A estrutura visual já está pronta para crescer sem trocar o layout.</p>
            </div>
        </div>

        <div class="overflow-hidden rounded-2xl border border-slate-200">
            <table class="w-full text-left">
                <thead class="h-11 bg-slate-50 text-xs font-bold text-slate-700">
                    <tr>
                        <th class="px-4 py-3">Módulo</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Objetivo</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm text-slate-600">
                    <tr class="h-12 hover:bg-slate-50">
                        <td class="px-4 py-3 font-semibold text-slate-900">Setores</td>
                        <td class="px-4 py-3"><x-badge variant="success">Pronto</x-badge></td>
                        <td class="px-4 py-3">Cadastrar setores e gerar códigos de acesso.</td>
                    </tr>
                    <tr class="h-12 hover:bg-slate-50">
                        <td class="px-4 py-3 font-semibold text-slate-900">Importações</td>
                        <td class="px-4 py-3"><x-badge>Futuro</x-badge></td>
                        <td class="px-4 py-3">Enviar planilhas e revisar colunas de forma simples.</td>
                    </tr>
                    <tr class="h-12 hover:bg-slate-50">
                        <td class="px-4 py-3 font-semibold text-slate-900">Dashboards</td>
                        <td class="px-4 py-3"><x-badge>Futuro</x-badge></td>
                        <td class="px-4 py-3">Montar gráficos e indicadores com filtros interativos.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </x-card>
</div>

<div class="space-y-5">
    @if (session('status'))
        <div class="rounded-2xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-semibold text-green-800">
            {{ session('status') }}
        </div>
    @endif

    <x-card>
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <x-badge variant="info">Meus Dashboards</x-badge>
                <h2 class="mt-4 text-2xl font-bold text-slate-950">
                    @if ($isAdmin && ! $selectedSector)
                        Setores com dashboards em rascunho
                    @elseif ($isAdmin && $selectedSector)
                        Dashboards de {{ $selectedSector->name }}
                    @else
                        Dashboards do setor
                    @endif
                </h2>
                <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600">
                    @if ($isAdmin && ! $selectedSector)
                        Escolha um setor para visualizar os dashboards em rascunho sem misturar todos os cards na mesma tela.
                    @elseif ($isAdmin && $selectedSector)
                        Acompanhe os dashboards deste setor e continue a importação quando necessário.
                    @else
                        Crie painéis para organizar indicadores, importar planilhas e acompanhar informações importantes do seu setor.
                    @endif
                </p>
            </div>

            @if ($isAdmin && $selectedSector)
                <button
                    type="button"
                    wire:click="clearSectorSelection"
                    class="inline-flex h-11 items-center justify-center gap-2 rounded-[10px] border border-slate-200 bg-white px-[18px] text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                >
                    <x-icon name="arrow-left" class="h-5 w-5" />
                    Voltar para setores
                </button>
            @elseif ($canCreate)
                <a
                    href="{{ route('dashboards.create') }}"
                    wire:navigate
                    class="inline-flex h-11 items-center justify-center gap-2 rounded-[10px] bg-seduc-primary px-[18px] text-sm font-semibold text-white shadow-seduc-button transition hover:bg-seduc-primary-hover"
                >
                    <x-icon name="plus-circle" class="h-5 w-5" />
                    Criar Dashboard
                </a>
            @endif
        </div>
    </x-card>

    @if (! $isAdmin && ! $canCreate)
        <x-card>
            <div class="flex items-start gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-[14px] bg-amber-100 text-amber-700">
                    <x-icon name="building-2" class="h-6 w-6" />
                </div>
                <div>
                    <h3 class="text-lg font-bold text-slate-950">Usuário sem setor vinculado</h3>
                    <p class="mt-1 text-sm leading-6 text-slate-600">
                        Para criar dashboards, a conta precisa estar vinculada a um setor. Usuários cadastrados com código de setor já recebem esse vínculo automaticamente.
                    </p>
                </div>
            </div>
        </x-card>
    @endif

    <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
        <x-card>
            <p class="text-sm font-semibold text-slate-600">{{ $isAdmin && ! $selectedSector ? 'Setores com rascunho' : 'Total de dashboards' }}</p>
            <p class="mt-2 text-2xl font-bold text-slate-950">{{ $isAdmin && ! $selectedSector ? $sectorCards->count() : $summary['total'] }}</p>
        </x-card>

        <x-card>
            <p class="text-sm font-semibold text-slate-600">Rascunhos</p>
            <p class="mt-2 text-2xl font-bold text-slate-950">{{ $summary['draft'] }}</p>
        </x-card>

        <x-card>
            <p class="text-sm font-semibold text-slate-600">Prontos</p>
            <p class="mt-2 text-2xl font-bold text-green-700">{{ $summary['ready'] }}</p>
        </x-card>

        <x-card>
            <p class="text-sm font-semibold text-slate-600">Registros importados</p>
            <p class="mt-2 text-2xl font-bold text-seduc-primary">{{ $summary['records'] }}</p>
        </x-card>
    </div>

    @if ($isAdmin && ! $selectedSector)
        @if ($sectorCards->isEmpty())
            <x-card>
                <div class="flex flex-col items-center py-10 text-center">
                    <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-seduc-primary-soft text-seduc-primary">
                        <x-icon name="building-2" class="h-8 w-8" />
                    </div>
                    <h3 class="mt-5 text-xl font-bold text-slate-950">Nenhum setor com dashboard em rascunho</h3>
                    <p class="mt-2 max-w-xl text-sm leading-6 text-slate-600">
                        Quando algum setor tiver dashboards pendentes, ele aparecerá aqui como um card clicável.
                    </p>
                </div>
            </x-card>
        @else
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($sectorCards as $sector)
                    <button
                        type="button"
                        wire:click="selectSector({{ $sector->id }})"
                        wire:key="sector-card-{{ $sector->id }}"
                        class="rounded-2xl border border-slate-200 bg-white p-5 text-left shadow-seduc-card transition hover:-translate-y-0.5 hover:border-blue-200 hover:shadow-seduc-card-hover focus:outline-none focus:ring-4 focus:ring-blue-100"
                    >
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <x-badge variant="warning">{{ $sector->draft_dashboards_count }} rascunhos</x-badge>
                                <h3 class="mt-3 text-xl font-bold text-slate-950">{{ $sector->name }}</h3>
                                <p class="mt-2 line-clamp-2 text-sm leading-6 text-slate-600">
                                    {{ $sector->description ?: 'Sem descrição informada.' }}
                                </p>
                            </div>

                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-[14px] bg-seduc-primary-soft text-seduc-primary">
                                <x-icon name="building-2" class="h-6 w-6" />
                            </div>
                        </div>

                        <div class="mt-5 grid grid-cols-3 gap-3 rounded-2xl bg-slate-50 p-4 text-sm">
                            <div>
                                <p class="text-xs font-semibold text-slate-500">Total</p>
                                <p class="mt-1 font-bold text-slate-950">{{ $sector->dashboards_count }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-slate-500">Prontos</p>
                                <p class="mt-1 font-bold text-green-700">{{ $sector->ready_dashboards_count }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-slate-500">Usuários</p>
                                <p class="mt-1 font-bold text-slate-950">{{ $sector->users_count }}</p>
                            </div>
                        </div>

                        <div class="mt-4 flex items-center justify-between text-sm">
                            <span class="font-semibold text-slate-500">
                                Atualizado em
                                {{ $sector->dashboards_max_updated_at ? \Illuminate\Support\Carbon::parse($sector->dashboards_max_updated_at)->format('d/m/Y H:i') : 'sem data' }}
                            </span>
                            <span class="font-bold text-seduc-primary">Ver dashboards</span>
                        </div>
                    </button>
                @endforeach
            </div>
        @endif
    @elseif ($dashboards->isEmpty())
        <x-card>
            <div class="flex flex-col items-center py-10 text-center">
                <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-seduc-primary-soft text-seduc-primary">
                    <x-icon name="layout-dashboard" class="h-8 w-8" />
                </div>
                <h3 class="mt-5 text-xl font-bold text-slate-950">Nenhum dashboard criado ainda</h3>
                <p class="mt-2 max-w-xl text-sm leading-6 text-slate-600">
                    Comece criando um dashboard em rascunho. Na próxima etapa, ele poderá receber uma planilha e gerar gráficos automaticamente.
                </p>

                @if ($canCreate)
                    <a
                        href="{{ route('dashboards.create') }}"
                        wire:navigate
                        class="mt-5 inline-flex h-11 items-center justify-center gap-2 rounded-[10px] bg-seduc-primary px-[18px] text-sm font-semibold text-white shadow-seduc-button transition hover:bg-seduc-primary-hover"
                    >
                        <x-icon name="plus-circle" class="h-5 w-5" />
                        Criar Dashboard
                    </a>
                @endif
            </div>
        </x-card>
    @else
        @if ($isAdmin && $selectedSector)
            <div class="rounded-2xl border border-blue-100 bg-blue-50 px-4 py-3 text-sm font-semibold text-seduc-primary">
                Exibindo {{ $dashboards->count() }} dashboard(s) do setor {{ $selectedSector->name }}.
            </div>
        @endif

        <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
            @foreach ($dashboards as $dashboard)
                <x-card wire:key="dashboard-{{ $dashboard->id }}">
                    <div class="flex flex-col gap-5">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <x-badge :variant="$dashboard->status->badgeVariant()">
                                    {{ $dashboard->status->label() }}
                                </x-badge>
                                <h3 class="mt-3 text-xl font-bold text-slate-950">{{ $dashboard->name }}</h3>
                                <p class="mt-2 line-clamp-2 text-sm leading-6 text-slate-600">
                                    {{ $dashboard->description ?: 'Sem descrição informada.' }}
                                </p>
                            </div>

                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-[14px] bg-seduc-primary-soft text-seduc-primary">
                                <x-icon name="chart-bar" class="h-6 w-6" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-3 rounded-2xl bg-slate-50 p-4 text-sm md:grid-cols-3">
                            <div>
                                <p class="text-xs font-semibold text-slate-500">Última atualização</p>
                                <p class="mt-1 font-bold text-slate-950">{{ $dashboard->updated_at->format('d/m/Y H:i') }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-slate-500">Quantidade de registros</p>
                                <p class="mt-1 font-bold text-slate-950">{{ $dashboard->recordsCount() }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-slate-500">Setor</p>
                                <p class="mt-1 truncate font-bold text-slate-950">{{ $dashboard->sector?->name ?? 'Sem setor' }}</p>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center gap-2">
                            <a href="{{ route('dashboards.show', $dashboard) }}" wire:navigate class="inline-flex h-10 items-center gap-2 rounded-[10px] bg-seduc-primary px-3.5 text-sm font-semibold text-white shadow-seduc-button transition hover:bg-seduc-primary-hover">
                                <x-icon name="eye" class="h-4 w-4" />
                                Visualizar
                            </a>

                            <a href="{{ route('dashboards.edit-basic', $dashboard) }}" wire:navigate class="inline-flex h-10 items-center gap-2 rounded-[10px] border border-slate-200 bg-white px-3.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                                <x-icon name="pencil" class="h-4 w-4" />
                                Editar
                            </a>

                            <a href="{{ route('dashboards.feed', $dashboard) }}" wire:navigate class="inline-flex h-10 items-center gap-2 rounded-[10px] border border-blue-100 bg-blue-50 px-3.5 text-sm font-semibold text-seduc-primary transition hover:bg-blue-100">
                                <x-icon name="upload" class="h-4 w-4" />
                                Alimentar Dados
                            </a>

                            <button
                                type="button"
                                wire:click="deleteDashboard({{ $dashboard->id }})"
                                wire:confirm="Tem certeza que deseja apagar este dashboard?"
                                class="inline-flex h-10 items-center gap-2 rounded-[10px] bg-red-50 px-3.5 text-sm font-semibold text-red-700 transition hover:bg-red-100"
                            >
                                <x-icon name="trash-2" class="h-4 w-4" />
                                Apagar
                            </button>
                        </div>
                    </div>
                </x-card>
            @endforeach
        </div>
    @endif
</div>

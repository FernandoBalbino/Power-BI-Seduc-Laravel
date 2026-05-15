<div class="space-y-5">
    @if (session('status'))
        <div class="rounded-2xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-semibold text-green-800">
            {{ session('status') }}
        </div>
    @endif

    <x-card>
        <div class="flex flex-col gap-5 xl:flex-row xl:items-start xl:justify-between">
            <div>
                <a href="{{ route('dashboards.show', $dashboard) }}" wire:navigate class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 transition hover:text-seduc-primary">
                    <x-icon name="arrow-left" class="h-4 w-4" />
                    Voltar para o dashboard
                </a>
                <h2 class="mt-3 text-3xl font-bold text-slate-950">Gerar gráficos</h2>
                <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600">
                    Use os relacionamentos salvos para criar widgets iniciais. Você pode gerar uma primeira versão automaticamente ou montar gráficos manualmente.
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                <a href="{{ route('dashboards.relationships', $dashboard) }}" wire:navigate class="inline-flex h-11 items-center gap-2 rounded-[10px] border border-slate-200 bg-white px-[18px] text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                    <x-icon name="refresh-cw" class="h-4 w-4" />
                    Relacionar Colunas
                </a>

                <x-button type="button" wire:click="generateAutomaticWidgets">
                    <x-icon name="chart-bar" class="h-4 w-4" />
                    Gerar automaticamente
                </x-button>
            </div>
        </div>
    </x-card>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
        <x-card>
            <p class="text-sm font-semibold text-slate-600">Widgets criados</p>
            <p class="mt-2 text-2xl font-bold text-slate-950">{{ $widgets->count() }}</p>
        </x-card>

        <x-card>
            <p class="text-sm font-semibold text-slate-600">Relacionamentos</p>
            <p class="mt-2 text-2xl font-bold text-slate-950">{{ $relationships->count() }}</p>
        </x-card>

        <x-card>
            <p class="text-sm font-semibold text-slate-600">Sugestões</p>
            <p class="mt-2 text-2xl font-bold text-seduc-primary">{{ count($suggestions) }}</p>
        </x-card>

        <x-card>
            <p class="text-sm font-semibold text-slate-600">Registros</p>
            <p class="mt-2 text-2xl font-bold text-slate-950">{{ $dashboard->recordsCount() }}</p>
        </x-card>
    </div>

    <div class="grid grid-cols-1 gap-5 xl:grid-cols-[minmax(0,1fr)_380px]">
        <div class="space-y-5">
            <x-card>
                <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-slate-950">Sugestões automáticas</h3>
                        <p class="mt-1 text-sm leading-6 text-slate-600">
                            O sistema prioriza cards, barras, donut, linha por data e uma tabela resumida, com no máximo oito widgets iniciais.
                        </p>
                        @error('automaticWidgets')
                            <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <x-button type="button" wire:click="generateAutomaticWidgets">
                        <x-icon name="save" class="h-4 w-4" />
                        Criar widgets sugeridos
                    </x-button>
                </div>

                <div class="mt-5 grid gap-4 lg:grid-cols-2">
                    @forelse ($suggestions as $suggestion)
                        <div class="rounded-2xl border border-slate-200 bg-white p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-sm font-bold text-slate-950">{{ $suggestion['title'] }}</p>
                                    <p class="mt-1 text-sm leading-6 text-slate-600">{{ $suggestion['reason'] }}</p>
                                </div>
                                <x-badge variant="info">{{ \App\Enums\DashboardWidgetChartType::from($suggestion['chart_type'])->label() }}</x-badge>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-6 text-sm text-slate-500">
                            Nenhuma sugestão disponível. Crie relacionamentos entre colunas antes de gerar gráficos.
                        </div>
                    @endforelse
                </div>
            </x-card>

            <x-card padding="p-0">
                <div class="border-b border-slate-200 p-5">
                    <h3 class="text-lg font-bold text-slate-950">Widgets do dashboard</h3>
                    <p class="mt-1 text-sm leading-6 text-slate-600">
                        Estes widgets já ficam salvos e serão usados na visualização final.
                    </p>
                </div>

                <div class="grid gap-4 p-5 xl:grid-cols-2">
                    @forelse ($widgets as $widget)
                        <div class="relative">
                            <x-dashboard-widget :widget="$widget" :data="$widgetData[$widget->id] ?? []" />
                            <button type="button" wire:click="removeWidget({{ $widget->id }})" class="absolute right-4 top-4 inline-flex h-9 items-center gap-2 rounded-[10px] bg-red-50 px-3 text-xs font-bold text-red-700 transition hover:bg-red-100">
                                <x-icon name="trash-2" class="h-4 w-4" />
                                Remover
                            </button>
                        </div>
                    @empty
                        <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-8 text-center">
                            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-seduc-primary-soft text-seduc-primary">
                                <x-icon name="chart-bar" class="h-7 w-7" />
                            </div>
                            <h3 class="mt-4 text-lg font-bold text-slate-950">Nenhum widget criado ainda.</h3>
                            <p class="mt-2 text-sm leading-6 text-slate-600">Gere automaticamente ou crie o primeiro gráfico manualmente.</p>
                        </div>
                    @endforelse
                </div>
            </x-card>
        </div>

        <x-card>
            <h3 class="text-lg font-bold text-slate-950">Criar gráfico manualmente</h3>
            <p class="mt-1 text-sm leading-6 text-slate-600">
                Monte um widget escolhendo tipo, agrupamento, valor, cálculo, ordenação e limite.
            </p>

            <div class="mt-5 space-y-4">
                <div class="space-y-2">
                    <label for="manualTitle" class="block text-[13px] font-semibold leading-5 text-slate-950">Título do gráfico</label>
                    <input
                        id="manualTitle"
                        type="text"
                        wire:model="manualTitle"
                        class="h-11 w-full rounded-[10px] border border-slate-300 bg-white px-3.5 text-sm text-slate-950 transition focus:border-seduc-primary focus:outline-none focus:ring-4 focus:ring-blue-100"
                    >
                    @error('manualTitle')
                        <p class="text-xs font-semibold text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="space-y-2">
                        <label for="manualChartType" class="block text-[13px] font-semibold leading-5 text-slate-950">Tipo do gráfico</label>
                        <select id="manualChartType" wire:model.live="manualChartType" class="h-11 w-full rounded-[10px] border border-slate-300 bg-white px-3.5 text-sm text-slate-950 transition focus:border-seduc-primary focus:outline-none focus:ring-4 focus:ring-blue-100">
                            @foreach ($this->chartTypeOptions as $chartTypeOption)
                                <option value="{{ $chartTypeOption['value'] }}">{{ $chartTypeOption['label'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label for="manualAggregation" class="block text-[13px] font-semibold leading-5 text-slate-950">Cálculo</label>
                        <select id="manualAggregation" wire:model.live="manualAggregation" class="h-11 w-full rounded-[10px] border border-slate-300 bg-white px-3.5 text-sm text-slate-950 transition focus:border-seduc-primary focus:outline-none focus:ring-4 focus:ring-blue-100">
                            @foreach ($this->aggregationOptions as $aggregationOption)
                                <option value="{{ $aggregationOption['value'] }}">{{ $aggregationOption['label'] }}</option>
                            @endforeach
                        </select>
                        @error('manualAggregation')
                            <p class="text-xs font-semibold text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                @if (! $this->isCardWidget)
                    <div class="space-y-2">
                        <label for="manualGroupingColumnId" class="block text-[13px] font-semibold leading-5 text-slate-950">Coluna de agrupamento</label>
                        <select id="manualGroupingColumnId" wire:model="manualGroupingColumnId" class="h-11 w-full rounded-[10px] border border-slate-300 bg-white px-3.5 text-sm text-slate-950 transition focus:border-seduc-primary focus:outline-none focus:ring-4 focus:ring-blue-100">
                            <option value="">Sem agrupamento</option>
                            @foreach ($groupingColumns as $column)
                                <option value="{{ $column->id }}">{{ $column->displayName() }} - {{ $column->type->label() }}</option>
                            @endforeach
                        </select>
                        @error('manualGroupingColumnId')
                            <p class="text-xs font-semibold text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                @else
                    <div class="rounded-2xl border border-blue-100 bg-blue-50 p-4">
                        <p class="text-sm font-semibold text-slate-950">Total geral</p>
                        <p class="mt-1 text-sm leading-6 text-slate-600">Cards de resumo mostram um único número do dashboard inteiro.</p>
                    </div>
                @endif

                <div class="space-y-2">
                    @if ($this->usesRecordCount)
                        <span class="block text-[13px] font-semibold leading-5 text-slate-950">O que será contado</span>
                        <div class="flex min-h-11 items-center rounded-[10px] border border-slate-200 bg-slate-50 px-3.5 text-sm font-semibold text-slate-700">
                            Quantidade de registros
                        </div>
                        <p class="text-xs font-semibold text-slate-500">Cada linha importada entra uma vez na contagem.</p>
                    @else
                        <label for="manualValueColumnId" class="block text-[13px] font-semibold leading-5 text-slate-950">Coluna de valor</label>
                        <select id="manualValueColumnId" wire:model="manualValueColumnId" class="h-11 w-full rounded-[10px] border border-slate-300 bg-white px-3.5 text-sm text-slate-950 transition focus:border-seduc-primary focus:outline-none focus:ring-4 focus:ring-blue-100">
                            @forelse ($valueColumns as $column)
                                <option value="{{ $column->id }}">{{ $column->displayName() }} - {{ $column->type->label() }}</option>
                            @empty
                                <option value="">Nenhuma coluna numérica disponível</option>
                            @endforelse
                        </select>
                        @error('manualValueColumnId')
                            <p class="text-xs font-semibold text-red-600">{{ $message }}</p>
                        @enderror
                    @endif
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="space-y-2">
                        <label for="manualSort" class="block text-[13px] font-semibold leading-5 text-slate-950">Ordenação</label>
                        <select id="manualSort" wire:model="manualSort" class="h-11 w-full rounded-[10px] border border-slate-300 bg-white px-3.5 text-sm text-slate-950 transition focus:border-seduc-primary focus:outline-none focus:ring-4 focus:ring-blue-100">
                            <option value="desc">Maior primeiro</option>
                            <option value="asc">Menor primeiro</option>
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label for="manualLimit" class="block text-[13px] font-semibold leading-5 text-slate-950">Limite</label>
                        <input
                            id="manualLimit"
                            type="number"
                            min="1"
                            max="50"
                            wire:model="manualLimit"
                            class="h-11 w-full rounded-[10px] border border-slate-300 bg-white px-3.5 text-sm text-slate-950 transition focus:border-seduc-primary focus:outline-none focus:ring-4 focus:ring-blue-100"
                        >
                        @error('manualLimit')
                            <p class="text-xs font-semibold text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="rounded-2xl border border-blue-100 bg-blue-50 p-4 text-sm leading-6 text-slate-600">
                    <p class="font-semibold text-slate-950">Filtros opcionais</p>
                    <p class="mt-1">A estrutura já fica preparada no widget, mas a escolha de filtros será habilitada em uma próxima etapa.</p>
                </div>

                <x-button type="button" wire:click="saveManualWidget" class="w-full">
                    <x-icon name="save" class="h-4 w-4" />
                    Criar gráfico
                </x-button>
            </div>
        </x-card>
    </div>
</div>

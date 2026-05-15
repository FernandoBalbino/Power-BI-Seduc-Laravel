<div class="space-y-5">
    @if (session('status'))
        <div class="rounded-2xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-semibold text-green-800">
            {{ session('status') }}
        </div>
    @endif

    <x-card>
        <div class="flex flex-col gap-5 xl:flex-row xl:items-start xl:justify-between">
            <div>
                <div class="flex flex-wrap items-center gap-2 text-sm font-semibold text-slate-500">
                    <a href="{{ route('dashboards.index') }}" wire:navigate class="transition hover:text-seduc-primary">Meus Dashboards</a>
                    <span>/</span>
                    <span>{{ $dashboard->sector?->name ?? 'Setor' }}</span>
                    <span>/</span>
                    <a href="{{ route('dashboards.show', $dashboard) }}" wire:navigate class="transition hover:text-seduc-primary">{{ $dashboard->name }}</a>
                    <span>/</span>
                    <span class="text-slate-700">Relacionar Colunas</span>
                </div>

                <h2 class="mt-4 text-3xl font-bold text-slate-950">Relacionar Colunas</h2>
                <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600">
                    Conecte colunas para definir quais combinações poderão gerar gráficos e indicadores nas próximas etapas.
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                <a href="{{ route('dashboards.show', $dashboard) }}" wire:navigate class="inline-flex h-11 items-center gap-2 rounded-[10px] border border-slate-200 bg-white px-[18px] text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                    <x-icon name="arrow-left" class="h-4 w-4" />
                    Voltar
                </a>

                <button type="button" wire:click="setMode('automatic')" class="inline-flex h-11 items-center gap-2 rounded-[10px] border {{ $mode === 'automatic' ? 'border-blue-200 bg-blue-50 text-seduc-primary' : 'border-slate-200 bg-white text-slate-700' }} px-[18px] text-sm font-semibold transition hover:bg-blue-50">
                    <x-icon name="refresh-cw" class="h-4 w-4" />
                    Sugestões automáticas
                </button>

                <button type="button" wire:click="setMode('manual')" class="inline-flex h-11 items-center gap-2 rounded-[10px] {{ $mode === 'manual' ? 'bg-seduc-primary text-white shadow-seduc-button' : 'border border-slate-200 bg-white text-slate-700' }} px-[18px] text-sm font-semibold transition hover:bg-seduc-primary-hover hover:text-white">
                    <x-icon name="plus-circle" class="h-4 w-4" />
                    Criar manualmente
                </button>
            </div>
        </div>
    </x-card>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
        <x-card>
            <p class="text-sm font-semibold text-slate-600">Campos detectados</p>
            <p class="mt-2 text-2xl font-bold text-slate-950">{{ $summary['columns'] }}</p>
        </x-card>

        <x-card>
            <p class="text-sm font-semibold text-slate-600">Relações criadas</p>
            <p class="mt-2 text-2xl font-bold text-slate-950">{{ $summary['relationships'] }}</p>
        </x-card>

        <x-card>
            <p class="text-sm font-semibold text-slate-600">Sugestões disponíveis</p>
            <p class="mt-2 text-2xl font-bold text-seduc-primary">{{ $summary['suggestions'] }}</p>
        </x-card>

        <x-card>
            <p class="text-sm font-semibold text-slate-600">Sem relação</p>
            <p class="mt-2 text-2xl font-bold text-amber-600">{{ $summary['without_relationship'] }}</p>
        </x-card>
    </div>

    <div class="grid grid-cols-1 gap-5 xl:grid-cols-[minmax(0,1fr)_320px]">
        <div class="space-y-5">
            <x-card>
                <div class="flex flex-wrap items-center gap-3">
                    <button type="button" wire:click="setMode('automatic')" class="inline-flex h-9 items-center gap-2 rounded-full px-4 text-sm font-bold transition {{ $mode === 'automatic' ? 'bg-seduc-primary text-white shadow-seduc-button' : 'bg-slate-100 text-slate-600 hover:bg-blue-50 hover:text-seduc-primary' }}">
                        Todas
                        <span class="rounded-full bg-white/20 px-2 py-0.5 text-xs">{{ $summary['suggestions'] }}</span>
                    </button>

                    <button type="button" wire:click="setMode('manual')" class="inline-flex h-9 items-center gap-2 rounded-full px-4 text-sm font-bold transition {{ $mode === 'manual' ? 'bg-seduc-primary text-white shadow-seduc-button' : 'bg-slate-100 text-slate-600 hover:bg-blue-50 hover:text-seduc-primary' }}">
                        Manual
                    </button>

                    <span class="inline-flex h-9 items-center rounded-full bg-slate-100 px-4 text-sm font-bold text-slate-600">
                        Relacionadas {{ $summary['relationships'] }}
                    </span>
                </div>
            </x-card>

            @if ($mode === 'automatic')
                <x-card>
                    <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                        <div>
                            <h3 class="text-lg font-bold text-slate-950">Sugestões automáticas</h3>
                            <p class="mt-1 text-sm leading-6 text-slate-600">
                                Revise as combinações sugeridas e salve apenas as que fazem sentido para o dashboard.
                            </p>
                            @error('automaticRelationships')
                                <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <button type="button" wire:click="acceptAllSuggestions" class="inline-flex h-11 items-center gap-2 rounded-[10px] border border-blue-100 bg-blue-50 px-[18px] text-sm font-semibold text-seduc-primary transition hover:bg-blue-100">
                                <x-icon name="shield-check" class="h-4 w-4" />
                                Aceitar todas
                            </button>

                            <x-button type="button" wire:click="saveAutomaticRelationships">
                                <x-icon name="save" class="h-4 w-4" />
                                Salvar selecionadas
                            </x-button>
                        </div>
                    </div>

                    <div class="mt-5 grid gap-4 lg:grid-cols-2">
                        @forelse ($suggestions as $suggestion)
                            <label wire:key="suggestion-{{ $suggestion['key'] }}" class="block rounded-2xl border border-slate-200 bg-white p-4 transition hover:border-blue-200 hover:bg-blue-50/40">
                                <div class="flex items-start gap-3">
                                    <input type="checkbox" wire:model="selectedSuggestions.{{ $suggestion['key'] }}" class="mt-1 h-4 w-4 rounded border-slate-300 text-seduc-primary focus:ring-blue-100">

                                    <div class="min-w-0 flex-1">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="text-sm font-bold text-slate-950">{{ $suggestion['base_name'] }}</span>
                                            <span class="text-slate-400">+</span>
                                            <span class="text-sm font-bold text-slate-950">{{ $suggestion['related_name'] }}</span>
                                            <x-badge variant="info">{{ $suggestion['aggregation_label'] }}</x-badge>
                                        </div>

                                        <p class="mt-2 text-sm leading-6 text-slate-600">{{ $suggestion['reason'] }}</p>

                                        <div class="mt-3 flex flex-wrap gap-2">
                                            @foreach ($suggestion['chart_types'] as $chartType)
                                                <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">{{ $chartType }}</span>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </label>
                        @empty
                            <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-6 text-sm text-slate-500">
                                Nenhuma sugestão nova encontrada. Verifique se já existem relacionamentos salvos ou se há colunas métricas disponíveis.
                            </div>
                        @endforelse
                    </div>
                </x-card>
            @else
                <x-card>
                    <div class="flex flex-col gap-2 md:flex-row md:items-start md:justify-between">
                        <div>
                            <h3 class="text-lg font-bold text-slate-950">Criar relacionamento manual</h3>
                            <p class="mt-1 text-sm leading-6 text-slate-600">
                                Escolha uma coluna base, marque as colunas relacionadas e defina o cálculo para cada combinação.
                            </p>
                            @error('manualRelationships')
                                <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <x-button type="button" wire:click="saveManualRelationships">
                            <x-icon name="save" class="h-4 w-4" />
                            Salvar relacionamento
                        </x-button>
                    </div>

                    <div class="mt-5 grid gap-5 lg:grid-cols-[320px_minmax(0,1fr)]">
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <label for="manualBaseColumnId" class="block text-[13px] font-semibold leading-5 text-slate-950">Coluna base</label>
                            <select
                                id="manualBaseColumnId"
                                wire:model.live="manualBaseColumnId"
                                class="mt-2 h-11 w-full rounded-[10px] border border-slate-300 bg-white px-3.5 text-sm text-slate-950 transition focus:border-seduc-primary focus:outline-none focus:ring-4 focus:ring-blue-100"
                            >
                                @foreach ($baseColumns as $column)
                                    <option value="{{ $column->id }}">{{ $column->displayName() }} - {{ $column->type->label() }}</option>
                                @endforeach
                            </select>

                            <div class="mt-4 rounded-2xl border border-blue-100 bg-blue-50 p-4 text-sm leading-6 text-slate-600">
                                <p class="font-semibold text-slate-950">Dica</p>
                                <p class="mt-1">Use município, status ou data como base. Código/Identificador costuma funcionar melhor como referência ou filtro.</p>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <label class="block rounded-2xl border border-slate-200 bg-white p-4">
                                <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                                    <div class="flex items-start gap-3">
                                        <input type="checkbox" value="records_count" wire:model.live="manualRelatedColumnIds" class="mt-1 h-4 w-4 rounded border-slate-300 text-seduc-primary focus:ring-blue-100">
                                        <div>
                                            <p class="text-sm font-bold text-slate-950">Quantidade de registros</p>
                                            <p class="mt-1 text-xs font-semibold text-slate-500">Cria contagem por coluna base.</p>
                                        </div>
                                    </div>
                                    <x-badge variant="neutral">Contagem</x-badge>
                                </div>
                            </label>

                            @foreach ($relatedColumns as $column)
                                @if ($column->id !== $manualBaseColumnId)
                                    <div wire:key="manual-related-{{ $column->id }}" class="rounded-2xl border border-slate-200 bg-white p-4">
                                        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                                            <label class="flex items-start gap-3">
                                                <input type="checkbox" value="{{ $column->id }}" wire:model.live="manualRelatedColumnIds" class="mt-1 h-4 w-4 rounded border-slate-300 text-seduc-primary focus:ring-blue-100">
                                                <span>
                                                    <span class="block text-sm font-bold text-slate-950">{{ $column->displayName() }}</span>
                                                    <span class="mt-1 block text-xs font-semibold text-slate-500">{{ $column->type->label() }}</span>
                                                </span>
                                            </label>

                                            <select
                                                wire:model="manualAggregations.{{ $column->id }}"
                                                class="h-10 w-full rounded-[10px] border border-slate-300 bg-white px-3 text-sm text-slate-950 transition focus:border-seduc-primary focus:outline-none focus:ring-4 focus:ring-blue-100 md:w-44"
                                            >
                                                @foreach ($this->aggregationOptions as $aggregationOption)
                                                    <option value="{{ $aggregationOption['value'] }}">{{ $aggregationOption['label'] }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </x-card>
            @endif

            <x-card padding="p-0">
                <div class="border-b border-slate-200 p-5">
                    <h3 class="text-lg font-bold text-slate-950">Relacionamentos salvos</h3>
                    <p class="mt-1 text-sm leading-6 text-slate-600">
                        Estas combinações ficarão disponíveis para a criação de gráficos na próxima etapa.
                    </p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-bold text-slate-600">Coluna base</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-slate-600">Relacionada</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-slate-600">Cálculo</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-slate-600">Origem</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-slate-600">Gráficos</th>
                                <th class="px-4 py-3 text-right text-xs font-bold text-slate-600">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse ($relationships as $relationship)
                                <tr class="transition hover:bg-slate-50">
                                    <td class="px-4 py-3 text-sm font-semibold text-slate-950">{{ $relationship->baseColumn->displayName() }}</td>
                                    <td class="px-4 py-3 text-sm text-slate-700">{{ $relationship->relatedColumn?->displayName() ?? 'Quantidade de registros' }}</td>
                                    <td class="px-4 py-3"><x-badge variant="info">{{ $relationship->aggregation->label() }}</x-badge></td>
                                    <td class="px-4 py-3"><x-badge variant="{{ $relationship->relationship_type === \App\Enums\DashboardRelationshipType::Auto ? 'purple' : 'neutral' }}">{{ $relationship->relationship_type->label() }}</x-badge></td>
                                    <td class="px-4 py-3">
                                        <div class="flex flex-wrap gap-2">
                                            @foreach ($relationship->suggestedChartTypes() as $chartType)
                                                <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">{{ $chartType }}</span>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <button type="button" wire:click="removeRelationship({{ $relationship->id }})" class="inline-flex h-9 items-center gap-2 rounded-[10px] bg-red-50 px-3 text-xs font-bold text-red-700 transition hover:bg-red-100">
                                            <x-icon name="trash-2" class="h-4 w-4" />
                                            Remover
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-10 text-center text-sm text-slate-500">
                                        Nenhum relacionamento salvo ainda.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-card>
        </div>

        <div class="space-y-5">
            <x-card>
                <h3 class="text-base font-bold text-slate-950">Resumo dos relacionamentos</h3>

                <div class="mt-4 space-y-3">
                    <div class="rounded-2xl border border-slate-200 bg-white p-4">
                        <p class="text-2xl font-bold text-slate-950">{{ $summary['relationships'] }}</p>
                        <p class="mt-1 text-xs font-semibold text-slate-500">relações criadas</p>
                    </div>

                    <div class="rounded-2xl border border-green-100 bg-green-50 p-4">
                        <p class="text-2xl font-bold text-green-700">{{ $summary['suggestions'] }}</p>
                        <p class="mt-1 text-xs font-semibold text-green-800">sugestões automáticas</p>
                    </div>

                    <div class="rounded-2xl border border-amber-100 bg-amber-50 p-4">
                        <p class="text-2xl font-bold text-amber-700">{{ $summary['without_relationship'] }}</p>
                        <p class="mt-1 text-xs font-semibold text-amber-800">campos sem relação</p>
                    </div>
                </div>
            </x-card>

            <x-card padding="p-0">
                <div class="border-b border-slate-200 p-5">
                    <h3 class="text-base font-bold text-slate-950">Campos detectados</h3>
                </div>

                <div class="divide-y divide-slate-100">
                    @foreach ($fieldCounts as $label => $count)
                        <div class="flex items-center justify-between px-5 py-3">
                            <span class="text-sm font-semibold text-slate-700">{{ $label }}</span>
                            <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-600">{{ $count }}</span>
                        </div>
                    @endforeach
                </div>
            </x-card>
        </div>
    </div>
</div>

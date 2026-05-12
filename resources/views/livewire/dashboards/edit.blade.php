<div class="space-y-5">
    <x-card>
        <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
            <div>
                <a href="{{ route('dashboards.show', $dashboard) }}" wire:navigate class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 transition hover:text-seduc-primary">
                    <x-icon name="arrow-left" class="h-4 w-4" />
                    Voltar para o dashboard
                </a>
                <h2 class="mt-3 text-2xl font-bold text-slate-950">Editar Dashboard</h2>
                <p class="mt-1 max-w-3xl text-sm leading-6 text-slate-600">
                    A montagem de gráficos, indicadores e filtros será habilitada após a importação dos dados.
                </p>
            </div>

            <x-badge variant="warning">Placeholder</x-badge>
        </div>
    </x-card>

    <x-card>
        <div class="flex min-h-[360px] flex-col items-center justify-center text-center">
            <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 text-slate-600">
                <x-icon name="chart-bar" class="h-8 w-8" />
            </div>
            <h3 class="mt-5 text-xl font-bold text-slate-950">Editor visual será criado nas próximas etapas.</h3>
            <p class="mt-2 max-w-xl text-sm leading-6 text-slate-600">
                Por enquanto, use a edição básica para nome e descrição, ou avance para a importação de planilha.
            </p>

            <div class="mt-5 flex flex-wrap justify-center gap-3">
                <a href="{{ route('dashboards.edit-basic', $dashboard) }}" wire:navigate class="inline-flex h-11 items-center gap-2 rounded-[10px] border border-slate-200 bg-white px-[18px] text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                    <x-icon name="pencil" class="h-4 w-4" />
                    Editar Informações
                </a>

                <a href="{{ route('dashboards.import', $dashboard) }}" wire:navigate class="inline-flex h-11 items-center gap-2 rounded-[10px] bg-seduc-primary px-[18px] text-sm font-semibold text-white shadow-seduc-button transition hover:bg-seduc-primary-hover">
                    <x-icon name="upload" class="h-4 w-4" />
                    Importar Planilha
                </a>
            </div>
        </div>
    </x-card>
</div>

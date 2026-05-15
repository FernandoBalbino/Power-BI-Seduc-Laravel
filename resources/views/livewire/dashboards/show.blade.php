<div class="space-y-5">
    @if (session('status'))
        <div class="rounded-2xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-semibold text-green-800">
            {{ session('status') }}
        </div>
    @endif

    <x-card>
        <div class="flex flex-col gap-5 md:flex-row md:items-start md:justify-between">
            <div>
                <a href="{{ route('dashboards.index') }}" wire:navigate class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 transition hover:text-seduc-primary">
                    <x-icon name="arrow-left" class="h-4 w-4" />
                    Voltar para Meus Dashboards
                </a>

                <div class="mt-4 flex flex-wrap items-center gap-3">
                    <x-badge :variant="$dashboard->status->badgeVariant()">
                        {{ $dashboard->status->label() }}
                    </x-badge>
                    <span class="text-sm text-slate-500">Atualizado em {{ $dashboard->updated_at->format('d/m/Y H:i') }}</span>
                </div>

                <h2 class="mt-3 text-2xl font-bold text-slate-950">{{ $dashboard->name }}</h2>
                <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600">
                    {{ $dashboard->description ?: 'Sem descrição informada.' }}
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                @if ($dashboard->status === \App\Enums\DashboardStatus::Draft)
                    <a href="{{ route('dashboards.import', $dashboard) }}" wire:navigate class="inline-flex h-11 items-center gap-2 rounded-[10px] bg-seduc-primary px-[18px] text-sm font-semibold text-white shadow-seduc-button transition hover:bg-seduc-primary-hover">
                        <x-icon name="upload" class="h-4 w-4" />
                        Importar Planilha
                    </a>
                @endif

                @if ($dashboard->columns()->exists())
                    <a href="{{ route('dashboards.relationships', $dashboard) }}" wire:navigate class="inline-flex h-11 items-center gap-2 rounded-[10px] bg-seduc-primary px-[18px] text-sm font-semibold text-white shadow-seduc-button transition hover:bg-seduc-primary-hover">
                        <x-icon name="refresh-cw" class="h-4 w-4" />
                        Relacionar Colunas
                    </a>
                @endif

                <a href="{{ route('dashboards.edit-basic', $dashboard) }}" wire:navigate class="inline-flex h-11 items-center gap-2 rounded-[10px] border border-slate-200 bg-white px-[18px] text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                    <x-icon name="pencil" class="h-4 w-4" />
                    Editar Informações
                </a>
            </div>
        </div>
    </x-card>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
        <x-card>
            <p class="text-sm font-semibold text-slate-600">Registros importados</p>
            <p class="mt-2 text-2xl font-bold text-slate-950">{{ $dashboard->recordsCount() }}</p>
        </x-card>

        <x-card>
            <p class="text-sm font-semibold text-slate-600">Setor</p>
            <p class="mt-2 truncate text-2xl font-bold text-slate-950">{{ $dashboard->sector?->name ?? 'Sem setor' }}</p>
        </x-card>

        <x-card>
            <p class="text-sm font-semibold text-slate-600">Criado por</p>
            <p class="mt-2 truncate text-2xl font-bold text-slate-950">{{ $dashboard->user?->name }}</p>
        </x-card>
    </div>

    <x-card>
        <div class="flex min-h-[360px] flex-col items-center justify-center text-center">
            <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-seduc-primary-soft text-seduc-primary">
                <x-icon name="file-spreadsheet" class="h-8 w-8" />
            </div>
            <h3 class="mt-5 text-xl font-bold text-slate-950">Este dashboard ainda não possui dados importados.</h3>
            <p class="mt-2 max-w-xl text-sm leading-6 text-slate-600">
                Na próxima etapa, esta área receberá a leitura da planilha, a revisão de colunas e os primeiros gráficos.
            </p>

            <div class="mt-5 flex flex-wrap justify-center gap-3">
                <a href="{{ route('dashboards.feed', $dashboard) }}" wire:navigate class="inline-flex h-11 items-center gap-2 rounded-[10px] bg-seduc-primary px-[18px] text-sm font-semibold text-white shadow-seduc-button transition hover:bg-seduc-primary-hover">
                    <x-icon name="upload" class="h-4 w-4" />
                    Alimentar Dados
                </a>

                <a href="{{ route('dashboards.edit', $dashboard) }}" wire:navigate class="inline-flex h-11 items-center gap-2 rounded-[10px] border border-slate-200 bg-white px-[18px] text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                    <x-icon name="chart-bar" class="h-4 w-4" />
                    Editar Dashboard
                </a>
            </div>
        </div>
    </x-card>
</div>

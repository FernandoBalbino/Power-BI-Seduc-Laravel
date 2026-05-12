<div class="space-y-5">
    @if (session('status'))
        <div class="rounded-2xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-semibold text-green-800">
            {{ session('status') }}
        </div>
    @endif

    <x-card>
        <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
            <div>
                <a href="{{ route('dashboards.show', $dashboard) }}" wire:navigate class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 transition hover:text-seduc-primary">
                    <x-icon name="arrow-left" class="h-4 w-4" />
                    Voltar para o dashboard
                </a>
                <h2 class="mt-3 text-2xl font-bold text-slate-950">Importar Planilha</h2>
                <p class="mt-1 max-w-3xl text-sm leading-6 text-slate-600">
                    Este é o início do fluxo de alimentação de dados do dashboard {{ $dashboard->name }}.
                </p>
            </div>

            <x-badge :variant="$dashboard->status->badgeVariant()">
                {{ $dashboard->status->label() }}
            </x-badge>
        </div>
    </x-card>

    <x-card>
        <div class="rounded-[14px] border-2 border-dashed border-[#6EA8FE] bg-[#F9FBFF] px-6 py-12 text-center">
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-seduc-primary-soft text-seduc-primary">
                <x-icon name="upload" class="h-7 w-7" />
            </div>
            <h3 class="mt-5 text-xl font-bold text-slate-950">Wizard de importação preparado</h3>
            <p class="mx-auto mt-2 max-w-2xl text-sm leading-6 text-slate-600">
                Na Etapa 4, esta tela receberá o envio da planilha, leitura das colunas, prévia dos dados e seleção dos tipos de informação.
            </p>
            <button type="button" disabled class="mt-6 inline-flex h-11 items-center justify-center gap-2 rounded-[10px] bg-slate-200 px-[18px] text-sm font-semibold text-slate-500">
                <x-icon name="file-spreadsheet" class="h-4 w-4" />
                Selecionar planilha
            </button>
        </div>
    </x-card>
</div>

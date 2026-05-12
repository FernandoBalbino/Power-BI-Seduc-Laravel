<div class="space-y-5">
    <div>
        <a href="{{ route('dashboards.show', $dashboard) }}" wire:navigate class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 transition hover:text-seduc-primary">
            <x-icon name="arrow-left" class="h-4 w-4" />
            Voltar para o dashboard
        </a>
        <h2 class="mt-3 text-2xl font-bold text-slate-950">Editar Informações Básicas</h2>
        <p class="mt-1 text-sm text-slate-500">Atualize nome e descrição sem alterar dados importados.</p>
    </div>

    <form wire:submit="save" class="grid grid-cols-1 gap-5 lg:grid-cols-[1fr_360px]">
        <x-card>
            <div class="space-y-4">
                <x-input
                    label="Nome do dashboard"
                    name="name"
                    placeholder="Ex.: Panorama de Obras"
                    wire:model="name"
                    :error="$errors->first('name')"
                    autofocus
                />

                <div class="space-y-2">
                    <label for="description" class="block text-[13px] font-semibold leading-5 text-slate-950">Descrição</label>
                    <textarea
                        id="description"
                        wire:model="description"
                        rows="6"
                        placeholder="Descreva o objetivo deste dashboard."
                        class="w-full rounded-[10px] border border-slate-300 bg-white px-3.5 py-3 text-sm text-slate-950 placeholder:text-slate-400 transition focus:border-seduc-primary focus:outline-none focus:ring-4 focus:ring-blue-100"
                    ></textarea>
                    @error('description')
                        <p class="text-xs font-medium text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </x-card>

        <div class="space-y-5">
            <x-card>
                <x-badge :variant="$dashboard->status->badgeVariant()">
                    {{ $dashboard->status->label() }}
                </x-badge>
                <p class="mt-4 text-sm leading-6 text-slate-600">
                    Esta edição altera apenas os textos de identificação do dashboard.
                </p>
            </x-card>

            <div class="flex justify-end gap-3">
                <a href="{{ route('dashboards.show', $dashboard) }}" wire:navigate class="inline-flex h-11 items-center justify-center rounded-[10px] border border-slate-200 bg-white px-[18px] text-sm font-semibold text-slate-900 transition hover:bg-slate-50">
                    Cancelar
                </a>

                <x-button type="submit">
                    <x-icon name="save" class="h-4 w-4" />
                    Salvar Alterações
                </x-button>
            </div>
        </div>
    </form>
</div>

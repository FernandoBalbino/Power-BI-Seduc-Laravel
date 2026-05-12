<div class="space-y-5">
    <div>
        <a href="{{ route('dashboards.index') }}" wire:navigate class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 transition hover:text-seduc-primary">
            <x-icon name="arrow-left" class="h-4 w-4" />
            Voltar para Meus Dashboards
        </a>
        <h2 class="mt-3 text-2xl font-bold text-slate-950">Criar Dashboard</h2>
        <p class="mt-1 text-sm text-slate-500">Crie a base do dashboard. A importação da planilha entra na próxima etapa.</p>
    </div>

    @unless ($canCreate)
        <x-card>
            <x-badge variant="warning">Setor pendente</x-badge>
            <h3 class="mt-4 text-xl font-bold text-slate-950">Não foi possível criar dashboard</h3>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">
                Sua conta ainda não está vinculada a um setor. Cadastros feitos com código de setor recebem esse vínculo automaticamente.
            </p>
        </x-card>
    @else
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
                            placeholder="Descreva o objetivo deste dashboard em linguagem simples."
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
                    <x-badge>Rascunho</x-badge>
                    <h3 class="mt-4 text-lg font-bold text-slate-950">Próximo passo</h3>
                    <p class="mt-2 text-sm leading-6 text-slate-600">
                        Após criar o dashboard, você poderá continuar para a tela de importação da planilha.
                    </p>

                    <div class="mt-4 rounded-2xl border border-dashed border-blue-200 bg-blue-50 p-4 text-sm text-slate-600">
                        <p class="font-semibold text-slate-950">Preparado para a Etapa 4</p>
                        <p class="mt-1 leading-6">O dashboard será criado com status de rascunho e sem registros importados.</p>
                    </div>
                </x-card>

                <div class="flex flex-col gap-3">
                    <x-button type="submit" class="w-full">
                        <x-icon name="save" class="h-4 w-4" />
                        Criar Dashboard
                    </x-button>

                    <button
                        type="button"
                        wire:click="saveAndImport"
                        class="inline-flex h-11 w-full items-center justify-center gap-2 rounded-[10px] border border-blue-100 bg-blue-50 px-[18px] text-sm font-semibold text-seduc-primary transition hover:bg-blue-100"
                    >
                        <x-icon name="upload" class="h-4 w-4" />
                        Criar e continuar para importação
                    </button>
                </div>
            </div>
        </form>
    @endunless
</div>

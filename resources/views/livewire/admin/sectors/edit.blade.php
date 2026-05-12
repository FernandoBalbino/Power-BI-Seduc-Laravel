<div class="space-y-5">
    @if (session('status'))
        <div class="rounded-2xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-semibold text-green-800">
            {{ session('status') }}
        </div>
    @endif

    <div class="flex items-center justify-between gap-4">
        <div>
            <a href="{{ route('admin.sectors.index') }}" wire:navigate class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 transition hover:text-seduc-primary">
                <x-icon name="arrow-left" class="h-4 w-4" />
                Voltar para setores
            </a>
            <h2 class="mt-3 text-2xl font-bold text-slate-950">Editar Setor</h2>
            <p class="mt-1 text-sm text-slate-500">Atualize os dados do setor e controle o código de cadastro.</p>
        </div>
    </div>

    <form wire:submit="save" class="grid grid-cols-1 gap-5 lg:grid-cols-[1fr_360px]">
        <x-card>
            <div class="space-y-4">
                <x-input
                    label="Nome do setor"
                    name="name"
                    placeholder="Ex.: SUENG"
                    wire:model="name"
                    :error="$errors->first('name')"
                    autofocus
                />

                <div class="space-y-2">
                    <label for="description" class="block text-[13px] font-semibold leading-5 text-slate-950">Descrição</label>
                    <textarea
                        id="description"
                        wire:model="description"
                        rows="5"
                        placeholder="Descreva de forma simples a finalidade deste setor."
                        class="w-full rounded-[10px] border border-slate-300 bg-white px-3.5 py-3 text-sm text-slate-950 placeholder:text-slate-400 transition focus:border-seduc-primary focus:outline-none focus:ring-4 focus:ring-blue-100"
                    ></textarea>
                    @error('description')
                        <p class="text-xs font-medium text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <label class="flex items-start gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <input type="checkbox" wire:model="isActive" class="mt-0.5 h-[18px] w-[18px] rounded border-slate-300 text-seduc-primary focus:ring-blue-100">
                    <span>
                        <span class="block text-sm font-bold text-slate-950">Setor ativo</span>
                        <span class="mt-1 block text-sm leading-6 text-slate-500">Quando desativado, o código não permite novos cadastros.</span>
                    </span>
                </label>
            </div>
        </x-card>

        <div class="space-y-5">
            <x-card>
                <x-badge :variant="$isActive ? 'success' : 'warning'">
                    {{ $isActive ? 'Ativo' : 'Inativo' }}
                </x-badge>
                <p class="mt-4 text-sm leading-6 text-slate-600">
                    O código pode ser regenerado a qualquer momento. O código anterior deixa de funcionar.
                </p>

                <div class="mt-4 rounded-2xl border border-dashed border-blue-200 bg-blue-50 p-4">
                    <p class="text-xs font-semibold text-slate-500">Código atual</p>
                    <p class="mt-2 break-all font-mono text-xl font-bold text-slate-950">{{ $registrationCode }}</p>
                </div>

                @error('registrationCode')
                    <p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>
                @enderror

                <div class="mt-4 grid grid-cols-2 gap-3">
                    <button
                        type="button"
                        wire:click="regenerateCode"
                        class="inline-flex h-11 items-center justify-center gap-2 rounded-[10px] border border-slate-200 bg-white px-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                    >
                        <x-icon name="refresh-cw" class="h-4 w-4" />
                        Regenerar
                    </button>

                    <button
                        type="button"
                        x-data="{ copied: false }"
                        @click="navigator.clipboard.writeText(@js($registrationCode)); copied = true; setTimeout(() => copied = false, 1400)"
                        class="inline-flex h-11 items-center justify-center gap-2 rounded-[10px] border border-blue-100 bg-blue-50 px-3 text-sm font-semibold text-seduc-primary transition hover:bg-blue-100"
                    >
                        <x-icon name="copy" class="h-4 w-4" />
                        <span x-text="copied ? 'Copiado' : 'Copiar'">Copiar</span>
                    </button>
                </div>
            </x-card>

            <div class="flex justify-end gap-3">
                <a href="{{ route('admin.sectors.index') }}" wire:navigate class="inline-flex h-11 items-center justify-center rounded-[10px] border border-slate-200 bg-white px-[18px] text-sm font-semibold text-slate-900 transition hover:bg-slate-50">
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

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
                    Envie uma planilha para o dashboard {{ $dashboard->name }} e confira a prévia antes de salvar os dados finais.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                @if ($importStatusLabel)
                    <x-badge :variant="$importStatusVariant">{{ $importStatusLabel }}</x-badge>
                @endif

                <x-badge :variant="$dashboard->status->badgeVariant()">
                    {{ $dashboard->status->label() }}
                </x-badge>
            </div>
        </div>
    </x-card>

    <x-card>
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            @foreach ([1 => 'Upload', 2 => 'Escolher aba', 3 => 'Prévia dos dados', 4 => 'Tipos e conversão'] as $number => $label)
                @php
                    $isDone = $step > $number || ($number === 2 && $importId && $step >= 3);
                    $isActive = $step === $number || ($number === 3 && count($columns) > 0 && $step < 4);
                @endphp

                <div class="flex items-center gap-3 rounded-2xl border {{ $isActive || $isDone ? 'border-blue-100 bg-blue-50' : 'border-slate-200 bg-white' }} p-4">
                    <div class="flex h-8 w-8 items-center justify-center rounded-full text-sm font-bold {{ $isDone || $isActive ? 'bg-seduc-primary text-white' : 'bg-slate-100 text-slate-500' }}">
                        {{ $number }}
                    </div>
                    <div>
                        <p class="text-sm font-bold text-slate-950">{{ $label }}</p>
                        <p class="text-xs text-slate-500">
                            @if ($number === 1)
                                Envie .xlsx ou .csv
                            @elseif ($number === 2)
                                Confirme a aba e o cabeçalho
                            @elseif ($number === 3)
                                Revise colunas e linhas
                            @else
                                Confirme e salve
                            @endif
                        </p>
                    </div>
                </div>
            @endforeach
        </div>
    </x-card>

    <form wire:submit="uploadFile" class="grid grid-cols-1 gap-5 xl:grid-cols-[minmax(0,1fr)_380px]">
        <x-card>
            <div
                x-data="{
                    dragging: false,
                    selectedName: @js($file?->getClientOriginalName() ?? $uploadedFilename),
                    browse() {
                        this.$refs.file.click();
                    },
                    selectFile(event) {
                        const files = event.target.files;

                        this.selectedName = files.length ? files[0].name : null;
                    },
                    dropFile(event) {
                        const files = event.dataTransfer.files;

                        if (! files.length) {
                            return;
                        }

                        this.$refs.file.files = files;
                        this.selectedName = files[0].name;
                        this.$refs.file.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                }"
                role="button"
                tabindex="0"
                data-testid="spreadsheet-dropzone"
                @click="browse()"
                @keydown.enter.prevent="browse()"
                @keydown.space.prevent="browse()"
                @dragover.prevent="dragging = true"
                @dragenter.prevent="dragging = true"
                @dragleave.prevent="dragging = false"
                @drop.prevent="dragging = false; dropFile($event)"
                class="block cursor-pointer rounded-[14px] border-2 border-dashed border-[#6EA8FE] bg-[#F9FBFF] px-6 py-10 text-center transition hover:bg-blue-50"
                :class="dragging || selectedName ? 'border-seduc-primary bg-blue-50 ring-4 ring-blue-100' : ''"
            >
                <input
                    x-ref="file"
                    id="spreadsheet-file"
                    type="file"
                    wire:model="file"
                    @change="selectFile($event)"
                    accept=".xlsx,.csv"
                    class="sr-only"
                >

                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-seduc-primary-soft text-seduc-primary">
                    <span x-show="! selectedName">
                        <x-icon name="cloud-upload" class="h-7 w-7" />
                    </span>
                    <span x-cloak x-show="selectedName">
                        <x-icon name="circle-check" class="h-7 w-7" />
                    </span>
                </div>
                <h3 class="mt-5 text-xl font-bold text-slate-950" x-text="selectedName ? 'Planilha selecionada' : 'Selecione ou arraste a planilha'">Selecione ou arraste a planilha</h3>
                <p x-show="! selectedName" class="mx-auto mt-2 max-w-2xl text-sm leading-6 text-slate-600">
                    Solte o arquivo aqui ou procure no computador. Use .xlsx ou .csv com até {{ $this->maxUploadMb }} MB.
                </p>
                <p x-cloak x-show="selectedName" class="mx-auto mt-2 max-w-2xl text-sm leading-6 text-slate-600">
                    Arquivo pronto para envio:
                    <span class="font-bold text-slate-950" x-text="selectedName"></span>
                </p>

                <div class="mt-5 inline-flex h-11 items-center justify-center gap-2 rounded-[10px] bg-seduc-primary px-[18px] text-sm font-semibold text-white shadow-seduc-button">
                    <x-icon name="file-spreadsheet" class="h-4 w-4" />
                    <span x-text="selectedName ? 'Trocar arquivo' : 'Procurar arquivo'">Procurar arquivo</span>
                </div>
            </div>

            @error('file')
                <p class="mt-3 text-sm font-semibold text-red-600">{{ $message }}</p>
            @enderror

            <div wire:loading wire:target="file" class="mt-3 rounded-2xl border border-blue-100 bg-blue-50 px-4 py-3 text-sm font-semibold text-seduc-primary">
                Preparando arquivo selecionado...
            </div>

            <div class="mt-4 rounded-2xl border border-slate-200 bg-white p-4">
                <p class="text-xs font-semibold text-slate-500">Arquivo selecionado</p>
                @if ($file)
                    <p class="mt-1 break-all text-sm font-bold text-slate-950">{{ $file->getClientOriginalName() }}</p>
                @elseif ($uploadedFilename)
                    <p class="mt-1 break-all text-sm font-bold text-slate-950">{{ $uploadedFilename }}</p>
                @else
                    <p class="mt-1 text-sm text-slate-500">Nenhum arquivo selecionado ainda.</p>
                @endif
            </div>

            <div class="mt-4 rounded-2xl border border-blue-100 bg-blue-50 p-4">
                <p class="text-[13px] font-semibold leading-5 text-slate-950">Intervalo da planilha</p>
                <div class="mt-3 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <div class="space-y-2">
                        <label for="headerStartCell" class="block text-xs font-semibold text-slate-600">Títulos começam em</label>
                        <input
                            id="headerStartCell"
                            type="text"
                            wire:model="headerStartCell"
                            placeholder="A1"
                            class="h-11 w-full rounded-[10px] border border-slate-300 bg-white px-3.5 text-sm font-semibold uppercase text-slate-950 placeholder:text-slate-400 transition focus:border-seduc-primary focus:outline-none focus:ring-4 focus:ring-blue-100"
                        >
                        @error('headerStartCell')
                            <p class="text-xs font-semibold text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <label for="dataEndCell" class="block text-xs font-semibold text-slate-600">Dados terminam em</label>
                        <input
                            id="dataEndCell"
                            type="text"
                            wire:model="dataEndCell"
                            placeholder="Opcional, ex.: A18"
                            class="h-11 w-full rounded-[10px] border border-slate-300 bg-white px-3.5 text-sm font-semibold uppercase text-slate-950 placeholder:text-slate-400 transition focus:border-seduc-primary focus:outline-none focus:ring-4 focus:ring-blue-100"
                        >
                        @error('dataEndCell')
                            <p class="text-xs font-semibold text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <label for="ignoredRowsInput" class="block text-xs font-semibold text-slate-600">Linhas para ignorar</label>
                        <input
                            id="ignoredRowsInput"
                            type="text"
                            wire:model="ignoredRowsInput"
                            placeholder="Opcional, ex.: 3"
                            class="h-11 w-full rounded-[10px] border border-slate-300 bg-white px-3.5 text-sm font-semibold text-slate-950 placeholder:text-slate-400 transition focus:border-seduc-primary focus:outline-none focus:ring-4 focus:ring-blue-100"
                        >
                        @error('ignoredRowsInput')
                            <p class="text-xs font-semibold text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <label for="excludedColumnsInput" class="block text-xs font-semibold text-slate-600">Colunas para ignorar</label>
                        <input
                            id="excludedColumnsInput"
                            type="text"
                            wire:model="excludedColumnsInput"
                            placeholder="Opcional, ex.: K, N"
                            class="h-11 w-full rounded-[10px] border border-slate-300 bg-white px-3.5 text-sm font-semibold uppercase text-slate-950 placeholder:text-slate-400 transition focus:border-seduc-primary focus:outline-none focus:ring-4 focus:ring-blue-100"
                        >
                        @error('excludedColumnsInput')
                            <p class="text-xs font-semibold text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <p class="mt-3 text-sm leading-6 text-slate-600">
                    Use <span class="font-semibold text-slate-950">A2</span> quando os títulos começarem na segunda linha, <span class="font-semibold text-slate-950">A18</span> para cortar somas no rodapé, <span class="font-semibold text-slate-950">3</span> para ignorar uma linha de observação e <span class="font-semibold text-slate-950">K, N</span> para remover colunas vazias ou auxiliares.
                </p>
            </div>
        </x-card>

        <div class="space-y-5">
            <x-card>
                <x-badge variant="info">Leitura inicial</x-badge>
                <h3 class="mt-4 text-lg font-bold text-slate-950">O que será feito agora</h3>
                <p class="mt-2 text-sm leading-6 text-slate-600">
                    O sistema salva o arquivo, identifica as abas, sugere a linha de cabeçalho e mostra uma prévia segura dos dados.
                </p>

                <div class="mt-4 space-y-2 rounded-2xl border border-blue-100 bg-blue-50 p-4 text-sm text-slate-600">
                    <p class="font-semibold text-slate-950">Conversão dos dados</p>
                    <p class="leading-6">Depois da prévia, confirme tipos simples como Dinheiro, Data e Opção/Categoria antes de gravar os dados.</p>
                </div>
            </x-card>

            <x-button type="submit" class="w-full" wire:loading.attr="disabled" wire:target="uploadFile,file">
                <x-icon name="upload" class="h-4 w-4" />
                <span wire:loading.remove wire:target="uploadFile">Enviar e ler planilha</span>
                <span wire:loading wire:target="uploadFile">Lendo planilha...</span>
            </x-button>
        </div>
    </form>

    @if ($importId)
        <div class="grid grid-cols-1 gap-5 lg:grid-cols-[360px_minmax(0,1fr)]">
            <x-card>
                <h3 class="text-lg font-bold text-slate-950">Escolher aba</h3>
                <p class="mt-1 text-sm leading-6 text-slate-600">
                    Confirme qual aba deve ser usada e onde estão os nomes das colunas.
                </p>

                <div class="mt-5 space-y-4">
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div class="space-y-2">
                            <label for="headerStartCellPreview" class="block text-[13px] font-semibold leading-5 text-slate-950">Títulos começam em</label>
                            <input
                                id="headerStartCellPreview"
                                type="text"
                                wire:model="headerStartCell"
                                placeholder="A1"
                                class="h-11 w-full rounded-[10px] border border-slate-300 bg-white px-3.5 text-sm font-semibold uppercase text-slate-950 placeholder:text-slate-400 transition focus:border-seduc-primary focus:outline-none focus:ring-4 focus:ring-blue-100"
                            >
                            @error('headerStartCell')
                                <p class="text-xs font-semibold text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="space-y-2">
                            <label for="dataEndCellPreview" class="block text-[13px] font-semibold leading-5 text-slate-950">Dados terminam em</label>
                            <input
                                id="dataEndCellPreview"
                                type="text"
                                wire:model="dataEndCell"
                                placeholder="Opcional"
                                class="h-11 w-full rounded-[10px] border border-slate-300 bg-white px-3.5 text-sm font-semibold uppercase text-slate-950 placeholder:text-slate-400 transition focus:border-seduc-primary focus:outline-none focus:ring-4 focus:ring-blue-100"
                            >
                            @error('dataEndCell')
                                <p class="text-xs font-semibold text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="space-y-2">
                            <label for="ignoredRowsInputPreview" class="block text-[13px] font-semibold leading-5 text-slate-950">Linhas ignoradas</label>
                            <input
                                id="ignoredRowsInputPreview"
                                type="text"
                                wire:model="ignoredRowsInput"
                                placeholder="Opcional"
                                class="h-11 w-full rounded-[10px] border border-slate-300 bg-white px-3.5 text-sm font-semibold text-slate-950 placeholder:text-slate-400 transition focus:border-seduc-primary focus:outline-none focus:ring-4 focus:ring-blue-100"
                            >
                            @error('ignoredRowsInput')
                                <p class="text-xs font-semibold text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="space-y-2">
                            <label for="excludedColumnsInputPreview" class="block text-[13px] font-semibold leading-5 text-slate-950">Colunas ignoradas</label>
                            <input
                                id="excludedColumnsInputPreview"
                                type="text"
                                wire:model="excludedColumnsInput"
                                placeholder="Opcional"
                                class="h-11 w-full rounded-[10px] border border-slate-300 bg-white px-3.5 text-sm font-semibold uppercase text-slate-950 placeholder:text-slate-400 transition focus:border-seduc-primary focus:outline-none focus:ring-4 focus:ring-blue-100"
                            >
                            @error('excludedColumnsInput')
                                <p class="text-xs font-semibold text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label for="selectedSheet" class="block text-[13px] font-semibold leading-5 text-slate-950">Aba da planilha</label>
                        <select
                            id="selectedSheet"
                            wire:model.live="selectedSheet"
                            class="h-11 w-full rounded-[10px] border border-slate-300 bg-white px-3.5 text-sm text-slate-950 transition focus:border-seduc-primary focus:outline-none focus:ring-4 focus:ring-blue-100"
                        >
                            @foreach ($sheets as $sheet)
                                <option value="{{ $sheet }}">{{ $sheet }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label for="headerRow" class="block text-[13px] font-semibold leading-5 text-slate-950">Linha com nomes das colunas</label>
                        <select
                            id="headerRow"
                            wire:model.live="headerRow"
                            class="h-11 w-full rounded-[10px] border border-slate-300 bg-white px-3.5 text-sm text-slate-950 transition focus:border-seduc-primary focus:outline-none focus:ring-4 focus:ring-blue-100"
                        >
                            @forelse ($possibleHeaderRows as $candidate)
                                <option value="{{ $candidate['row_number'] }}">
                                    Linha {{ $candidate['row_number'] }} - {{ implode(' | ', $candidate['preview']) }}
                                </option>
                            @empty
                                <option value="{{ $headerRow }}">Linha {{ $headerRow }}</option>
                            @endforelse
                        </select>
                    </div>

                    <button
                        type="button"
                        wire:click="loadPreview"
                        wire:loading.attr="disabled"
                        wire:target="loadPreview,selectedSheet,headerRow"
                        class="inline-flex h-11 w-full items-center justify-center gap-2 rounded-[10px] border border-blue-100 bg-blue-50 px-[18px] text-sm font-semibold text-seduc-primary transition hover:bg-blue-100"
                    >
                        <x-icon name="refresh-cw" class="h-4 w-4" />
                        Atualizar prévia
                    </button>
                </div>
            </x-card>

            <x-card>
                <div class="flex flex-col gap-2 md:flex-row md:items-start md:justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-slate-950">Colunas detectadas</h3>
                        <p class="mt-1 text-sm leading-6 text-slate-600">
                            Estes nomes serão usados apenas como referência visual nesta etapa.
                        </p>
                    </div>

                    <x-badge variant="purple">{{ count($columns) }} colunas</x-badge>
                </div>

                <div wire:loading wire:target="loadPreview,selectedSheet,headerRow" class="mt-4 rounded-2xl border border-blue-100 bg-blue-50 px-4 py-3 text-sm font-semibold text-seduc-primary">
                    Atualizando prévia...
                </div>

                <div class="mt-5 grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                    @forelse ($columns as $column)
                        <div class="rounded-2xl border border-slate-200 bg-white p-4">
                            <div class="flex items-center justify-between gap-3">
                                <p class="text-sm font-bold text-slate-950">{{ $column['name'] }}</p>
                                <span class="rounded-full bg-slate-100 px-2 py-1 text-xs font-bold text-slate-500">{{ $column['letter'] }}</span>
                            </div>
                            <p class="mt-2 text-xs font-semibold text-slate-500">Nome preparado: {{ $column['normalized_name'] }}</p>

                            <div class="mt-3 flex flex-wrap gap-2">
                                @forelse ($column['samples'] as $sample)
                                    <span class="max-w-full truncate rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">{{ $sample }}</span>
                                @empty
                                    <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-500">Sem exemplo</span>
                                @endforelse
                            </div>
                        </div>
                    @empty
                        <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-5 text-sm text-slate-500">
                            Nenhuma coluna foi detectada. Verifique a aba e a linha de cabeçalho.
                        </div>
                    @endforelse
                </div>
            </x-card>
        </div>

        @if ($columnMappings)
            <x-card>
                <div class="flex flex-col gap-2 md:flex-row md:items-start md:justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-slate-950">Confirmar tipos das colunas</h3>
                        <p class="mt-1 text-sm leading-6 text-slate-600">
                            Confira a sugestão do sistema e ajuste apenas o que precisar. Estes tipos serão usados para salvar os dados de forma padronizada.
                        </p>
                        @error('columnMappings')
                            <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <x-badge variant="info">{{ count($columnMappings) }} colunas</x-badge>
                </div>

                <div class="mt-5 grid gap-4 xl:grid-cols-2">
                    @foreach ($columnMappings as $mappingKey => $mapping)
                        <div wire:key="column-type-{{ $mappingKey }}" class="rounded-2xl border border-slate-200 bg-white p-4 shadow-[0_8px_24px_rgba(15,23,42,0.03)]">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                <div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <p class="text-sm font-bold text-slate-950">{{ $mapping['original_name'] }}</p>
                                        <span class="rounded-full bg-slate-100 px-2 py-1 text-xs font-bold text-slate-500">{{ $mapping['letter'] }}</span>
                                    </div>
                                    <p class="mt-1 text-xs font-semibold text-slate-500">Nome preparado: {{ $mapping['normalized_name'] }}</p>
                                </div>

                                <x-badge variant="purple">Sugestão: {{ $mapping['suggested_label'] }}</x-badge>
                            </div>

                            <div class="mt-3 flex flex-wrap gap-2">
                                @forelse ($mapping['samples'] as $sample)
                                    <span class="max-w-full truncate rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">{{ $sample }}</span>
                                @empty
                                    <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-500">Sem exemplo</span>
                                @endforelse
                            </div>

                            <div class="mt-4 grid gap-4 md:grid-cols-2">
                                <div class="space-y-2">
                                    <label for="columnType{{ $mappingKey }}" class="block text-[13px] font-semibold leading-5 text-slate-950">Tipo de informação</label>
                                    <select
                                        id="columnType{{ $mappingKey }}"
                                        wire:key="column-type-select-{{ $mappingKey }}"
                                        wire:model.live="columnMappings.{{ $mappingKey }}.type"
                                        class="h-11 w-full rounded-[10px] border border-slate-300 bg-white px-3.5 text-sm text-slate-950 transition focus:border-seduc-primary focus:outline-none focus:ring-4 focus:ring-blue-100"
                                    >
                                        @foreach ($this->typeOptions as $typeOption)
                                            <option value="{{ $typeOption['value'] }}">{{ $typeOption['label'] }}</option>
                                        @endforeach
                                    </select>
                                    @error("columnMappings.$mappingKey.type")
                                        <p class="text-xs font-semibold text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="space-y-2">
                                    <label for="friendlyName{{ $mappingKey }}" class="block text-[13px] font-semibold leading-5 text-slate-950">Nome amigável</label>
                                    <input
                                        id="friendlyName{{ $mappingKey }}"
                                        type="text"
                                        wire:key="column-friendly-name-{{ $mappingKey }}"
                                        wire:model="columnMappings.{{ $mappingKey }}.friendly_name"
                                        class="h-11 w-full rounded-[10px] border border-slate-300 bg-white px-3.5 text-sm text-slate-950 placeholder:text-slate-400 transition focus:border-seduc-primary focus:outline-none focus:ring-4 focus:ring-blue-100"
                                    >
                                    @error("columnMappings.$mappingKey.friendly_name")
                                        <p class="text-xs font-semibold text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div class="mt-4 grid gap-3 sm:grid-cols-3">
                                <label for="filterable{{ $mappingKey }}" class="flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-semibold text-slate-700">
                                    <input
                                        id="filterable{{ $mappingKey }}"
                                        type="checkbox"
                                        wire:key="column-filterable-{{ $mappingKey }}"
                                        wire:model.live="columnMappings.{{ $mappingKey }}.is_filterable"
                                        class="h-4 w-4 rounded border-slate-300 text-seduc-primary focus:ring-blue-100"
                                    >
                                    Usar em filtros
                                </label>

                                <label for="chartable{{ $mappingKey }}" class="flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-semibold text-slate-700">
                                    <input
                                        id="chartable{{ $mappingKey }}"
                                        type="checkbox"
                                        wire:key="column-chartable-{{ $mappingKey }}"
                                        wire:model.live="columnMappings.{{ $mappingKey }}.is_chartable"
                                        class="h-4 w-4 rounded border-slate-300 text-seduc-primary focus:ring-blue-100"
                                    >
                                    Usar em gráficos
                                </label>

                                <label for="required{{ $mappingKey }}" class="flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-semibold text-slate-700">
                                    <input
                                        id="required{{ $mappingKey }}"
                                        type="checkbox"
                                        wire:key="column-required-{{ $mappingKey }}"
                                        wire:model.live="columnMappings.{{ $mappingKey }}.is_required"
                                        class="h-4 w-4 rounded border-slate-300 text-seduc-primary focus:ring-blue-100"
                                    >
                                    Obrigatório
                                </label>
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-card>
        @endif

        @if ($conversionErrors)
            <x-card padding="p-0">
                <div class="border-b border-red-100 bg-red-50 p-5">
                    <div class="flex flex-col gap-2 md:flex-row md:items-start md:justify-between">
                        <div>
                            <h3 class="text-lg font-bold text-red-800">Inconsistências encontradas</h3>
                            <p class="mt-1 text-sm leading-6 text-red-700">
                                Corrija os valores abaixo ou marque para ignorar o valor desta célula. Nada será salvo enquanto houver inconsistências.
                            </p>
                        </div>

                        <x-badge variant="danger">{{ count($conversionErrors) }} ajustes</x-badge>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="whitespace-nowrap px-4 py-3 text-left text-xs font-bold text-slate-600">Linha</th>
                                <th class="min-w-48 px-4 py-3 text-left text-xs font-bold text-slate-600">Coluna</th>
                                <th class="min-w-48 px-4 py-3 text-left text-xs font-bold text-slate-600">Valor original</th>
                                <th class="min-w-64 px-4 py-3 text-left text-xs font-bold text-slate-600">Correção</th>
                                <th class="min-w-48 px-4 py-3 text-left text-xs font-bold text-slate-600">Erro</th>
                                <th class="whitespace-nowrap px-4 py-3 text-left text-xs font-bold text-slate-600">Ignorar</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @foreach ($conversionErrors as $error)
                                <tr wire:key="conversion-error-{{ $error['id'] }}" class="transition hover:bg-slate-50">
                                    <td class="whitespace-nowrap px-4 py-3 text-xs font-semibold text-slate-500">{{ $error['row_number'] }}</td>
                                    <td class="px-4 py-3 text-xs font-semibold text-slate-800">{{ $error['column_name'] }}</td>
                                    <td class="px-4 py-3 text-xs text-slate-700">{{ $error['value'] === '' ? '-' : $error['value'] }}</td>
                                    <td class="px-4 py-3">
                                        <input
                                            type="text"
                                            wire:model="corrections.{{ $error['id'] }}"
                                            class="h-10 w-full rounded-[10px] border border-slate-300 bg-white px-3 text-xs font-semibold text-slate-950 placeholder:text-slate-400 transition focus:border-seduc-primary focus:outline-none focus:ring-4 focus:ring-blue-100"
                                        >
                                    </td>
                                    <td class="px-4 py-3 text-xs text-red-700">{{ $error['error'] }}</td>
                                    <td class="px-4 py-3">
                                        <label class="inline-flex items-center gap-2 text-xs font-semibold text-slate-700">
                                            <input type="checkbox" wire:model="ignoredCells.{{ $error['id'] }}" class="h-4 w-4 rounded border-slate-300 text-seduc-primary focus:ring-blue-100">
                                            Ignorar valor
                                        </label>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-card>
        @endif

        @if ($columnMappings)
            <div class="flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-[0_8px_24px_rgba(15,23,42,0.04)] md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-sm font-bold text-slate-950">Salvar dados convertidos</p>
                    <p class="mt-1 text-sm text-slate-600">
                        As colunas ignoradas ficam fora do salvamento. As demais serão gravadas com os tipos confirmados.
                    </p>
                </div>

                <x-button type="button" wire:click="saveConvertedData" wire:loading.attr="disabled" wire:target="saveConvertedData" class="w-full md:w-auto">
                    <x-icon name="save" class="h-4 w-4" />
                    <span wire:loading.remove wire:target="saveConvertedData">Converter e salvar dados</span>
                    <span wire:loading wire:target="saveConvertedData">Convertendo...</span>
                </x-button>
            </div>
        @endif

        <x-card padding="p-0">
            <div class="flex flex-col gap-2 border-b border-slate-200 p-5 md:flex-row md:items-start md:justify-between">
                <div>
                    <h3 class="text-lg font-bold text-slate-950">Prévia dos dados</h3>
                    <p class="mt-1 text-sm leading-6 text-slate-600">
                        Mostrando até {{ config('seduc-bi.imports.preview_rows', 20) }} linhas para conferência{{ $dataEndCell ? ', limitadas até '.$dataEndCell : '' }}. Os dados finais ainda não foram salvos.
                    </p>

                    @if ($ignoredRows || $excludedColumns)
                        <p class="mt-2 text-xs font-semibold text-slate-500">
                            @if ($ignoredRows)
                                Linhas ignoradas: {{ implode(', ', $ignoredRows) }}.
                            @endif
                            @if ($excludedColumns)
                                Colunas ignoradas: {{ implode(', ', $excludedColumns) }}.
                            @endif
                        </p>
                    @endif
                </div>

                <x-badge variant="info">{{ count($previewRows) }} linhas</x-badge>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="whitespace-nowrap px-4 py-3 text-left text-xs font-bold text-slate-600">Linha</th>
                            @foreach ($columns as $column)
                                <th class="min-w-40 px-4 py-3 text-left text-xs font-bold text-slate-600">
                                    <span class="block truncate">{{ $column['name'] }}</span>
                                    <span class="block text-[11px] font-semibold text-slate-400">{{ $column['normalized_name'] }}</span>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($previewRows as $row)
                            <tr class="transition hover:bg-slate-50">
                                <td class="whitespace-nowrap px-4 py-3 text-xs font-semibold text-slate-500">{{ $row['row_number'] }}</td>
                                @foreach ($columns as $column)
                                    <td class="max-w-72 px-4 py-3 text-xs text-slate-700">
                                        <span class="block truncate">{{ $row['values'][$column['index']] ?? '-' }}</span>
                                    </td>
                                @endforeach
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ max(count($columns) + 1, 1) }}" class="px-4 py-10 text-center text-sm text-slate-500">
                                    Nenhuma linha de dados encontrada após o cabeçalho selecionado.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-card>
    @endif
</div>

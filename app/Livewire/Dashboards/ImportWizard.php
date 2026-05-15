<?php

namespace App\Livewire\Dashboards;

use App\Enums\DashboardColumnType;
use App\Enums\DashboardImportStatus;
use App\Enums\DashboardStatus;
use App\Models\Dashboard;
use App\Models\DashboardColumn;
use App\Models\DashboardImport;
use App\Models\DashboardRow;
use App\Services\ColumnTypeDetectorService;
use App\Services\ColumnValueConverterService;
use App\Services\SpreadsheetReaderService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use Livewire\Component;
use Livewire\WithFileUploads;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use Throwable;

class ImportWizard extends Component
{
    use WithFileUploads;

    public Dashboard $dashboard;

    public $file = null;

    public int $step = 1;

    public ?int $importId = null;

    public ?string $uploadedFilename = null;

    public ?string $selectedSheet = null;

    public int $headerRow = 1;

    public string $headerStartCell = 'A1';

    public int $headerStartColumnIndex = 0;

    public ?string $dataEndCell = null;

    public ?int $dataEndRow = null;

    public ?string $ignoredRowsInput = null;

    public array $ignoredRows = [];

    public ?string $excludedColumnsInput = null;

    public array $excludedColumns = [];

    public array $excludedColumnIndexes = [];

    public array $sheets = [];

    public array $possibleHeaderRows = [];

    public array $columns = [];

    public array $previewRows = [];

    public array $columnSamples = [];

    public array $columnMappings = [];

    public array $conversionErrors = [];

    public array $corrections = [];

    public array $ignoredCells = [];

    public ?string $importStatus = null;

    public ?string $importStatusLabel = null;

    public ?string $importStatusVariant = null;

    public function mount(Dashboard $dashboard): void
    {
        abort_unless($dashboard->canBeAccessedBy(Auth::user()), 403, 'Você não pode alimentar este dashboard.');

        $this->dashboard = $dashboard->load(['sector', 'user', 'latestImport']);
    }

    public function uploadFile(): void
    {
        $this->validateUpload();
        $this->syncHeaderStartFromCell();
        $this->syncDataEndFromCell();
        $this->syncIgnoredRowsFromInput();
        $this->syncExcludedColumnsFromInput();

        $this->resetImportState();
        $this->syncHeaderStartFromCell();
        $this->syncDataEndFromCell();
        $this->syncIgnoredRowsFromInput();
        $this->syncExcludedColumnsFromInput();

        $extension = Str::lower($this->file->getClientOriginalExtension());
        $originalFilename = $this->file->getClientOriginalName();
        $storedFilename = Str::uuid().'.'.$extension;
        $filePath = $this->file->storeAs("dashboard-imports/{$this->dashboard->id}", $storedFilename, 'local');
        $reader = app(SpreadsheetReaderService::class);

        $import = DashboardImport::query()->create([
            'dashboard_id' => $this->dashboard->id,
            'original_filename' => $originalFilename,
            'file_path' => $filePath,
            'header_start_cell' => $this->headerStartCell,
            'data_end_cell' => $this->dataEndCell,
            'ignored_rows' => $this->ignoredRows,
            'excluded_columns' => $this->excludedColumns,
            'status' => DashboardImportStatus::Uploaded,
        ]);

        $this->importId = $import->id;
        $this->uploadedFilename = $originalFilename;
        $this->syncStatus($import->status);

        try {
            $import->update(['status' => DashboardImportStatus::Reading]);
            $this->syncStatus(DashboardImportStatus::Reading);

            $absolutePath = Storage::disk('local')->path($filePath);
            $this->sheets = $reader->sheetNames($absolutePath);
            $this->selectedSheet = $this->sheets[0] ?? null;
            $this->loadPreview($reader);
        } catch (Throwable $exception) {
            report($exception);

            $import->update(['status' => DashboardImportStatus::Error]);
            $this->syncStatus(DashboardImportStatus::Error);

            throw ValidationException::withMessages([
                'file' => 'Não foi possível ler esta planilha. Confira o arquivo e tente novamente.',
            ]);
        }
    }

    public function loadPreview(?SpreadsheetReaderService $reader = null): void
    {
        $import = $this->currentImport();

        if (! $import) {
            return;
        }

        $reader ??= app(SpreadsheetReaderService::class);
        $this->syncHeaderStartFromCell();
        $this->syncDataEndFromCell();
        $this->syncIgnoredRowsFromInput();
        $this->syncExcludedColumnsFromInput();

        $analysis = $reader->preview(
            Storage::disk('local')->path($import->file_path),
            $this->selectedSheet,
            $this->headerRow,
            (int) config('seduc-bi.imports.preview_rows', 20),
            $this->headerStartColumnIndex,
            $this->dataEndRow,
            $this->ignoredRows,
            $this->excludedColumnIndexes
        );

        $this->selectedSheet = $analysis['sheet_name'];
        $this->headerRow = $analysis['header_row'];
        $this->possibleHeaderRows = $analysis['possible_header_rows'];
        $this->columns = $analysis['columns'];
        $this->previewRows = $analysis['rows'];
        $this->columnSamples = collect($this->columns)
            ->mapWithKeys(fn (array $column) => [$column['normalized_name'] => $column['samples']])
            ->all();
        $this->prepareColumnMappings();
        $this->conversionErrors = [];
        $this->corrections = [];
        $this->ignoredCells = [];
        $this->step = 4;

        $import->update([
            'sheet_name' => $this->selectedSheet,
            'header_start_cell' => $this->headerStartCell,
            'data_end_cell' => $this->dataEndCell,
            'ignored_rows' => $this->ignoredRows,
            'excluded_columns' => $this->excludedColumns,
            'status' => DashboardImportStatus::Mapped,
        ]);

        $this->syncStatus(DashboardImportStatus::Mapped);
    }

    public function updatedSelectedSheet(): void
    {
        if ($this->importId) {
            $this->syncHeaderStartFromCell();
            $this->loadPreview();
        }
    }

    public function updatedHeaderRow(): void
    {
        if ($this->importId) {
            $this->headerRow = max(1, (int) $this->headerRow);
            $this->headerStartCell = Coordinate::stringFromColumnIndex($this->headerStartColumnIndex + 1).$this->headerRow;
            $this->loadPreview();
        }
    }

    public function getMaxUploadMbProperty(): int
    {
        return (int) ceil(((int) config('seduc-bi.imports.max_upload_kb', 10240)) / 1024);
    }

    public function getTypeOptionsProperty(): array
    {
        return DashboardColumnType::options();
    }

    public function saveConvertedData(
        ?SpreadsheetReaderService $reader = null,
        ?ColumnValueConverterService $converter = null
    ): void {
        $import = $this->currentImport();

        if (! $import) {
            return;
        }

        $this->validate([
            'columnMappings' => ['required', 'array', 'min:1'],
            'columnMappings.*.type' => [
                'required',
                Rule::in(array_column(DashboardColumnType::options(), 'value')),
            ],
            'columnMappings.*.friendly_name' => ['nullable', 'string', 'max:120'],
        ], [
            'columnMappings.required' => 'Confirme os tipos das colunas antes de salvar.',
            'columnMappings.*.type.required' => 'Escolha o tipo de cada coluna.',
            'columnMappings.*.type.in' => 'Escolha um tipo de informação válido.',
            'columnMappings.*.friendly_name.max' => 'Use um nome amigável menor para a coluna.',
        ]);

        $reader ??= app(SpreadsheetReaderService::class);
        $converter ??= app(ColumnValueConverterService::class);

        $this->syncHeaderStartFromCell();
        $this->syncDataEndFromCell();
        $this->syncIgnoredRowsFromInput();
        $this->syncExcludedColumnsFromInput();

        $analysis = $reader->readData(
            Storage::disk('local')->path($import->file_path),
            $this->selectedSheet,
            $this->headerRow,
            $this->headerStartColumnIndex,
            $this->dataEndRow,
            $this->ignoredRows,
            $this->excludedColumnIndexes
        );

        $this->selectedSheet = $analysis['sheet_name'];
        $this->columns = $analysis['columns'];
        $this->previewRows = array_slice($analysis['rows'], 0, (int) config('seduc-bi.imports.preview_rows', 20));
        $this->columnSamples = collect($this->columns)
            ->mapWithKeys(fn (array $column) => [$column['normalized_name'] => $column['samples']])
            ->all();
        $this->prepareColumnMappings();
        $activeMappings = $this->activeColumnMappings();

        if ($activeMappings === []) {
            throw ValidationException::withMessages([
                'columnMappings' => 'Mantenha pelo menos uma coluna para salvar os dados.',
            ]);
        }

        [$convertedRows, $errors] = $this->convertRows($analysis['rows'], $converter);

        if ($errors !== []) {
            $this->conversionErrors = $errors;
            $this->step = 4;

            return;
        }

        DB::transaction(function () use ($convertedRows, $import, $activeMappings): void {
            DashboardColumn::query()
                ->where('dashboard_id', $this->dashboard->id)
                ->delete();

            DashboardRow::query()
                ->where('dashboard_id', $this->dashboard->id)
                ->delete();

            foreach ($activeMappings as $position => $mapping) {
                DashboardColumn::query()->create([
                    'dashboard_id' => $this->dashboard->id,
                    'original_name' => $mapping['original_name'],
                    'normalized_name' => $mapping['normalized_name'],
                    'friendly_name' => $this->cleanNullableText($mapping['friendly_name'] ?? null),
                    'type' => $mapping['type'],
                    'is_filterable' => (bool) ($mapping['is_filterable'] ?? false),
                    'is_chartable' => (bool) ($mapping['is_chartable'] ?? true),
                    'is_required' => (bool) ($mapping['is_required'] ?? false),
                    'position' => $position + 1,
                ]);
            }

            foreach ($convertedRows as $row) {
                DashboardRow::query()->create([
                    'dashboard_id' => $this->dashboard->id,
                    'row_hash' => sha1(json_encode($row['data'], JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION) ?: ''),
                    'data_json' => $row['data'],
                    'created_by' => Auth::id(),
                    'updated_by' => null,
                ]);
            }

            $this->dashboard->update(['status' => DashboardStatus::Ready]);

            $import->update([
                'sheet_name' => $this->selectedSheet,
                'header_start_cell' => $this->headerStartCell,
                'data_end_cell' => $this->dataEndCell,
                'ignored_rows' => $this->ignoredRows,
                'excluded_columns' => $this->excludedColumns,
                'status' => DashboardImportStatus::Converted,
                'imported_at' => now(),
            ]);
        });

        $this->dashboard->refresh();
        $this->conversionErrors = [];
        $this->corrections = [];
        $this->ignoredCells = [];
        $this->syncStatus(DashboardImportStatus::Converted);

        session()->flash('status', 'Dados convertidos e salvos com sucesso.');
    }

    public function render()
    {
        return view('livewire.dashboards.import-wizard')
            ->layout('layouts.app')
            ->title('Importar Planilha | SEDUC BI');
    }

    private function validateUpload(): void
    {
        $maxKb = (int) config('seduc-bi.imports.max_upload_kb', 10240);

        $this->validate([
            'headerStartCell' => [
                'required',
                'string',
                'max:8',
                'regex:/^[A-Za-z]{1,3}[1-9][0-9]{0,4}$/',
            ],
            'dataEndCell' => [
                'nullable',
                'string',
                'max:8',
                'regex:/^[A-Za-z]{1,3}[1-9][0-9]{0,4}$/',
            ],
            'ignoredRowsInput' => ['nullable', 'string', 'max:120'],
            'excludedColumnsInput' => ['nullable', 'string', 'max:120'],
            'file' => [
                'required',
                'file',
                'max:'.$maxKb,
                function (string $attribute, mixed $value, $fail): void {
                    $extension = Str::lower($value?->getClientOriginalExtension() ?? '');

                    if (! in_array($extension, ['xlsx', 'csv'], true)) {
                        $fail('Envie uma planilha no formato .xlsx ou .csv.');
                    }
                },
            ],
        ], [
            'headerStartCell.required' => 'Informe onde começam os títulos da planilha.',
            'headerStartCell.regex' => 'Informe uma célula válida, como A1 ou A2.',
            'dataEndCell.regex' => 'Informe uma célula válida, como A18, ou deixe em branco.',
            'ignoredRowsInput.max' => 'Informe menos linhas para ignorar.',
            'excludedColumnsInput.max' => 'Informe menos colunas para ignorar.',
            'file.required' => 'Selecione uma planilha para importar.',
            'file.file' => 'Selecione um arquivo válido.',
            'file.max' => 'A planilha deve ter no máximo '.$this->maxUploadMb.' MB.',
        ]);
    }

    private function currentImport(): ?DashboardImport
    {
        if (! $this->importId) {
            return null;
        }

        return DashboardImport::query()
            ->where('dashboard_id', $this->dashboard->id)
            ->find($this->importId);
    }

    private function syncStatus(DashboardImportStatus $status): void
    {
        $this->importStatus = $status->value;
        $this->importStatusLabel = $status->label();
        $this->importStatusVariant = $status->badgeVariant();
    }

    private function resetImportState(): void
    {
        $this->step = 1;
        $this->importId = null;
        $this->uploadedFilename = null;
        $this->selectedSheet = null;
        $this->sheets = [];
        $this->possibleHeaderRows = [];
        $this->columns = [];
        $this->previewRows = [];
        $this->columnSamples = [];
        $this->columnMappings = [];
        $this->conversionErrors = [];
        $this->corrections = [];
        $this->ignoredCells = [];
        $this->importStatus = null;
        $this->importStatusLabel = null;
        $this->importStatusVariant = null;
    }

    private function prepareColumnMappings(): void
    {
        $detector = app(ColumnTypeDetectorService::class);
        $previousMappings = collect($this->columnMappings)
            ->keyBy('normalized_name')
            ->all();
        $mappings = [];

        foreach ($this->columns as $position => $column) {
            $previous = $previousMappings[$column['normalized_name']] ?? null;
            $suggestedType = $detector->suggest($column['name'], $column['samples']);
            $selectedType = $previous['type'] ?? $suggestedType->value;
            $type = DashboardColumnType::tryFrom($selectedType) ?? $suggestedType;

            $mappings[] = [
                'index' => $column['index'],
                'letter' => $column['letter'],
                'original_name' => $column['name'],
                'normalized_name' => $column['normalized_name'],
                'samples' => $column['samples'],
                'suggested_type' => $suggestedType->value,
                'suggested_label' => $suggestedType->label(),
                'type' => $type->value,
                'friendly_name' => $previous['friendly_name'] ?? $column['name'],
                'is_filterable' => $previous['is_filterable'] ?? $type->isDimensional(),
                'is_chartable' => $previous['is_chartable'] ?? ($type !== DashboardColumnType::Ignore && $type !== DashboardColumnType::LongText),
                'is_required' => $previous['is_required'] ?? false,
                'position' => $position + 1,
            ];
        }

        $this->columnMappings = $mappings;
    }

    /**
     * @param  array<int, array{row_number: int, values: array<int, string>}>  $rows
     * @return array{0: array<int, array{row_number: int, data: array<string, mixed>}>, 1: array<int, array{id: string, row_number: int, column_name: string, normalized_name: string, value: string, error: string}>}
     */
    private function convertRows(array $rows, ColumnValueConverterService $converter): array
    {
        $convertedRows = [];
        $errors = [];
        $mappings = $this->activeColumnMappings();

        foreach ($rows as $row) {
            $converted = [];

            foreach ($mappings as $mapping) {
                $value = $row['values'][$mapping['index']] ?? '';
                $errorId = $this->conversionErrorId((int) $row['row_number'], $mapping['normalized_name']);

                if ((bool) ($this->ignoredCells[$errorId] ?? false)) {
                    $converted[$mapping['normalized_name']] = null;

                    continue;
                }

                $valueToConvert = array_key_exists($errorId, $this->corrections)
                    ? $this->corrections[$errorId]
                    : $value;

                try {
                    $converted[$mapping['normalized_name']] = $converter->convert($valueToConvert, $mapping['type']);
                } catch (InvalidArgumentException $exception) {
                    $this->corrections[$errorId] ??= $value;

                    $errors[] = [
                        'id' => $errorId,
                        'row_number' => (int) $row['row_number'],
                        'column_name' => $this->cleanNullableText($mapping['friendly_name'] ?? null)
                            ?: $mapping['original_name'],
                        'normalized_name' => $mapping['normalized_name'],
                        'value' => $value,
                        'error' => $exception->getMessage(),
                    ];
                }
            }

            $convertedRows[] = [
                'row_number' => (int) $row['row_number'],
                'data' => $converted,
            ];
        }

        return [$convertedRows, $errors];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function activeColumnMappings(): array
    {
        return array_values(array_filter(
            $this->columnMappings,
            fn (array $mapping) => ($mapping['type'] ?? null) !== DashboardColumnType::Ignore->value
        ));
    }

    private function conversionErrorId(int $rowNumber, string $normalizedName): string
    {
        return 'linha_'.$rowNumber.'_'.$normalizedName;
    }

    private function cleanNullableText(?string $value): ?string
    {
        $value = Str::squish((string) $value);

        return $value === '' ? null : $value;
    }

    private function syncHeaderStartFromCell(): void
    {
        $cell = Str::upper(trim($this->headerStartCell));

        if (! preg_match('/^[A-Z]{1,3}[1-9][0-9]{0,4}$/', $cell)) {
            throw ValidationException::withMessages([
                'headerStartCell' => 'Informe uma célula válida, como A1 ou A2.',
            ]);
        }

        [$column, $row] = Coordinate::coordinateFromString($cell);

        $this->headerRow = (int) $row;
        $this->headerStartColumnIndex = Coordinate::columnIndexFromString($column) - 1;
        $this->headerStartCell = $column.$this->headerRow;
    }

    private function syncDataEndFromCell(): void
    {
        $cell = Str::upper(trim((string) $this->dataEndCell));

        if ($cell === '') {
            $this->dataEndCell = null;
            $this->dataEndRow = null;

            return;
        }

        if (! preg_match('/^[A-Z]{1,3}[1-9][0-9]{0,4}$/', $cell)) {
            throw ValidationException::withMessages([
                'dataEndCell' => 'Informe uma célula válida, como A18, ou deixe em branco.',
            ]);
        }

        [$column, $row] = Coordinate::coordinateFromString($cell);

        $this->dataEndRow = (int) $row;
        $this->dataEndCell = $column.$this->dataEndRow;

        if ($this->dataEndRow <= $this->headerRow) {
            throw ValidationException::withMessages([
                'dataEndCell' => 'A linha final precisa ficar depois da linha dos títulos.',
            ]);
        }
    }

    private function syncIgnoredRowsFromInput(): void
    {
        $input = trim((string) $this->ignoredRowsInput);

        if ($input === '') {
            $this->ignoredRows = [];
            $this->ignoredRowsInput = null;

            return;
        }

        $rows = [];

        foreach (preg_split('/[,;]+/', $input) ?: [] as $part) {
            $part = trim($part);

            if ($part === '') {
                continue;
            }

            if (preg_match('/^[1-9][0-9]*$/', $part)) {
                $rows[] = (int) $part;

                continue;
            }

            if (preg_match('/^([1-9][0-9]*)\s*-\s*([1-9][0-9]*)$/', $part, $matches)) {
                $start = (int) $matches[1];
                $end = (int) $matches[2];

                if ($end < $start) {
                    [$start, $end] = [$end, $start];
                }

                $rows = array_merge($rows, range($start, $end));

                continue;
            }

            throw ValidationException::withMessages([
                'ignoredRowsInput' => 'Informe linhas válidas, como 3, 19 ou 3-5.',
            ]);
        }

        $rows = array_values(array_unique($rows));
        sort($rows);

        $this->ignoredRows = $rows;
        $this->ignoredRowsInput = implode(', ', $rows);
    }

    private function syncExcludedColumnsFromInput(): void
    {
        $input = Str::upper(trim((string) $this->excludedColumnsInput));

        if ($input === '') {
            $this->excludedColumns = [];
            $this->excludedColumnIndexes = [];
            $this->excludedColumnsInput = null;

            return;
        }

        $indexes = [];

        foreach (preg_split('/[,;]+/', $input) ?: [] as $part) {
            $part = trim($part);

            if ($part === '') {
                continue;
            }

            if (preg_match('/^[A-Z]{1,3}$/', $part)) {
                $indexes[] = Coordinate::columnIndexFromString($part) - 1;

                continue;
            }

            if (preg_match('/^([A-Z]{1,3})\s*[:-]\s*([A-Z]{1,3})$/', $part, $matches)) {
                $start = Coordinate::columnIndexFromString($matches[1]) - 1;
                $end = Coordinate::columnIndexFromString($matches[2]) - 1;

                if ($end < $start) {
                    [$start, $end] = [$end, $start];
                }

                $indexes = array_merge($indexes, range($start, $end));

                continue;
            }

            throw ValidationException::withMessages([
                'excludedColumnsInput' => 'Informe colunas válidas, como K, N ou B:D.',
            ]);
        }

        $indexes = array_values(array_unique($indexes));
        sort($indexes);

        $this->excludedColumnIndexes = $indexes;
        $this->excludedColumns = array_map(
            fn (int $index) => Coordinate::stringFromColumnIndex($index + 1),
            $indexes
        );
        $this->excludedColumnsInput = implode(', ', $this->excludedColumns);
    }
}

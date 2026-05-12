<?php

namespace App\Livewire\Dashboards;

use App\Enums\DashboardImportStatus;
use App\Models\Dashboard;
use App\Models\DashboardImport;
use App\Services\SpreadsheetReaderService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
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

    public array $sheets = [];

    public array $possibleHeaderRows = [];

    public array $columns = [];

    public array $previewRows = [];

    public array $columnSamples = [];

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

        $this->resetImportState();
        $this->syncHeaderStartFromCell();
        $this->syncDataEndFromCell();

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

        $analysis = $reader->preview(
            Storage::disk('local')->path($import->file_path),
            $this->selectedSheet,
            $this->headerRow,
            (int) config('seduc-bi.imports.preview_rows', 20),
            $this->headerStartColumnIndex,
            $this->dataEndRow
        );

        $this->selectedSheet = $analysis['sheet_name'];
        $this->headerRow = $analysis['header_row'];
        $this->possibleHeaderRows = $analysis['possible_header_rows'];
        $this->columns = $analysis['columns'];
        $this->previewRows = $analysis['rows'];
        $this->columnSamples = collect($this->columns)
            ->mapWithKeys(fn (array $column) => [$column['normalized_name'] => $column['samples']])
            ->all();
        $this->step = 3;

        $import->update([
            'sheet_name' => $this->selectedSheet,
            'header_start_cell' => $this->headerStartCell,
            'data_end_cell' => $this->dataEndCell,
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
        $this->importStatus = null;
        $this->importStatusLabel = null;
        $this->importStatusVariant = null;
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
}

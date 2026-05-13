<?php

namespace App\Services;

use DateInterval;
use DateTimeInterface;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Reader\IReader;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SpreadsheetReaderService
{
    /**
     * @return array<int, string>
     */
    public function sheetNames(string $absolutePath): array
    {
        $reader = $this->readerFor($absolutePath);

        return array_values($reader->listWorksheetNames($absolutePath));
    }

    /**
     * @return array{
     *     sheet_name: string,
     *     header_row: int,
     *     possible_header_rows: array<int, array{row_number: int, filled_cells: int, preview: array<int, string>}>,
     *     columns: array<int, array{index: int, letter: string, name: string, normalized_name: string, samples: array<int, string>}>,
     *     rows: array<int, array{row_number: int, values: array<int, string>}>
     * }
     */
    public function preview(
        string $absolutePath,
        ?string $sheetName = null,
        int $headerRow = 1,
        int $previewRows = 20,
        int $startColumnIndex = 0,
        ?int $endRow = null,
        array $ignoredRows = [],
        array $excludedColumnIndexes = []
    ): array {
        $reader = $this->readerFor($absolutePath);
        $reader->setReadDataOnly(true);
        $reader->setReadEmptyCells(true);

        if ($sheetName) {
            $reader->setLoadSheetsOnly($sheetName);
        }

        $spreadsheet = $reader->load($absolutePath);
        $worksheet = $sheetName
            ? $spreadsheet->getSheetByName($sheetName) ?? $spreadsheet->getActiveSheet()
            : $spreadsheet->getActiveSheet();

        $startColumnIndex = max(0, $startColumnIndex);
        $readLimit = max($headerRow + $previewRows + 10, 40);

        if ($endRow !== null) {
            $readLimit = min($readLimit, max(1, $endRow));
        }

        $ignoredRows = array_values(array_unique(array_map('intval', $ignoredRows)));
        $excludedColumnIndexes = array_values(array_unique(array_map('intval', $excludedColumnIndexes)));

        $read = $this->readRows($worksheet, $readLimit, $startColumnIndex, $excludedColumnIndexes);
        $rows = $read['rows'];
        $columnIndexes = $read['column_indexes'];
        $possibleHeaderRows = $this->possibleHeaderRows(array_diff_key($rows, array_flip($ignoredRows)));
        $headerRow = max(1, min($headerRow, max(array_keys($rows) ?: [1])));
        $headerValues = $rows[$headerRow] ?? [];
        $dataRows = $this->previewRows($rows, $headerRow, $previewRows, $ignoredRows);
        $columns = $this->columns($headerValues, $dataRows, $columnIndexes);

        $spreadsheet->disconnectWorksheets();

        return [
            'sheet_name' => $worksheet->getTitle(),
            'header_row' => $headerRow,
            'possible_header_rows' => $possibleHeaderRows,
            'columns' => $columns,
            'rows' => $dataRows,
        ];
    }

    private function readerFor(string $absolutePath): IReader
    {
        $reader = IOFactory::createReaderForFile($absolutePath, [
            IOFactory::READER_XLSX,
            IOFactory::READER_CSV,
        ]);

        if ($reader instanceof Csv) {
            $reader->setTestAutoDetect(true);
            $reader->setDelimiter($this->detectCsvDelimiter($absolutePath));
        }

        return $reader;
    }

    private function detectCsvDelimiter(string $absolutePath): string
    {
        $handle = fopen($absolutePath, 'r');

        if (! $handle) {
            return ',';
        }

        $scores = [
            ',' => 0,
            ';' => 0,
            "\t" => 0,
            '|' => 0,
        ];
        $checkedLines = 0;

        while (($line = fgets($handle)) !== false && $checkedLines < 10) {
            $line = trim($line);

            if ($line === '') {
                continue;
            }

            foreach (array_keys($scores) as $delimiter) {
                $scores[$delimiter] += max(count(str_getcsv($line, $delimiter)) - 1, 0);
            }

            $checkedLines++;
        }

        fclose($handle);

        arsort($scores);

        return array_key_first($scores) ?: ',';
    }

    /**
     * @return array{rows: array<int, array<int, string>>, column_indexes: array<int, int>}
     */
    private function readRows(Worksheet $worksheet, int $limit, int $startColumnIndex, array $excludedColumnIndexes): array
    {
        $highestDataRow = min($worksheet->getHighestDataRow(), $limit);
        $highestDataColumn = $worksheet->getHighestDataColumn();
        $highestDataColumnIndex = Coordinate::columnIndexFromString($highestDataColumn);

        if ($highestDataRow < 1 || $startColumnIndex + 1 > $highestDataColumnIndex) {
            return ['rows' => [], 'column_indexes' => []];
        }

        $excludedColumnIndexes = array_flip($excludedColumnIndexes);
        $columnIndexes = [];

        for ($columnIndex = $startColumnIndex; $columnIndex < $highestDataColumnIndex; $columnIndex++) {
            if (isset($excludedColumnIndexes[$columnIndex])) {
                continue;
            }

            $columnIndexes[] = $columnIndex;
        }

        if ($columnIndexes === []) {
            return ['rows' => [], 'column_indexes' => []];
        }

        $startColumn = Coordinate::stringFromColumnIndex($startColumnIndex + 1);
        $range = $startColumn.'1:'.$highestDataColumn.$highestDataRow;
        $rawRows = $worksheet->rangeToArray($range, null, true, false, false);
        $rows = [];

        foreach ($rawRows as $index => $row) {
            $rowNumber = $index + 1;
            $cleanRow = [];

            foreach ($columnIndexes as $columnIndex) {
                $relativeIndex = $columnIndex - $startColumnIndex;
                $cleanRow[] = $this->sanitizeValue($row[$relativeIndex] ?? null);
            }

            if ($this->rowIsEmpty($cleanRow)) {
                continue;
            }

            $rows[$rowNumber] = $cleanRow;
        }

        return [
            'rows' => $rows,
            'column_indexes' => $columnIndexes,
        ];
    }

    /**
     * @param  array<int, array<int, string>>  $rows
     * @return array<int, array{row_number: int, filled_cells: int, preview: array<int, string>}>
     */
    private function possibleHeaderRows(array $rows): array
    {
        $candidates = [];

        foreach (array_slice($rows, 0, 10, true) as $rowNumber => $row) {
            $filledCells = count(array_filter($row, fn (string $value) => $value !== ''));

            if ($filledCells === 0) {
                continue;
            }

            $candidates[] = [
                'row_number' => $rowNumber,
                'filled_cells' => $filledCells,
                'preview' => array_slice(array_values(array_filter($row, fn (string $value) => $value !== '')), 0, 6),
            ];
        }

        return $candidates;
    }

    /**
     * @param  array<int, array<int, string>>  $rows
     * @return array<int, array{row_number: int, values: array<int, string>}>
     */
    private function previewRows(array $rows, int $headerRow, int $limit, array $ignoredRows): array
    {
        $preview = [];
        $ignoredRows = array_flip($ignoredRows);

        foreach ($rows as $rowNumber => $row) {
            if ($rowNumber <= $headerRow || isset($ignoredRows[$rowNumber])) {
                continue;
            }

            $preview[] = [
                'row_number' => $rowNumber,
                'values' => array_values($row),
            ];

            if (count($preview) >= $limit) {
                break;
            }
        }

        return $preview;
    }

    /**
     * @param  array<int, string>  $headerValues
     * @param  array<int, array{row_number: int, values: array<int, string>}>  $dataRows
     * @param  array<int, int>  $columnIndexes
     * @return array<int, array{index: int, letter: string, name: string, normalized_name: string, samples: array<int, string>}>
     */
    private function columns(array $headerValues, array $dataRows, array $columnIndexes): array
    {
        $highestColumnIndex = max(
            count($headerValues),
            ...array_map(fn (array $row) => count($row['values']), $dataRows ?: [['values' => []]])
        );
        $usedNames = [];
        $columns = [];

        for ($index = 0; $index < $highestColumnIndex; $index++) {
            $name = trim($headerValues[$index] ?? '') ?: 'Coluna '.($index + 1);
            $normalizedName = $this->uniqueNormalizedName($name, $index, $usedNames);
            $samples = [];

            foreach ($dataRows as $row) {
                $sample = $row['values'][$index] ?? '';

                if ($sample === '') {
                    continue;
                }

                $samples[] = $sample;

                if (count($samples) >= 5) {
                    break;
                }
            }

            $columns[] = [
                'index' => $index,
                'letter' => Coordinate::stringFromColumnIndex(($columnIndexes[$index] ?? $index) + 1),
                'name' => $name,
                'normalized_name' => $normalizedName,
                'samples' => $samples,
            ];
        }

        return $columns;
    }

    /**
     * @param  array<string, int>  $usedNames
     */
    private function uniqueNormalizedName(string $name, int $index, array &$usedNames): string
    {
        $baseName = Str::of($name)
            ->ascii()
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', '_')
            ->trim('_')
            ->toString();

        $baseName = $baseName !== '' ? $baseName : 'coluna_'.($index + 1);
        $count = ($usedNames[$baseName] ?? 0) + 1;
        $usedNames[$baseName] = $count;

        return $count === 1 ? $baseName : $baseName.'_'.$count;
    }

    private function sanitizeValue(mixed $value): string
    {
        if ($value instanceof DateTimeInterface) {
            return $value->format('d/m/Y H:i');
        }

        if ($value instanceof DateInterval) {
            return $value->format('%d dias %h:%i:%s');
        }

        if (is_bool($value)) {
            return $value ? 'Sim' : 'Não';
        }

        if ($value === null) {
            return '';
        }

        $value = is_scalar($value) ? (string) $value : '';
        $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $value) ?? '';

        return Str::limit(trim($value), 150, '...');
    }

    /**
     * @param  array<int, string>  $row
     */
    private function rowIsEmpty(array $row): bool
    {
        foreach ($row as $value) {
            if ($value !== '') {
                return false;
            }
        }

        return true;
    }
}

<?php

namespace App\Services;

use App\Enums\DashboardColumnType;
use Carbon\CarbonImmutable;
use InvalidArgumentException;

class ColumnValueConverterService
{
    public function convert(mixed $value, DashboardColumnType|string $type): mixed
    {
        $type = $type instanceof DashboardColumnType ? $type : DashboardColumnType::from($type);
        $value = trim((string) $value);

        if ($type === DashboardColumnType::Ignore) {
            return null;
        }

        if ($value === '') {
            return null;
        }

        return match ($type) {
            DashboardColumnType::Money => round($this->parseNumber($value), 2),
            DashboardColumnType::Percentage => $this->convertPercentage($value),
            DashboardColumnType::Number => $this->parseNumber($value),
            DashboardColumnType::Date => $this->convertDate($value),
            DashboardColumnType::Boolean => $this->convertBoolean($value),
            DashboardColumnType::LongText,
            DashboardColumnType::ShortText,
            DashboardColumnType::Category,
            DashboardColumnType::Identifier => $this->cleanText($value),
            DashboardColumnType::Ignore => null,
        };
    }

    private function convertPercentage(string $value): float
    {
        $hasSymbol = str_contains($value, '%');
        $number = $this->parseNumber(str_replace('%', '', $value));

        if (! $hasSymbol && abs($number) <= 1) {
            return round($number * 100, 4);
        }

        return round($number, 4);
    }

    private function convertDate(string $value): string
    {
        $formats = ['d/m/Y', 'd/m/y', 'Y-m-d', 'd-m-Y', 'd-m-y', 'd/m/Y H:i', 'Y-m-d H:i:s'];

        foreach ($formats as $format) {
            try {
                $date = CarbonImmutable::createFromFormat($format, $value);

                if ($date && $date->format($format) === $value) {
                    return $date->format('Y-m-d');
                }
            } catch (\Throwable) {
                // Try the next supported date format.
            }
        }

        throw new InvalidArgumentException('Data inválida. Use um formato como 21/05/2026.');
    }

    private function convertBoolean(string $value): bool
    {
        $normalized = mb_strtolower($this->cleanText($value));

        return match ($normalized) {
            'sim', 's', 'yes', 'y', 'true', '1' => true,
            'não', 'nao', 'n', 'no', 'false', '0' => false,
            default => throw new InvalidArgumentException('Valor de Sim/Não inválido.'),
        };
    }

    private function parseNumber(string $value): float
    {
        $value = str_replace(["\u{00A0}", 'R$', 'r$', '%', ' '], '', $value);
        $value = preg_replace('/[^\d,.\-]/', '', $value) ?? '';

        if ($value === '' || $value === '-' || $value === ',' || $value === '.') {
            throw new InvalidArgumentException('Número inválido.');
        }

        $lastComma = strrpos($value, ',');
        $lastDot = strrpos($value, '.');

        if ($lastComma !== false && $lastDot !== false) {
            $decimalSeparator = $lastComma > $lastDot ? ',' : '.';
            $thousandSeparator = $decimalSeparator === ',' ? '.' : ',';
            $value = str_replace($thousandSeparator, '', $value);
            $value = str_replace($decimalSeparator, '.', $value);
        } elseif ($lastComma !== false) {
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);
        } elseif ($lastDot !== false && preg_match('/\.\d{3}$/', $value) && substr_count($value, '.') === 1) {
            $value = str_replace('.', '', $value);
        }

        if (! is_numeric($value)) {
            throw new InvalidArgumentException('Número inválido.');
        }

        return (float) $value;
    }

    private function cleanText(string $value): string
    {
        return preg_replace('/\s+/u', ' ', trim($value)) ?? '';
    }
}

<?php

namespace App\Services;

use App\Enums\DashboardColumnType;
use Illuminate\Support\Str;

class ColumnTypeDetectorService
{
    /**
     * @param  array<int, string>  $samples
     */
    public function suggest(string $name, array $samples): DashboardColumnType
    {
        $normalizedName = Str::of($name)->ascii()->lower()->toString();
        $filledSamples = array_values(array_filter(
            array_map(fn (mixed $value) => trim((string) $value), $samples),
            fn (string $value) => $value !== ''
        ));

        if (Str::contains($normalizedName, ['valor', 'saldo', 'pago', 'investimento'])) {
            return DashboardColumnType::Money;
        }

        if ($this->samplesContain($filledSamples, '/R\$/i')) {
            return DashboardColumnType::Money;
        }

        if ($this->samplesContain($filledSamples, '/%/')) {
            return DashboardColumnType::Percentage;
        }

        if ($this->mostly($filledSamples, fn (string $value) => $this->looksLikeDate($value))) {
            return DashboardColumnType::Date;
        }

        if (Str::contains($normalizedName, ['codigo', 'id', 'processo', 'convenio'])) {
            return DashboardColumnType::Identifier;
        }

        if ($this->mostly($filledSamples, fn (string $value) => $this->looksLikeNumber($value))) {
            return DashboardColumnType::Number;
        }

        if ($this->hasFewRepeatedOptions($filledSamples)) {
            return DashboardColumnType::Category;
        }

        if ($this->hasLongText($filledSamples)) {
            return DashboardColumnType::LongText;
        }

        return DashboardColumnType::ShortText;
    }

    /**
     * @param  array<int, string>  $samples
     */
    private function samplesContain(array $samples, string $pattern): bool
    {
        foreach ($samples as $sample) {
            if (preg_match($pattern, $sample)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<int, string>  $samples
     */
    private function mostly(array $samples, callable $callback): bool
    {
        if ($samples === []) {
            return false;
        }

        $matches = 0;

        foreach ($samples as $sample) {
            if ($callback($sample)) {
                $matches++;
            }
        }

        return $matches / count($samples) >= 0.7;
    }

    private function looksLikeDate(string $value): bool
    {
        return (bool) preg_match('/^\d{1,2}[\/-]\d{1,2}[\/-]\d{2,4}$/', $value)
            || (bool) preg_match('/^\d{4}-\d{1,2}-\d{1,2}$/', $value);
    }

    private function looksLikeNumber(string $value): bool
    {
        return (bool) preg_match('/^-?\s*\d{1,3}([.,]\d{3})*([,.]\d+)?$/', $value)
            || (bool) preg_match('/^-?\s*\d+([,.]\d+)?$/', $value);
    }

    /**
     * @param  array<int, string>  $samples
     */
    private function hasFewRepeatedOptions(array $samples): bool
    {
        if (count($samples) < 3) {
            return false;
        }

        $unique = array_unique(array_map(
            fn (string $value) => Str::of($value)->ascii()->lower()->squish()->toString(),
            $samples
        ));

        return count($unique) <= min(8, max(2, (int) ceil(count($samples) * 0.6)));
    }

    /**
     * @param  array<int, string>  $samples
     */
    private function hasLongText(array $samples): bool
    {
        foreach ($samples as $sample) {
            if (mb_strlen($sample) > 120) {
                return true;
            }
        }

        return false;
    }
}

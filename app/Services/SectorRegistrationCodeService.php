<?php

namespace App\Services;

use App\Models\Sector;
use Illuminate\Support\Str;

class SectorRegistrationCodeService
{
    public function generate(?int $ignoreSectorId = null): string
    {
        do {
            $code = 'SEDUC-'.Str::upper(Str::random(8));
        } while ($this->codeExists($code, $ignoreSectorId));

        return $code;
    }

    public function normalize(?string $code): string
    {
        return Str::of($code ?? '')
            ->trim()
            ->upper()
            ->replace(' ', '')
            ->toString();
    }

    public function findActiveByCode(string $code): ?Sector
    {
        $normalizedCode = $this->normalize($code);

        if ($normalizedCode === '') {
            return null;
        }

        return Sector::query()
            ->where('registration_code', $normalizedCode)
            ->where('is_active', true)
            ->first();
    }

    private function codeExists(string $code, ?int $ignoreSectorId = null): bool
    {
        return Sector::query()
            ->when($ignoreSectorId, fn ($query) => $query->whereKeyNot($ignoreSectorId))
            ->where('registration_code', $code)
            ->exists();
    }
}

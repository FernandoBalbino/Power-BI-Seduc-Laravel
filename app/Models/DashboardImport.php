<?php

namespace App\Models;

use App\Enums\DashboardImportStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['dashboard_id', 'original_filename', 'file_path', 'sheet_name', 'status', 'imported_at'])]
class DashboardImport extends Model
{
    public function dashboard(): BelongsTo
    {
        return $this->belongsTo(Dashboard::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => DashboardImportStatus::class,
            'imported_at' => 'datetime',
        ];
    }
}

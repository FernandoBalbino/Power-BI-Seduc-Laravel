<?php

namespace App\Models;

use App\Enums\DashboardColumnType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'dashboard_id',
    'original_name',
    'normalized_name',
    'friendly_name',
    'type',
    'is_filterable',
    'is_chartable',
    'is_required',
    'position',
])]
class DashboardColumn extends Model
{
    public function dashboard(): BelongsTo
    {
        return $this->belongsTo(Dashboard::class);
    }

    public function isDimensional(): bool
    {
        return $this->type->isDimensional();
    }

    public function isMetric(): bool
    {
        return $this->type->isMetric();
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => DashboardColumnType::class,
            'is_filterable' => 'boolean',
            'is_chartable' => 'boolean',
            'is_required' => 'boolean',
        ];
    }
}

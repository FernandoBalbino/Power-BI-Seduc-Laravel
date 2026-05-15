<?php

namespace App\Models;

use App\Enums\DashboardColumnType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function baseRelationships(): HasMany
    {
        return $this->hasMany(DashboardRelationship::class, 'base_column_id');
    }

    public function relatedRelationships(): HasMany
    {
        return $this->hasMany(DashboardRelationship::class, 'related_column_id');
    }

    public function displayName(): string
    {
        return $this->friendly_name ?: $this->original_name;
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

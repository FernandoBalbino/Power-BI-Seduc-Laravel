<?php

namespace App\Models;

use App\Enums\DashboardRelationshipAggregation;
use App\Enums\DashboardRelationshipType;
use App\Services\RelationshipSuggestionService;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'dashboard_id',
    'base_column_id',
    'related_column_id',
    'aggregation',
    'relationship_type',
])]
class DashboardRelationship extends Model
{
    public function dashboard(): BelongsTo
    {
        return $this->belongsTo(Dashboard::class);
    }

    public function baseColumn(): BelongsTo
    {
        return $this->belongsTo(DashboardColumn::class, 'base_column_id');
    }

    public function relatedColumn(): BelongsTo
    {
        return $this->belongsTo(DashboardColumn::class, 'related_column_id');
    }

    /**
     * @return array<int, string>
     */
    public function suggestedChartTypes(): array
    {
        return app(RelationshipSuggestionService::class)
            ->chartTypesForColumns($this->baseColumn, $this->relatedColumn, $this->aggregation);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'aggregation' => DashboardRelationshipAggregation::class,
            'relationship_type' => DashboardRelationshipType::class,
        ];
    }
}

<?php

namespace App\Models;

use App\Enums\DashboardWidgetChartType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'dashboard_id',
    'title',
    'chart_type',
    'config_json',
    'position_x',
    'position_y',
    'width',
    'height',
])]
class DashboardWidget extends Model
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
            'chart_type' => DashboardWidgetChartType::class,
            'config_json' => 'array',
        ];
    }
}

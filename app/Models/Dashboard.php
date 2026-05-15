<?php

namespace App\Models;

use App\Enums\DashboardStatus;
use Database\Factories\DashboardFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['sector_id', 'user_id', 'name', 'description', 'status'])]
class Dashboard extends Model
{
    /** @use HasFactory<DashboardFactory> */
    use HasFactory;

    public function sector(): BelongsTo
    {
        return $this->belongsTo(Sector::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function imports(): HasMany
    {
        return $this->hasMany(DashboardImport::class);
    }

    public function columns(): HasMany
    {
        return $this->hasMany(DashboardColumn::class)->orderBy('position');
    }

    public function rows(): HasMany
    {
        return $this->hasMany(DashboardRow::class);
    }

    public function relationships(): HasMany
    {
        return $this->hasMany(DashboardRelationship::class);
    }

    public function widgets(): HasMany
    {
        return $this->hasMany(DashboardWidget::class)
            ->orderBy('position_y')
            ->orderBy('position_x')
            ->orderBy('id');
    }

    public function validRelationships(): HasMany
    {
        return $this->relationships()
            ->with(['baseColumn', 'relatedColumn'])
            ->whereHas('baseColumn')
            ->where(function ($query): void {
                $query->whereNull('related_column_id')
                    ->orWhereHas('relatedColumn');
            });
    }

    public function dimensionalColumns(): HasMany
    {
        return $this->columns()
            ->whereIn('type', ['short_text', 'category', 'identifier', 'date']);
    }

    public function metricColumns(): HasMany
    {
        return $this->columns()
            ->whereIn('type', ['number', 'money', 'percentage']);
    }

    public function latestImport(): HasOne
    {
        return $this->hasOne(DashboardImport::class)->latestOfMany();
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->isAdmin()) {
            return $query;
        }

        if (! $user->sector_id) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where('sector_id', $user->sector_id);
    }

    public function canBeAccessedBy(User $user): bool
    {
        return $user->isAdmin() || ($user->sector_id !== null && $this->sector_id === $user->sector_id);
    }

    public function recordsCount(): int
    {
        return $this->rows()->count();
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => DashboardStatus::class,
        ];
    }
}

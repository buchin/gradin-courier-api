<?php

namespace App\Models;

use Database\Factories\CourierFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Courier extends Model
{
    /** @use HasFactory<CourierFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'phone',
        'email',
        'vehicle_type',
        'license_plate',
        'level',
        'is_active',
        'registered_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'level' => 'integer',
            'is_active' => 'boolean',
            'registered_at' => 'datetime',
        ];
    }

    /**
     * Filter couriers by multi-word name search (every word must match).
     */
    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        $trimmed = trim((string) $search);
        if ($trimmed === '') {
            return $query;
        }

        $terms = preg_split('/\s+/', $trimmed, -1, PREG_SPLIT_NO_EMPTY);
        if (! $terms) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($terms): void {
            foreach ($terms as $term) {
                $q->whereRaw('LOWER(name) LIKE ?', ['%'.mb_strtolower($term).'%']);
            }
        });
    }

    /**
     * Filter couriers by one or more levels (e.g. "2,3" or [2, 3]).
     *
     * @param  string|array<int, int|string>|null  $levels
     */
    public function scopeFilterLevels(Builder $query, string|array|null $levels): Builder
    {
        if ($levels === null || $levels === '' || $levels === []) {
            return $query;
        }

        $raw = is_array($levels) ? $levels : explode(',', $levels);
        $parsed = array_values(array_filter(
            array_map(static fn (mixed $v): int => (int) trim((string) $v), $raw),
            static fn (int $level): bool => $level >= 1 && $level <= 5,
        ));

        if ($parsed === []) {
            return $query;
        }

        return $query->whereIn('level', $parsed);
    }
}

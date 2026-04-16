<?php

namespace Webfloo\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin Model
 *
 * @property int $sort_order
 * @property list<string> $fillable
 */
trait Sortable
{
    public static function bootSortable(): void
    {
        static::creating(function (Model $model): void {
            $currentOrder = $model->getAttribute('sort_order');
            if ($currentOrder === null) {
                $maxOrder = static::max('sort_order');
                $model->setAttribute('sort_order', (is_numeric($maxOrder) ? (int) $maxOrder : 0) + 1);
            }
        });
    }

    public function initializeSortable(): void
    {
        $this->fillable = array_merge($this->fillable, ['sort_order']);
    }

    /**
     * @param  Builder<Model>  $query
     * @return Builder<Model>
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order');
    }

    public function moveUp(): void
    {
        $previous = static::where('sort_order', '<', $this->sort_order)
            ->orderByDesc('sort_order')
            ->first();

        if ($previous instanceof Model) {
            $this->swapOrderWith($previous);
        }
    }

    public function moveDown(): void
    {
        $next = static::where('sort_order', '>', $this->sort_order)
            ->orderBy('sort_order')
            ->first();

        if ($next instanceof Model) {
            $this->swapOrderWith($next);
        }
    }

    public function swapOrderWith(Model $other): void
    {
        $thisOrder = $this->sort_order;
        $otherOrder = $other->getAttribute('sort_order');

        $this->update(['sort_order' => $otherOrder]);
        $other->update(['sort_order' => $thisOrder]);
    }
}

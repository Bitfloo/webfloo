<?php

namespace Webfloo\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin Model
 *
 * @property bool $is_active
 */
trait HasActive
{
    /**
     * @param  Builder<Model>  $query
     * @return Builder<Model>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}

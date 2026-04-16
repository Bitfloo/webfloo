<?php

namespace Webfloo\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin Model
 *
 * @property bool $is_featured
 */
trait HasFeatured
{
    /**
     * @param  Builder<Model>  $query
     * @return Builder<Model>
     */
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }
}

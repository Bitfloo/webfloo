<?php

namespace Webfloo\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * @mixin Model
 *
 * @property string|null $slug
 * @property int|null $id
 */
trait HasSlug
{
    public static function bootHasSlug(): void
    {
        static::creating(function (Model $model): void {
            $slug = $model->getAttribute('slug');
            /** @var string $sourceField */
            $sourceField = method_exists($model, 'getSlugSource') ? $model->getSlugSource() : 'title';
            $source = $model->getAttribute($sourceField);
            if (empty($slug) && is_string($source) && $source !== '') {
                $model->setAttribute('slug', static::generateUniqueSlugFor($source, null));
            }
        });

        static::updating(function (Model $model): void {
            /** @var string $sourceField */
            $sourceField = method_exists($model, 'getSlugSource') ? $model->getSlugSource() : 'title';
            $source = $model->getAttribute($sourceField);
            if ($model->isDirty($sourceField) && ! $model->isDirty('slug') && is_string($source) && $source !== '') {
                $id = $model->getAttribute('id');
                $model->setAttribute('slug', static::generateUniqueSlugFor($source, is_int($id) ? $id : null));
            }
        });
    }

    protected static function generateUniqueSlugFor(string $title, ?int $excludeId): string
    {
        $slug = Str::slug($title);
        $originalSlug = $slug;
        $counter = 1;

        $query = static::where('slug', $slug);
        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId);
        }

        while ($query->exists()) {
            $slug = $originalSlug.'-'.$counter;
            $counter++;
            $query = static::where('slug', $slug);
            if ($excludeId !== null) {
                $query->where('id', '!=', $excludeId);
            }
        }

        return $slug;
    }

    public function generateUniqueSlug(string $source): string
    {
        $id = $this->getAttribute('id');

        return static::generateUniqueSlugFor($source, is_int($id) ? $id : null);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}

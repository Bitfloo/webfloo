<?php

namespace Webfloo\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @mixin Model
 *
 * @property string $status
 * @property Carbon|null $published_at
 * @property array<string> $fillable
 * @property array<string, string> $casts
 */
trait Publishable
{
    public function initializePublishable(): void
    {
        $this->fillable = array_merge($this->fillable, [
            'status',
            'published_at',
        ]);

        $this->casts = array_merge($this->casts, [
            'published_at' => 'datetime',
        ]);
    }

    /**
     * @param  Builder<Model>  $query
     * @return Builder<Model>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('status', 'published')
            ->where(function (Builder $q): void {
                $q->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            });
    }

    /**
     * @param  Builder<Model>  $query
     * @return Builder<Model>
     */
    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', 'draft');
    }

    /**
     * @param  Builder<Model>  $query
     * @return Builder<Model>
     */
    public function scopeScheduled(Builder $query): Builder
    {
        return $query
            ->where('status', 'published')
            ->where('published_at', '>', now());
    }

    public function isPublished(): bool
    {
        return $this->status === 'published'
            && ($this->published_at === null || $this->published_at <= now());
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function publish(): void
    {
        $this->update([
            'status' => 'published',
            'published_at' => $this->published_at ?? now(),
        ]);
    }

    public function unpublish(): void
    {
        $this->update(['status' => 'draft']);
    }

    /**
     * @return array<string, string>
     */
    public static function getStatusOptions(): array
    {
        return [
            'draft' => 'Draft',
            'published' => 'Published',
            'archived' => 'Archived',
        ];
    }
}

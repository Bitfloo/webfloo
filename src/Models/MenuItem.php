<?php

namespace Webfloo\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;
use Spatie\Translatable\HasTranslations;
use Webfloo\Database\Factories\MenuItemFactory;
use Webfloo\Traits\HasActive;
use Webfloo\Traits\Sortable;

/**
 * @property int $id
 * @property string $label
 * @property string|null $href
 * @property string $target
 * @property string $location
 * @property int|null $parent_id
 * @property bool $is_active
 * @property int $sort_order
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read MenuItem|null $parent
 * @property-read Collection<int, MenuItem> $children
 */
class MenuItem extends Model
{
    /** @use HasFactory<MenuItemFactory> */
    use HasFactory;

    use HasActive;
    use HasTranslations;
    use Sortable;

    protected static function newFactory(): MenuItemFactory
    {
        return MenuItemFactory::new();
    }

    /** @var list<string> */
    public array $translatable = ['label'];

    /**
     * @var list<string>
     */
    protected $fillable = [
        'label',
        'href',
        'target',
        'location',
        'parent_id',
        'is_active',
        'sort_order',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Available menu locations.
     */
    public const LOCATIONS = [
        'header' => 'Menu glowne (Header)',
        'footer_company' => 'Footer - Firma',
        'footer_services' => 'Footer - Uslugi',
        'footer_legal' => 'Footer - Prawne',
    ];

    /**
     * Available target options.
     */
    public const TARGETS = [
        '_self' => 'Ta sama karta',
        '_blank' => 'Nowa karta',
    ];

    /**
     * Get the parent menu item.
     *
     * @return BelongsTo<MenuItem, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class, 'parent_id');
    }

    /**
     * Get the child menu items ordered by sort_order.
     *
     * @return HasMany<MenuItem, $this>
     */
    public function children(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'parent_id')->orderBy('sort_order');
    }

    /**
     * Scope to filter menu items by location.
     *
     * @param  Builder<MenuItem>  $query
     * @return Builder<MenuItem>
     */
    public function scopeInLocation(Builder $query, string $location): Builder
    {
        return $query->where('location', $location);
    }

    /**
     * Scope to filter only top-level menu items (no parent).
     *
     * @param  Builder<MenuItem>  $query
     * @return Builder<MenuItem>
     */
    public function scopeTopLevel(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Get available location options.
     *
     * @return array<string, string>
     */
    public static function getLocationOptions(): array
    {
        return self::LOCATIONS;
    }

    /**
     * Get available target options.
     *
     * @return array<string, string>
     */
    public static function getTargetOptions(): array
    {
        return self::TARGETS;
    }

    /**
     * Get active, sorted, top-level menu items with children for a given location.
     *
     * @return Collection<int, MenuItem>
     */
    public static function getForLocation(string $location): Collection
    {
        $items = static::query()
            ->active()
            ->inLocation($location)
            ->topLevel()
            ->ordered()
            ->get();

        // @phpstan-ignore argument.type (callback modifies query, return type not relevant)
        $items->load(['children' => function (HasMany $relation): void {
            $relation->where('is_active', true)->orderBy('sort_order');
        }]);

        /** @var Collection<int, MenuItem> */
        return $items;
    }

    /**
     * Cache key for grouped menu items. Bumped automatically on MenuItem save/delete
     * via the boot() hook below. TTL = 1 day as ultimate fallback only.
     */
    private const CACHE_KEY = 'bitfloo.menu_items.grouped_by_location';

    private const CACHE_TTL_SECONDS = 86400;

    /**
     * Load ALL active menu items in 1 query, grouped by location.
     * Called every request via HandleInertiaRequests — cached because menu
     * data changes rarely (admin edit) but is hot on the read path.
     *
     * @return array<string, Collection<int, MenuItem>>
     */
    public static function getAllGroupedByLocation(): array
    {
        /** @var array<string, Collection<int, MenuItem>> */
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL_SECONDS, static function (): array {
            $all = static::query()
                ->active()
                ->topLevel()
                ->ordered()
                ->get();

            // @phpstan-ignore argument.type
            $all->load(['children' => function (HasMany $relation): void {
                $relation->where('is_active', true)->orderBy('sort_order');
            }]);

            /** @var array<string, Collection<int, MenuItem>> */
            return $all->groupBy('location')->toArray() !== []
                ? $all->groupBy('location')->all()
                : [];
        });
    }

    /**
     * Invalidate the grouped-by-location cache whenever any MenuItem mutates.
     * Idempotent — safe to call from saved/deleted/restored events.
     */
    public static function flushMenuCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    protected static function booted(): void
    {
        static::saved(static fn (): bool => Cache::forget(self::CACHE_KEY));
        static::deleted(static fn (): bool => Cache::forget(self::CACHE_KEY));
    }
}

<?php

namespace Webfloo\Models;

use Webfloo\Traits\HasSeo;
use Webfloo\Traits\HasSlug;
use Webfloo\Traits\Publishable;
use Webfloo\Traits\Sortable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

/**
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property array<string, mixed>|null $content
 * @property string $template
 * @property int|null $parent_id
 * @property string|null $meta_title
 * @property string|null $meta_description
 * @property string|null $meta_image
 * @property string $status
 * @property Carbon|null $published_at
 * @property int $sort_order
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Page|null $parent
 * @property-read Collection<int, Page> $children
 * @property-read string $url
 * @property-read array<int, array{title: string, url: string, slug: string}> $breadcrumbs
 */
class Page extends Model
{
    use HasSeo;
    use HasSlug;
    use HasTranslations;
    use Publishable;
    use SoftDeletes;
    use Sortable;

    /** @var list<string> */
    public array $translatable = ['title', 'meta_title', 'meta_description'];

    /**
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'slug',
        'content',
        'template',
        'parent_id',
        'meta_title',
        'meta_description',
        'meta_image',
        'status',
        'published_at',
        'sort_order',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'content' => 'array',
            'published_at' => 'datetime',
            'sort_order' => 'integer',
        ];
    }

    /** Maximum ancestor traversal depth to prevent infinite loops on circular references. */
    private const MAX_ANCESTOR_DEPTH = 10;

    /**
     * Available template options for pages.
     *
     * @var array<string, string>
     */
    public const TEMPLATES = [
        'default' => 'Default',
        'home' => 'Homepage',
        'contact' => 'Contact',
        'services' => 'Services',
        'about' => 'About',
    ];

    /**
     * Memoized ancestor chain (root-first order). Null = not yet computed.
     *
     * @var list<Page>|null
     */
    private ?array $ancestorsCache = null;

    /**
     * Get the parent page.
     *
     * @return BelongsTo<Page, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Page::class, 'parent_id');
    }

    /**
     * Get the child pages ordered by sort_order.
     *
     * @return HasMany<Page, $this>
     */
    public function children(): HasMany
    {
        return $this->hasMany(Page::class, 'parent_id')->orderBy('sort_order');
    }

    /**
     * Collect ancestor chain in root-first order, reusing eager-loaded relations.
     *
     * Result is memoized per instance -- calling url, breadcrumbs, and getDepth()
     * on the same page triggers at most one traversal (and zero extra queries when
     * the parent relation was eager-loaded recursively).
     *
     * @return list<Page>
     */
    public function getAncestors(): array
    {
        if ($this->ancestorsCache !== null) {
            return $this->ancestorsCache;
        }

        $ancestors = [];
        $current = $this->parent;
        $depth = 0;

        while ($current !== null && $depth < self::MAX_ANCESTOR_DEPTH) {
            $ancestors[] = $current;
            $current = $current->parent;
            $depth++;
        }

        $this->ancestorsCache = array_reverse($ancestors);

        return $this->ancestorsCache;
    }

    /**
     * Build the full URL path considering parent hierarchy.
     *
     * Uses memoized ancestor chain -- no N+1 when parent is eager-loaded.
     */
    public function getUrlAttribute(): string
    {
        $segments = array_map(
            fn (Page $ancestor): string => $ancestor->slug,
            $this->getAncestors(),
        );
        $segments[] = $this->slug;

        return '/'.implode('/', $segments);
    }

    /**
     * Get array of ancestors + self for breadcrumb navigation.
     *
     * Uses memoized ancestor chain -- builds URLs from slugs in a single pass
     * instead of calling getUrlAttribute() recursively per ancestor.
     *
     * @return array<int, array{title: string, url: string, slug: string}>
     */
    public function getBreadcrumbsAttribute(): array
    {
        $ancestors = $this->getAncestors();
        $breadcrumbs = [];
        $pathSegments = [];

        foreach ($ancestors as $ancestor) {
            $pathSegments[] = $ancestor->slug;
            $breadcrumbs[] = [
                'title' => $ancestor->title,
                'url' => '/'.implode('/', $pathSegments),
                'slug' => $ancestor->slug,
            ];
        }

        $pathSegments[] = $this->slug;
        $breadcrumbs[] = [
            'title' => $this->title,
            'url' => '/'.implode('/', $pathSegments),
            'slug' => $this->slug,
        ];

        return $breadcrumbs;
    }

    /**
     * Recursive eager-load string for loading the full parent chain.
     *
     * Usage: Page::with([Page::parentChainEagerLoad()])->get()
     */
    public static function parentChainEagerLoad(): string
    {
        return implode('.', array_fill(0, self::MAX_ANCESTOR_DEPTH, 'parent'));
    }

    /**
     * Scope to only root pages (no parent).
     *
     * @param  Builder<Page>  $query
     * @return Builder<Page>
     */
    public function scopeRoots(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope to filter pages by template.
     *
     * @param  Builder<Page>  $query
     * @return Builder<Page>
     */
    public function scopeByTemplate(Builder $query, string $template): Builder
    {
        return $query->where('template', $template);
    }

    /**
     * Scope to eager-load the full parent chain for URL/breadcrumb computation.
     *
     * @param  Builder<Page>  $query
     * @return Builder<Page>
     */
    public function scopeWithParentChain(Builder $query): Builder
    {
        return $query->with([self::parentChainEagerLoad()]);
    }

    /**
     * Check if this page is a root page (has no parent).
     */
    public function isRoot(): bool
    {
        return $this->parent_id === null;
    }

    /**
     * Check if this page has any child pages.
     */
    public function hasChildren(): bool
    {
        return $this->children()->exists();
    }

    /**
     * Get the nesting depth level (0 for root pages).
     *
     * Uses memoized ancestor chain -- no extra queries.
     */
    public function getDepth(): int
    {
        return count($this->getAncestors());
    }

    /**
     * Get available template options.
     *
     * @return array<string, string>
     */
    public static function getTemplateOptions(): array
    {
        return self::TEMPLATES;
    }

    /**
     * Clear memoized ancestor cache (e.g. after reparenting).
     */
    public function clearAncestorCache(): void
    {
        $this->ancestorsCache = null;
    }
}

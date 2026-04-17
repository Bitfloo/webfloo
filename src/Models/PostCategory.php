<?php

namespace Webfloo\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;
use Webfloo\Database\Factories\PostCategoryFactory;
use Webfloo\Traits\HasActive;
use Webfloo\Traits\HasSlug;
use Webfloo\Traits\Sortable;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property string|null $icon
 * @property string $color
 * @property bool $is_active
 * @property int $sort_order
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Collection<int, Post> $posts
 * @property-read int $posts_count
 */
class PostCategory extends Model
{
    /** @use HasFactory<PostCategoryFactory> */
    use HasFactory;

    use HasActive;
    use HasSlug;
    use HasTranslations;
    use Sortable;

    protected static function newFactory(): PostCategoryFactory
    {
        return PostCategoryFactory::new();
    }

    /** @var list<string> */
    public array $translatable = ['name', 'description'];

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'color',
        'is_active',
        'sort_order',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Available color options for categories.
     *
     * @var array<string, string>
     */
    public const COLORS = [
        'primary' => 'Primary',
        'secondary' => 'Secondary',
        'accent' => 'Accent',
        'info' => 'Info',
        'success' => 'Success',
        'warning' => 'Warning',
        'error' => 'Error',
        'neutral' => 'Neutral',
    ];

    /**
     * Get the source field for generating the slug.
     */
    protected function getSlugSource(): string
    {
        return 'name';
    }

    /**
     * Get posts in this category.
     *
     * @return HasMany<Post, $this>
     */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    /**
     * Get available color options.
     *
     * @return array<string, string>
     */
    public static function getColorOptions(): array
    {
        return self::COLORS;
    }

    /**
     * Get the badge CSS class for this category's color.
     */
    public function getBadgeClass(): string
    {
        return "badge-{$this->color}";
    }
}

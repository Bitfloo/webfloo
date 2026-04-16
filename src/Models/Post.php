<?php

namespace Webfloo\Models;

use Webfloo\Traits\HasFeatured;
use Webfloo\Traits\HasSeo;
use Webfloo\Traits\HasSlug;
use Webfloo\Traits\Publishable;
use Webfloo\Traits\Sortable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Storage;
use Spatie\Translatable\HasTranslations;

/**
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property string|null $excerpt
 * @property string|null $content
 * @property string|null $featured_image
 * @property int|null $post_category_id
 * @property int|null $author_id
 * @property int|null $reading_time
 * @property string|null $meta_title
 * @property string|null $meta_description
 * @property string|null $meta_image
 * @property bool $no_index
 * @property string $status
 * @property Carbon|null $published_at
 * @property bool $is_featured
 * @property int $sort_order
 * @property int $views_count
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 * @property-read PostCategory|null $category
 * @property-read User|null $author
 * @property-read Collection<int, Post> $relatedPosts
 * @property-read Collection<int, Project> $relatedProjects
 * @property-read string $url
 * @property-read string|null $featured_image_url
 */
class Post extends Model
{
    use HasFeatured;
    use HasSeo;
    use HasSlug;
    use HasTranslations;
    use Publishable;
    use SoftDeletes;
    use Sortable;

    /** @var list<string> */
    public array $translatable = ['title', 'excerpt', 'content', 'meta_title', 'meta_description'];

    /**
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'content',
        'featured_image',
        'post_category_id',
        'author_id',
        'reading_time',
        'meta_title',
        'meta_description',
        'meta_image',
        'no_index',
        'status',
        'published_at',
        'is_featured',
        'sort_order',
        'views_count',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'is_featured' => 'boolean',
            'no_index' => 'boolean',
            'reading_time' => 'integer',
            'views_count' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Get the category of the post.
     *
     * @return BelongsTo<PostCategory, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(PostCategory::class, 'post_category_id');
    }

    /**
     * Get the author of the post.
     *
     * @return BelongsTo<User, $this>
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(webfloo_user_model(), 'author_id');
    }

    /**
     * Get related posts.
     *
     * @return BelongsToMany<Post, $this>
     */
    public function relatedPosts(): BelongsToMany
    {
        return $this->belongsToMany(
            Post::class,
            'post_related',
            'post_id',
            'related_post_id'
        );
    }

    /**
     * Get related projects (case studies).
     *
     * @return BelongsToMany<Project, $this>
     */
    public function relatedProjects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'post_project');
    }

    /**
     * Scope to filter by category.
     *
     * @param  Builder<Post>  $query
     * @return Builder<Post>
     */
    public function scopeByCategory(Builder $query, PostCategory|int $category): Builder
    {
        $categoryId = $category instanceof PostCategory ? $category->id : $category;

        return $query->where('post_category_id', $categoryId);
    }

    /**
     * Get the URL for this post.
     */
    public function getUrlAttribute(): string
    {
        return "/blog/{$this->slug}";
    }

    /**
     * Get the full URL for the featured image.
     */
    public function getFeaturedImageUrlAttribute(): ?string
    {
        if (! $this->featured_image) {
            return null;
        }

        return Storage::url($this->featured_image);
    }

    /**
     * Increment the view count.
     */
    public function incrementViews(): void
    {
        $this->increment('views_count');
    }

    /**
     * Calculate and set reading time based on content.
     */
    public function calculateReadingTime(): int
    {
        $wordCount = str_word_count(strip_tags($this->content ?? ''));
        $readingTime = (int) ceil($wordCount / 200);

        return max(1, $readingTime);
    }

    /**
     * Boot the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::saving(function (Post $post): void {
            if ($post->isDirty('content') && ! $post->isDirty('reading_time')) {
                $post->reading_time = $post->calculateReadingTime();
            }
        });
    }

    /**
     * Convert to detail array for Inertia show page.
     *
     * @return array{title: string|null, slug: string|null, excerpt: string|null, content: string, image: string|null, category: array{name: string, slug: string}|null, author: array{name: string}|null, published_at: string|null, reading_time: int|null, views_count: int, seo: array{title: string, description: string|null, image: string|null, no_index: bool}}
     */
    public function toDetailArray(): array
    {
        return [
            'title' => $this->title,
            'slug' => $this->slug,
            'excerpt' => $this->excerpt,
            'content' => is_string($c = clean($this->content ?? '')) ? $c : '',
            'image' => $this->featured_image ? Storage::url($this->featured_image) : null,
            'category' => $this->category ? ['name' => $this->category->name, 'slug' => $this->category->slug] : null,
            'author' => $this->author ? ['name' => $this->author->name] : null,
            'published_at' => $this->published_at?->format('d M Y'),
            'reading_time' => $this->reading_time,
            'views_count' => $this->views_count,
            'seo' => $this->getSeoData(),
        ];
    }

    /**
     * Convert to card array for Inertia frontend.
     *
     * @return array{title: string|null, slug: string|null, excerpt: string|null, image: string|null, category: array{name: string, slug: string}|null, published_at: string|null, reading_time: int|null}
     */
    public function toCardArray(): array
    {
        return [
            'title' => $this->title,
            'slug' => $this->slug,
            'excerpt' => $this->excerpt,
            'image' => $this->featured_image ? Storage::url($this->featured_image) : null,
            'category' => $this->category ? ['name' => $this->category->name, 'slug' => $this->category->slug] : null,
            'published_at' => $this->published_at?->format('d M Y'),
            'reading_time' => $this->reading_time,
        ];
    }
}

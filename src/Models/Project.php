<?php

namespace Webfloo\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Spatie\Translatable\HasTranslations;
use Webfloo\Traits\HasActive;
use Webfloo\Traits\HasFeatured;
use Webfloo\Traits\HasSlug;
use Webfloo\Traits\Sortable;

/**
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property string|null $excerpt
 * @property string|null $description
 * @property string|null $challenge
 * @property string|null $solution
 * @property string|null $results
 * @property string|null $image
 * @property array<int, string>|null $gallery
 * @property string|null $category
 * @property string|null $industry
 * @property array<int, string>|null $technologies
 * @property array<int, array{value: string, label: string}>|null $metrics
 * @property array<int, string>|null $achievements
 * @property string|null $testimonial_quote
 * @property string|null $testimonial_author
 * @property string|null $testimonial_role
 * @property string|null $testimonial_company
 * @property string|null $testimonial_avatar
 * @property string|null $client
 * @property string|null $url
 * @property string|null $video_url
 * @property string|null $duration
 * @property string|null $team_size
 * @property bool $is_featured
 * @property bool $is_active
 * @property int $sort_order
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Project extends Model
{
    use HasActive;
    use HasFeatured;
    use HasSlug;
    use HasTranslations;
    use Sortable;

    /** @var list<string> */
    public array $translatable = ['title', 'excerpt', 'description', 'challenge', 'solution', 'results', 'testimonial_quote'];

    /**
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'description',
        'challenge',
        'solution',
        'results',
        'image',
        'gallery',
        'category',
        'industry',
        'technologies',
        'metrics',
        'achievements',
        'testimonial_quote',
        'testimonial_author',
        'testimonial_role',
        'testimonial_company',
        'testimonial_avatar',
        'client',
        'url',
        'video_url',
        'duration',
        'team_size',
        'is_featured',
        'is_active',
        'sort_order',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'technologies' => 'array',
            'metrics' => 'array',
            'achievements' => 'array',
            'gallery' => 'array',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * @param  Builder<Project>  $query
     * @return Builder<Project>
     */
    public function scopeByIndustry(Builder $query, string $industry): Builder
    {
        return $query->where('industry', $industry);
    }

    /**
     * Check if project has case study content.
     */
    public function hasCaseStudy(): bool
    {
        return ! empty($this->challenge) || ! empty($this->solution) || ! empty($this->results);
    }

    /**
     * Check if project has testimonial.
     */
    public function hasTestimonial(): bool
    {
        return ! empty($this->testimonial_quote) && ! empty($this->testimonial_author);
    }

    /**
     * Check if project has metrics.
     */
    public function hasMetrics(): bool
    {
        return ! empty($this->metrics);
    }

    /**
     * Check if project has gallery.
     */
    public function hasGallery(): bool
    {
        return ! empty($this->gallery);
    }

    /**
     * @return array<string, string>
     */
    public static function getCategoryOptions(): array
    {
        return [
            'web' => 'Aplikacje webowe',
            'mobile' => 'Aplikacje mobilne',
            'ecommerce' => 'E-commerce',
            'crm' => 'Systemy CRM',
            'erp' => 'Systemy ERP',
            'saas' => 'Platformy SaaS',
            'api' => 'Integracje API',
            'other' => 'Inne',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function getIndustryOptions(): array
    {
        return [
            'fintech' => 'FinTech',
            'ecommerce' => 'E-commerce',
            'healthcare' => 'Healthcare',
            'realestate' => 'Nieruchomości',
            'logistics' => 'Logistyka',
            'manufacturing' => 'Produkcja',
            'education' => 'Edukacja',
            'hospitality' => 'HoReCa',
            'automotive' => 'Motoryzacja',
            'media' => 'Media & Entertainment',
            'other' => 'Inne',
        ];
    }

    /**
     * Convert to card array for Inertia frontend.
     *
     * @return array{title: string|null, slug: string|null, excerpt: string|null, image: string|null, category: string|null, technologies: array<string>}
     */
    public function toCardArray(): array
    {
        return [
            'title' => $this->title,
            'slug' => $this->slug,
            'excerpt' => $this->excerpt,
            'image' => $this->image ? Storage::url($this->image) : null,
            'category' => $this->category ? (self::getCategoryOptions()[$this->category] ?? $this->category) : null,
            'technologies' => $this->technologies ?? [],
        ];
    }
}

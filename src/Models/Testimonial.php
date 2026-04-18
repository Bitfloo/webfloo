<?php

namespace Webfloo\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;
use Webfloo\Database\Factories\TestimonialFactory;
use Webfloo\Traits\HasActive;
use Webfloo\Traits\HasFeatured;
use Webfloo\Traits\Sortable;

/**
 * @property int $id
 * @property string $content
 * @property string $author
 * @property string|null $role
 * @property string|null $company
 * @property string|null $avatar
 * @property int|null $rating
 * @property bool $is_active
 * @property bool $is_featured
 * @property int $sort_order
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Testimonial extends Model
{
    use HasActive;

    /** @use HasFactory<TestimonialFactory> */
    use HasFactory;

    use HasFeatured;
    use HasTranslations;
    use Sortable;

    protected static function newFactory(): TestimonialFactory
    {
        return TestimonialFactory::new();
    }

    /** @var list<string> */
    public array $translatable = ['content', 'role', 'company'];

    /**
     * @var list<string>
     */
    protected $fillable = [
        'content',
        'author',
        'role',
        'company',
        'avatar',
        'rating',
        'is_active',
        'is_featured',
        'sort_order',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'sort_order' => 'integer',
        ];
    }
}

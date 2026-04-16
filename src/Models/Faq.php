<?php

namespace Webfloo\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;
use Webfloo\Traits\HasActive;
use Webfloo\Traits\Sortable;

/**
 * @property int $id
 * @property string $question
 * @property string $answer
 * @property string|null $icon
 * @property string|null $category
 * @property bool $is_active
 * @property int $sort_order
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Faq extends Model
{
    use HasActive;
    use HasTranslations;
    use Sortable;

    /** @var list<string> */
    public array $translatable = ['question', 'answer'];

    /**
     * @var list<string>
     */
    protected $fillable = [
        'question',
        'answer',
        'icon',
        'category',
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
     * @param  Builder<Faq>  $query
     * @return Builder<Faq>
     */
    public function scopeInCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', $category);
    }
}

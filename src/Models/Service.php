<?php

namespace Webfloo\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;
use Webfloo\Database\Factories\ServiceFactory;
use Webfloo\Traits\HasActive;
use Webfloo\Traits\HasFeatured;
use Webfloo\Traits\Sortable;

/**
 * @property int $id
 * @property string $title
 * @property string $icon
 * @property string|null $description
 * @property string|null $href
 * @property bool $is_active
 * @property bool $is_featured
 * @property int $sort_order
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Service extends Model
{
    use HasActive;
    use HasFeatured;
    /** @use HasFactory<ServiceFactory> */
    use HasFactory;
    use HasTranslations;
    use Sortable;

    /** @var list<string> */
    public array $translatable = ['title', 'description'];

    protected static function newFactory(): ServiceFactory
    {
        return ServiceFactory::new();
    }

    /**
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'icon',
        'description',
        'href',
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
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function getIconOptions(): array
    {
        return [
            'globe-alt' => 'Globe Alt',
            'code-bracket' => 'Code Bracket',
            'cog-6-tooth' => 'Cog',
            'device-phone-mobile' => 'Mobile Device',
            'chart-bar' => 'Chart Bar',
            'wrench-screwdriver' => 'Wrench Screwdriver',
            'server' => 'Server',
            'cloud' => 'Cloud',
            'database' => 'Database',
            'cpu-chip' => 'CPU Chip',
            'bolt' => 'Bolt',
            'rocket-launch' => 'Rocket Launch',
            'light-bulb' => 'Light Bulb',
            'puzzle-piece' => 'Puzzle Piece',
            'shield-check' => 'Shield Check',
            'lock-closed' => 'Lock Closed',
        ];
    }
}

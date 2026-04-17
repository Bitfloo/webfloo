<?php

declare(strict_types=1);

namespace Webfloo\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Webfloo\Database\Factories\LeadTagFactory;

/**
 * @property int $id
 * @property string $name
 * @property string $color
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Collection<int, Lead> $leads
 */
class LeadTag extends Model
{
    /** @use HasFactory<LeadTagFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'color',
    ];

    protected static function newFactory(): LeadTagFactory
    {
        return LeadTagFactory::new();
    }

    /**
     * @return array<string, string>
     */
    public static function getColorOptions(): array
    {
        return [
            'gray' => 'Szary',
            'primary' => 'Niebieski',
            'success' => 'Zielony',
            'warning' => 'Żółty',
            'danger' => 'Czerwony',
            'info' => 'Błękitny',
        ];
    }

    /**
     * @return BelongsToMany<Lead, $this>
     */
    public function leads(): BelongsToMany
    {
        return $this->belongsToMany(Lead::class, 'lead_lead_tag');
    }
}

<?php

namespace Webfloo\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Webfloo\Database\Factories\NewsletterSubscriberFactory;
use Webfloo\Traits\HasActive;

/**
 * @property int $id
 * @property string $email
 * @property string|null $name
 * @property bool $is_active
 * @property Carbon $subscribed_at
 * @property Carbon|null $unsubscribed_at
 * @property string|null $ip_address
 * @property string $source
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class NewsletterSubscriber extends Model
{
    /** @use HasFactory<NewsletterSubscriberFactory> */
    use HasFactory;

    use HasActive;

    protected static function newFactory(): NewsletterSubscriberFactory
    {
        return NewsletterSubscriberFactory::new();
    }

    /**
     * @var list<string>
     */
    protected $fillable = [
        'email',
        'name',
        'is_active',
        'subscribed_at',
        'unsubscribed_at',
        'ip_address',
        'source',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'subscribed_at' => 'datetime',
            'unsubscribed_at' => 'datetime',
        ];
    }
}

<?php

declare(strict_types=1);

namespace Webfloo\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User;

/**
 * @property int $id
 * @property int $lead_id
 * @property int|null $user_id
 * @property string $type
 * @property string $title
 * @property string|null $description
 * @property array<string, mixed>|null $metadata
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Lead $lead
 * @property-read User|null $user
 */
class LeadActivity extends Model
{
    public const TYPE_STATUS_CHANGE = 'status_change';

    public const TYPE_NOTE = 'note';

    public const TYPE_EMAIL_SENT = 'email_sent';

    public const TYPE_CALL = 'call';

    public const TYPE_MEETING = 'meeting';

    public const TYPE_TASK_COMPLETED = 'task_completed';

    public const TYPE_CREATED = 'created';

    public const TYPE_WEBHOOK = 'webhook';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'lead_id',
        'user_id',
        'type',
        'title',
        'description',
        'metadata',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function getTypeOptions(): array
    {
        return [
            self::TYPE_STATUS_CHANGE => 'Zmiana statusu',
            self::TYPE_NOTE => 'Notatka',
            self::TYPE_EMAIL_SENT => 'Email wysłany',
            self::TYPE_CALL => 'Rozmowa telefoniczna',
            self::TYPE_MEETING => 'Spotkanie',
            self::TYPE_TASK_COMPLETED => 'Zadanie wykonane',
            self::TYPE_CREATED => 'Utworzono',
            self::TYPE_WEBHOOK => 'Zdarzenie zewnętrzne',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function getTypeIcons(): array
    {
        return [
            self::TYPE_STATUS_CHANGE => 'arrow-path',
            self::TYPE_NOTE => 'document-text',
            self::TYPE_EMAIL_SENT => 'envelope',
            self::TYPE_CALL => 'phone',
            self::TYPE_MEETING => 'calendar',
            self::TYPE_TASK_COMPLETED => 'check-circle',
            self::TYPE_CREATED => 'plus-circle',
            self::TYPE_WEBHOOK => 'globe-alt',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function getTypeColors(): array
    {
        return [
            self::TYPE_STATUS_CHANGE => 'warning',
            self::TYPE_NOTE => 'gray',
            self::TYPE_EMAIL_SENT => 'info',
            self::TYPE_CALL => 'success',
            self::TYPE_MEETING => 'primary',
            self::TYPE_TASK_COMPLETED => 'success',
            self::TYPE_CREATED => 'primary',
            self::TYPE_WEBHOOK => 'gray',
        ];
    }

    /**
     * @return BelongsTo<Lead, $this>
     */
    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(webfloo_user_model());
    }
}

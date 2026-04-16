<?php

declare(strict_types=1);

namespace Webfloo\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User;

/**
 * @property int $id
 * @property int $lead_id
 * @property int|null $user_id
 * @property string $title
 * @property string|null $description
 * @property Carbon $due_at
 * @property Carbon|null $completed_at
 * @property string $priority
 * @property bool $notification_sent
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Lead $lead
 * @property-read User|null $user
 */
class LeadReminder extends Model
{
    public const PRIORITY_LOW = 'low';

    public const PRIORITY_NORMAL = 'normal';

    public const PRIORITY_HIGH = 'high';

    public const PRIORITY_URGENT = 'urgent';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'lead_id',
        'user_id',
        'title',
        'description',
        'due_at',
        'completed_at',
        'priority',
        'notification_sent',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'due_at' => 'datetime',
            'completed_at' => 'datetime',
            'notification_sent' => 'boolean',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function getPriorityOptions(): array
    {
        return [
            self::PRIORITY_LOW => 'Niski',
            self::PRIORITY_NORMAL => 'Normalny',
            self::PRIORITY_HIGH => 'Wysoki',
            self::PRIORITY_URGENT => 'Pilny',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function getPriorityColors(): array
    {
        return [
            self::PRIORITY_LOW => 'gray',
            self::PRIORITY_NORMAL => 'info',
            self::PRIORITY_HIGH => 'warning',
            self::PRIORITY_URGENT => 'danger',
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

    /**
     * @param  Builder<LeadReminder>  $query
     * @return Builder<LeadReminder>
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->whereNull('completed_at');
    }

    /**
     * @param  Builder<LeadReminder>  $query
     * @return Builder<LeadReminder>
     */
    public function scopeCompleted(Builder $query): Builder
    {
        return $query->whereNotNull('completed_at');
    }

    /**
     * @param  Builder<LeadReminder>  $query
     * @return Builder<LeadReminder>
     */
    public function scopeOverdue(Builder $query): Builder
    {
        return $query->pending()->where('due_at', '<', now());
    }

    /**
     * @param  Builder<LeadReminder>  $query
     * @return Builder<LeadReminder>
     */
    public function scopeDueToday(Builder $query): Builder
    {
        return $query->pending()->whereDate('due_at', today());
    }

    /**
     * @param  Builder<LeadReminder>  $query
     * @return Builder<LeadReminder>
     */
    public function scopeUpcoming(Builder $query, int $days = 7): Builder
    {
        return $query->pending()
            ->whereBetween('due_at', [now(), now()->addDays($days)]);
    }

    /**
     * @param  Builder<LeadReminder>  $query
     * @return Builder<LeadReminder>
     */
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function markAsCompleted(): void
    {
        $this->update(['completed_at' => now()]);

        LeadActivity::create([
            'lead_id' => $this->lead_id,
            'user_id' => auth()->id(),
            'type' => LeadActivity::TYPE_TASK_COMPLETED,
            'title' => 'Przypomnienie wykonane',
            'description' => $this->title,
        ]);
    }

    public function isOverdue(): bool
    {
        return $this->completed_at === null && $this->due_at->isPast();
    }

    public function isPending(): bool
    {
        return $this->completed_at === null;
    }
}

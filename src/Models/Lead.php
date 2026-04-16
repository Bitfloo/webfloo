<?php

declare(strict_types=1);

namespace Webfloo\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string|null $phone
 * @property string|null $company
 * @property string|null $message
 * @property string $source
 * @property string $status
 * @property int|null $assigned_to
 * @property float|null $estimated_value
 * @property string $currency
 * @property Carbon|null $last_contacted_at
 * @property Carbon|null $converted_at
 * @property string|null $notes
 * @property Carbon|null $consent_at
 * @property array<string, mixed>|null $metadata
 * @property string|null $external_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read User|null $assignee
 * @property-read Collection<int, LeadActivity> $activities
 * @property-read Collection<int, LeadReminder> $reminders
 * @property-read Collection<int, LeadTag> $tags
 */
class Lead extends Model
{
    // Status constants
    public const STATUS_NEW = 'new';

    public const STATUS_CONTACTED = 'contacted';

    public const STATUS_QUALIFIED = 'qualified';

    public const STATUS_CONVERTED = 'converted';

    public const STATUS_LOST = 'lost';

    // Source constants
    public const SOURCE_CONTACT_FORM = 'contact_form';

    public const SOURCE_NEWSLETTER = 'newsletter';

    public const SOURCE_CALCULATOR = 'calculator';

    public const SOURCE_MANUAL = 'manual';

    public const SOURCE_WEBHOOK = 'webhook';

    public const SOURCE_IMPORT = 'import';

    /**
     * Pipeline statuses (for Kanban - excludes terminal states)
     *
     * @var list<string>
     */
    public const PIPELINE_STATUSES = [
        self::STATUS_NEW,
        self::STATUS_CONTACTED,
        self::STATUS_QUALIFIED,
    ];

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'company',
        'message',
        'consent_at',
        'source',
        'status',
        'assigned_to',
        'estimated_value',
        'currency',
        'last_contacted_at',
        'converted_at',
        'notes',
        'metadata',
        'external_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'estimated_value' => 'decimal:2',
            'consent_at' => 'datetime',
            'last_contacted_at' => 'datetime',
            'converted_at' => 'datetime',
        ];
    }

    // ==================== RELATIONSHIPS ====================

    /**
     * @return BelongsTo<User, $this>
     */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(webfloo_user_model(), 'assigned_to');
    }

    /**
     * @return HasMany<LeadActivity, $this>
     */
    public function activities(): HasMany
    {
        return $this->hasMany(LeadActivity::class)->orderByDesc('created_at');
    }

    /**
     * @return HasMany<LeadReminder, $this>
     */
    public function reminders(): HasMany
    {
        return $this->hasMany(LeadReminder::class);
    }

    /**
     * @return HasMany<LeadReminder, $this>
     */
    public function pendingReminders(): HasMany
    {
        return $this->reminders()->pending()->orderBy('due_at');
    }

    /**
     * @return BelongsToMany<LeadTag, $this>
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(LeadTag::class, 'lead_lead_tag');
    }

    // ==================== SCOPES ====================

    /**
     * @param  Builder<Lead>  $query
     * @return Builder<Lead>
     */
    public function scopeNew(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_NEW);
    }

    /**
     * @param  Builder<Lead>  $query
     * @return Builder<Lead>
     */
    public function scopeContacted(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_CONTACTED);
    }

    /**
     * @param  Builder<Lead>  $query
     * @return Builder<Lead>
     */
    public function scopeQualified(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_QUALIFIED);
    }

    /**
     * @param  Builder<Lead>  $query
     * @return Builder<Lead>
     */
    public function scopeConverted(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_CONVERTED);
    }

    /**
     * @param  Builder<Lead>  $query
     * @return Builder<Lead>
     */
    public function scopeLost(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_LOST);
    }

    /**
     * @param  Builder<Lead>  $query
     * @return Builder<Lead>
     */
    public function scopeInPipeline(Builder $query): Builder
    {
        return $query->whereIn('status', self::PIPELINE_STATUSES);
    }

    /**
     * @param  Builder<Lead>  $query
     * @return Builder<Lead>
     */
    public function scopeFromSource(Builder $query, string $source): Builder
    {
        return $query->where('source', $source);
    }

    /**
     * @param  Builder<Lead>  $query
     * @return Builder<Lead>
     */
    public function scopeAssignedTo(Builder $query, int $userId): Builder
    {
        return $query->where('assigned_to', $userId);
    }

    /**
     * @param  Builder<Lead>  $query
     * @return Builder<Lead>
     */
    public function scopeUnassigned(Builder $query): Builder
    {
        return $query->whereNull('assigned_to');
    }

    // ==================== STATUS TRANSITIONS ====================

    public function transitionTo(string $newStatus): void
    {
        $oldStatus = $this->status;

        if ($oldStatus === $newStatus) {
            return;
        }

        $updates = ['status' => $newStatus];

        if ($newStatus === self::STATUS_CONVERTED) {
            $updates['converted_at'] = now();
        }

        if (in_array($newStatus, [self::STATUS_CONTACTED, self::STATUS_QUALIFIED])) {
            $updates['last_contacted_at'] = now();
        }

        $this->update($updates);

        LeadActivity::create([
            'lead_id' => $this->id,
            'user_id' => auth()->id(),
            'type' => LeadActivity::TYPE_STATUS_CHANGE,
            'title' => 'Status zmieniony',
            'description' => sprintf(
                'Status zmieniony z "%s" na "%s"',
                self::getStatusOptions()[$oldStatus] ?? $oldStatus,
                self::getStatusOptions()[$newStatus] ?? $newStatus
            ),
            'metadata' => [
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
            ],
        ]);
    }

    public function markAsContacted(): void
    {
        $this->transitionTo(self::STATUS_CONTACTED);
    }

    public function markAsQualified(): void
    {
        $this->transitionTo(self::STATUS_QUALIFIED);
    }

    public function markAsConverted(): void
    {
        $this->transitionTo(self::STATUS_CONVERTED);
    }

    public function markAsLost(): void
    {
        $this->transitionTo(self::STATUS_LOST);
    }

    // ==================== ACTIVITY HELPERS ====================

    public function addNote(string $note): LeadActivity
    {
        return LeadActivity::create([
            'lead_id' => $this->id,
            'user_id' => auth()->id(),
            'type' => LeadActivity::TYPE_NOTE,
            'title' => 'Notatka dodana',
            'description' => $note,
        ]);
    }

    public function logCall(?string $notes = null): LeadActivity
    {
        $this->update(['last_contacted_at' => now()]);

        return LeadActivity::create([
            'lead_id' => $this->id,
            'user_id' => auth()->id(),
            'type' => LeadActivity::TYPE_CALL,
            'title' => 'Rozmowa telefoniczna',
            'description' => $notes,
        ]);
    }

    public function logEmail(string $subject, ?string $body = null): LeadActivity
    {
        $this->update(['last_contacted_at' => now()]);

        return LeadActivity::create([
            'lead_id' => $this->id,
            'user_id' => auth()->id(),
            'type' => LeadActivity::TYPE_EMAIL_SENT,
            'title' => 'Email wysłany',
            'description' => $subject,
            'metadata' => [
                'subject' => $subject,
                'body' => $body,
            ],
        ]);
    }

    public function logMeeting(?string $notes = null): LeadActivity
    {
        $this->update(['last_contacted_at' => now()]);

        return LeadActivity::create([
            'lead_id' => $this->id,
            'user_id' => auth()->id(),
            'type' => LeadActivity::TYPE_MEETING,
            'title' => 'Spotkanie',
            'description' => $notes,
        ]);
    }

    public function scheduleReminder(
        string $title,
        \DateTimeInterface $dueAt,
        ?string $description = null,
        string $priority = LeadReminder::PRIORITY_NORMAL
    ): LeadReminder {
        return LeadReminder::create([
            'lead_id' => $this->id,
            'user_id' => auth()->id(),
            'title' => $title,
            'description' => $description,
            'due_at' => $dueAt,
            'priority' => $priority,
        ]);
    }

    // ==================== HELPERS ====================

    public function isInPipeline(): bool
    {
        return in_array($this->status, self::PIPELINE_STATUSES);
    }

    public function isTerminal(): bool
    {
        return in_array($this->status, [self::STATUS_CONVERTED, self::STATUS_LOST]);
    }

    public static function createFromNewsletterSubscriber(NewsletterSubscriber $subscriber): self
    {
        $lead = self::create([
            'name' => $subscriber->name ?? $subscriber->email,
            'email' => $subscriber->email,
            'source' => self::SOURCE_NEWSLETTER,
            'status' => self::STATUS_NEW,
            'metadata' => [
                'newsletter_subscribed_at' => $subscriber->subscribed_at->toIso8601String(),
                'newsletter_source' => $subscriber->source,
            ],
        ]);

        LeadActivity::create([
            'lead_id' => $lead->id,
            'type' => LeadActivity::TYPE_CREATED,
            'title' => 'Lead utworzony z newslettera',
            'description' => "Email: {$subscriber->email}",
        ]);

        return $lead;
    }

    // ==================== OPTIONS ====================

    /**
     * @return array<string, string>
     */
    public static function getStatusOptions(): array
    {
        return [
            self::STATUS_NEW => 'Nowy',
            self::STATUS_CONTACTED => 'Skontaktowany',
            self::STATUS_QUALIFIED => 'Zakwalifikowany',
            self::STATUS_CONVERTED => 'Skonwertowany',
            self::STATUS_LOST => 'Utracony',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function getPipelineStatusOptions(): array
    {
        return array_filter(
            self::getStatusOptions(),
            fn ($key) => in_array($key, self::PIPELINE_STATUSES),
            ARRAY_FILTER_USE_KEY
        );
    }

    /**
     * @return array<string, string>
     */
    public static function getSourceOptions(): array
    {
        return [
            self::SOURCE_CONTACT_FORM => 'Formularz kontaktowy',
            self::SOURCE_NEWSLETTER => 'Newsletter',
            self::SOURCE_CALCULATOR => 'Kalkulator',
            self::SOURCE_MANUAL => 'Ręcznie',
            self::SOURCE_WEBHOOK => 'Webhook',
            self::SOURCE_IMPORT => 'Import',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function getStatusColors(): array
    {
        return [
            self::STATUS_NEW => 'warning',
            self::STATUS_CONTACTED => 'info',
            self::STATUS_QUALIFIED => 'primary',
            self::STATUS_CONVERTED => 'success',
            self::STATUS_LOST => 'danger',
        ];
    }
}

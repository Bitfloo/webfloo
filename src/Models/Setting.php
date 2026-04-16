<?php

namespace Webfloo\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * @property int $id
 * @property string $key
 * @property string $group
 * @property mixed $value
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Setting extends Model
{
    /** @var array<string, mixed>|null In-memory request-scoped cache */
    protected static ?array $requestCache = null;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'key',
        'group',
        'value',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'value' => 'json',
        ];
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        if (static::$requestCache === null) {
            static::preloadAll();
        }

        $cache = static::$requestCache ?? [];

        return array_key_exists($key, $cache)
            ? ($cache[$key] ?? $default)
            : $default;
    }

    public static function set(string $key, mixed $value, string $group = 'general'): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'group' => $group]
        );

        static::$requestCache = null;
        Cache::forget('settings.all');
    }

    /**
     * @return array<string, mixed>
     */
    public static function getGroup(string $group): array
    {
        /** @var array<string, mixed> */
        return static::where('group', $group)
            ->pluck('value', 'key')
            ->toArray();
    }

    public static function clearCache(): void
    {
        Cache::forget('settings.all');
        static::$requestCache = null;
    }

    /**
     * Load all settings into memory in a single query.
     * Settings table is small (<200 rows), so loading all is efficient.
     * Cached in Redis for cross-request persistence.
     */
    public static function preloadAll(): void
    {
        $ttl = config('webfloo.settings.cache_ttl', 3600);
        $ttlValue = is_int($ttl) ? $ttl : 3600;

        /** @var array<string, mixed> $cached */
        $cached = Cache::remember('settings.all', $ttlValue, function (): array {
            $result = [];
            foreach (static::all() as $setting) {
                $result[$setting->key] = $setting->value;
            }

            return $result;
        });

        static::$requestCache = $cached;
    }

    /**
     * Flush request-scoped cache. Call between requests (Octane/testing).
     */
    public static function flushRequestCache(): void
    {
        static::$requestCache = null;
    }
}

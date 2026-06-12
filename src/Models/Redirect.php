<?php

namespace Webfloo\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $from_path
 * @property string $to_path
 * @property int $status_code
 * @property bool $is_active
 * @property int $hits_count
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Redirect extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'from_path',
        'to_path',
        'status_code',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status_code' => 'integer',
            'is_active' => 'boolean',
            'hits_count' => 'integer',
        ];
    }

    /**
     * Find the active redirect for a request path, or null.
     */
    public static function forPath(string $path): ?self
    {
        return static::query()
            ->where('from_path', static::normalizePath($path))
            ->where('is_active', true)
            ->first();
    }

    /**
     * Canonical storage form: leading slash, no trailing slash, "/" for root.
     * Single source of the path format — middleware and observer both use it.
     */
    public static function normalizePath(string $path): string
    {
        $trimmed = trim($path, '/');

        return $trimmed === '' ? '/' : '/'.$trimmed;
    }
}

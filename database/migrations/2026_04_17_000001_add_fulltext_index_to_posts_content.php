<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * D4 — FULLTEXT index na posts content (PARTIAL).
 *
 * Discovery during implementation: `posts.title`, `posts.excerpt`,
 * `posts.content` są wszystkie JSON columns (spatie/laravel-translatable
 * multi-locale storage). MySQL FULLTEXT nie działa bezpośrednio na JSON —
 * wymaga generated columns per locale.
 *
 * Pragmatic decyzja: index tylko na `slug` (VARCHAR, non-translatable,
 * admin search hit) + odroczenie full-content indeksu do osobnego
 * migration'u D4.1 używającego generated columns pattern.
 *
 * D4.1 scope (TODO):
 *   ALTER TABLE posts ADD COLUMN content_pl TEXT GENERATED ALWAYS
 *     AS (JSON_UNQUOTE(JSON_EXTRACT(content, '$.pl'))) STORED,
 *     ADD COLUMN content_en TEXT GENERATED ALWAYS
 *     AS (JSON_UNQUOTE(JSON_EXTRACT(content, '$.en'))) STORED;
 *   ALTER TABLE posts ADD FULLTEXT (content_pl), ADD FULLTEXT (content_en);
 *
 * Refs: docs/plans/webfloo-extraction/02-extraction-plan.md D4.
 * Idempotent + skip dla non-MySQL (SQLite in tests).
 */
return new class extends Migration
{
    private const INDEX_NAME = 'posts_slug_fulltext';

    public function up(): void
    {
        if (! $this->isMySql()) {
            return;
        }

        if ($this->indexExists()) {
            return;
        }

        Schema::table('posts', function ($table): void {
            $table->fullText('slug', self::INDEX_NAME);
        });
    }

    public function down(): void
    {
        if (! $this->isMySql()) {
            return;
        }

        if (! $this->indexExists()) {
            return;
        }

        Schema::table('posts', function ($table): void {
            $table->dropFullText(self::INDEX_NAME);
        });
    }

    private function isMySql(): bool
    {
        return DB::connection()->getDriverName() === 'mysql';
    }

    private function indexExists(): bool
    {
        return collect(DB::select('SHOW INDEX FROM posts'))
            ->contains(fn (object $row): bool => ($row->Key_name ?? null) === self::INDEX_NAME);
    }
};

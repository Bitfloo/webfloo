<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * FULLTEXT na posts.slug. title/excerpt/content są JSON (translatable) —
 * FULLTEXT na nich wymaga generated columns per locale (D4.1, deferred).
 * Idempotent + no-op na non-MySQL.
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

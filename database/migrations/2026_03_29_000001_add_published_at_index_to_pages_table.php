<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Idempotent: index may already exist from corrected original migration
        if ($this->hasIndex('pages', 'pages_published_at_index')) {
            return;
        }

        Schema::table('pages', function (Blueprint $table) {
            $table->index('published_at');
        });
    }

    public function down(): void
    {
        if (! $this->hasIndex('pages', 'pages_published_at_index')) {
            return;
        }

        Schema::table('pages', function (Blueprint $table) {
            $table->dropIndex(['published_at']);
        });
    }

    private function hasIndex(string $table, string $indexName): bool
    {
        // SHOW INDEX is MySQL-only; on SQLite fall back to PRAGMA index_list.
        if (DB::connection()->getDriverName() !== 'mysql') {
            $indexes = DB::select("PRAGMA index_list({$table})");

            return collect($indexes)->contains(fn (object $row): bool => ($row->name ?? null) === $indexName);
        }

        /** @var list<object{Key_name: string}> $indexes */
        $indexes = DB::select("SHOW INDEX FROM `{$table}` WHERE `Key_name` = ?", [$indexName]);

        return $indexes !== [];
    }
};

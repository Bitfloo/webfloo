<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Translatable columns that must be JSON.
     * post_categories already fixed in 2026_03_29_000003.
     *
     * @var array<string, list<string>>
     */
    private array $translatableColumns = [
        'services' => ['title', 'description'],
        'projects' => ['title', 'excerpt', 'description', 'challenge', 'solution', 'results', 'testimonial_quote'],
        'faqs' => ['question', 'answer'],
        'testimonials' => ['content', 'role', 'company'],
        'pages' => ['title', 'meta_title', 'meta_description'],
        'posts' => ['title', 'excerpt', 'content', 'meta_title', 'meta_description'],
        'menu_items' => ['label'],
    ];

    public function up(): void
    {
        // 1. Fix translatable columns: varchar/text -> json (idempotent)
        foreach ($this->translatableColumns as $table => $columns) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            $columnsNeedingChange = $this->getNonJsonColumns($table, $columns);

            if ($columnsNeedingChange === []) {
                continue;
            }

            // Wrap existing plain-text values in {"pl": "value"} JSON
            DB::transaction(function () use ($table, $columnsNeedingChange): void {
                foreach (DB::table($table)->get() as $row) {
                    $updates = [];
                    foreach ($columnsNeedingChange as $column) {
                        $value = $row->{$column} ?? null;
                        if (is_string($value) && $value !== '' && ! $this->isJson($value)) {
                            $updates[$column] = json_encode(['pl' => $value], JSON_UNESCAPED_UNICODE);
                        }
                    }
                    if ($updates !== []) {
                        DB::table($table)->where('id', $row->id)->update($updates);
                    }
                }
            });

            // ALTER columns to JSON
            Schema::table($table, function (Blueprint $blueprint) use ($columnsNeedingChange) {
                foreach ($columnsNeedingChange as $column) {
                    $blueprint->json($column)->nullable()->change();
                }
            });
        }

        // 2. Add missing indexes (idempotent -- skip if exists)

        // leads.email -- primary search key, no index
        if (Schema::hasTable('leads') && ! $this->hasIndex('leads', 'leads_email_index')) {
            Schema::table('leads', function (Blueprint $table) {
                $table->index('email');
            });
        }

        // pages ['status', 'published_at'] -- posts has it, pages didn't
        if (Schema::hasTable('pages') && ! $this->hasIndex('pages', 'pages_status_published_at_index')) {
            Schema::table('pages', function (Blueprint $table) {
                $table->index(['status', 'published_at']);
            });
        }

        // newsletter_subscribers.is_active -- used in scopeActive()
        if (Schema::hasTable('newsletter_subscribers') && ! $this->hasIndex('newsletter_subscribers', 'newsletter_subscribers_is_active_index')) {
            Schema::table('newsletter_subscribers', function (Blueprint $table) {
                $table->index('is_active');
            });
        }

        // 3. Standardize sort_order: unsignedInteger -> integer (posts, post_categories)
        if (Schema::hasTable('posts') && $this->isColumnUnsigned('posts', 'sort_order')) {
            Schema::table('posts', function (Blueprint $table) {
                $table->integer('sort_order')->default(0)->change();
            });
        }

        if (Schema::hasTable('post_categories') && $this->isColumnUnsigned('post_categories', 'sort_order')) {
            Schema::table('post_categories', function (Blueprint $table) {
                $table->integer('sort_order')->default(0)->change();
            });
        }
    }

    public function down(): void
    {
        // Reverse sort_order standardization
        if (Schema::hasTable('post_categories')) {
            Schema::table('post_categories', function (Blueprint $table) {
                $table->unsignedInteger('sort_order')->default(0)->change();
            });
        }

        if (Schema::hasTable('posts')) {
            Schema::table('posts', function (Blueprint $table) {
                $table->unsignedInteger('sort_order')->default(0)->change();
            });
        }

        // Remove added indexes
        if (Schema::hasTable('newsletter_subscribers') && $this->hasIndex('newsletter_subscribers', 'newsletter_subscribers_is_active_index')) {
            Schema::table('newsletter_subscribers', function (Blueprint $table) {
                $table->dropIndex(['is_active']);
            });
        }

        if (Schema::hasTable('pages') && $this->hasIndex('pages', 'pages_status_published_at_index')) {
            Schema::table('pages', function (Blueprint $table) {
                $table->dropIndex(['status', 'published_at']);
            });
        }

        if (Schema::hasTable('leads') && $this->hasIndex('leads', 'leads_email_index')) {
            Schema::table('leads', function (Blueprint $table) {
                $table->dropIndex(['email']);
            });
        }

        // Reverse translatable columns: json -> text (WARNING: loses non-PL translations)
        foreach ($this->translatableColumns as $table => $columns) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) use ($columns) {
                foreach ($columns as $column) {
                    $blueprint->text($column)->nullable()->change();
                }
            });

            DB::transaction(function () use ($table, $columns): void {
                foreach (DB::table($table)->get() as $row) {
                    $updates = [];
                    foreach ($columns as $column) {
                        $value = $row->{$column} ?? null;
                        if (is_string($value) && $this->isJson($value)) {
                            /** @var array<string, string>|null $decoded */
                            $decoded = json_decode($value, true);
                            if (is_array($decoded) && isset($decoded['pl']) && is_string($decoded['pl'])) {
                                $updates[$column] = $decoded['pl'];
                            }
                        }
                    }
                    if ($updates !== []) {
                        DB::table($table)->where('id', $row->id)->update($updates);
                    }
                }
            });
        }
    }

    /**
     * Filter columns that are NOT already JSON type.
     *
     * @param  list<string>  $columns
     * @return list<string>
     */
    private function getNonJsonColumns(string $table, array $columns): array
    {
        $nonJson = [];
        foreach ($columns as $column) {
            if (! Schema::hasColumn($table, $column)) {
                continue;
            }
            if (! $this->isColumnJson($table, $column)) {
                $nonJson[] = $column;
            }
        }

        return $nonJson;
    }

    private function isColumnJson(string $table, string $column): bool
    {
        /** @var list<object{Type: string, Field: string}> $result */
        $result = DB::select("SHOW COLUMNS FROM `{$table}` WHERE `Field` = ?", [$column]);

        if ($result === []) {
            return false;
        }

        return strtolower($result[0]->Type) === 'json';
    }

    private function isColumnUnsigned(string $table, string $column): bool
    {
        /** @var list<object{Type: string, Field: string}> $result */
        $result = DB::select("SHOW COLUMNS FROM `{$table}` WHERE `Field` = ?", [$column]);

        if ($result === []) {
            return false;
        }

        return str_contains(strtolower($result[0]->Type), 'unsigned');
    }

    private function hasIndex(string $table, string $indexName): bool
    {
        /** @var list<object{Key_name: string}> $indexes */
        $indexes = DB::select("SHOW INDEX FROM `{$table}` WHERE `Key_name` = ?", [$indexName]);

        return $indexes !== [];
    }

    private function isJson(string $value): bool
    {
        if ($value === '' || ($value[0] !== '{' && $value[0] !== '[')) {
            return false;
        }
        json_decode($value);

        return json_last_error() === JSON_ERROR_NONE;
    }
};

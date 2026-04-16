<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Idempotent: columns may already be json from corrected original migration
        $columnsToChange = [];
        foreach (['name', 'description'] as $column) {
            if (! $this->isColumnJson('post_categories', $column)) {
                $columnsToChange[] = $column;
            }
        }

        if ($columnsToChange === []) {
            return;
        }

        Schema::table('post_categories', function (Blueprint $table) use ($columnsToChange) {
            foreach ($columnsToChange as $column) {
                $table->json($column)->nullable()->change();
            }
        });
    }

    public function down(): void
    {
        Schema::table('post_categories', function (Blueprint $table) {
            $table->string('name', 100)->change();
            $table->text('description')->nullable()->change();
        });
    }

    private function isColumnJson(string $table, string $column): bool
    {
        /** @var list<object{Type: string}> $result */
        $result = DB::select("SHOW COLUMNS FROM `{$table}` WHERE `Field` = ?", [$column]);

        if ($result === []) {
            return false;
        }

        return strtolower($result[0]->Type) === 'json';
    }
};

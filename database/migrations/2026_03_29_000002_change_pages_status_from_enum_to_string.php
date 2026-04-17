<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Idempotent: column may already be varchar from corrected original migration
        if ($this->isColumnType('pages', 'status', 'varchar')) {
            return;
        }

        Schema::table('pages', function (Blueprint $table) {
            $table->string('status', 20)->default('draft')->change();
        });
    }

    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft')->change();
        });
    }

    private function isColumnType(string $table, string $column, string $typePrefix): bool
    {
        // SHOW COLUMNS is MySQL-only; on SQLite the schema is always a string type.
        if (DB::connection()->getDriverName() !== 'mysql') {
            return true;
        }

        /** @var list<object{Type: string}> $result */
        $result = DB::select("SHOW COLUMNS FROM `{$table}` WHERE `Field` = ?", [$column]);

        if ($result === []) {
            return false;
        }

        return str_starts_with(strtolower($result[0]->Type), strtolower($typePrefix));
    }
};

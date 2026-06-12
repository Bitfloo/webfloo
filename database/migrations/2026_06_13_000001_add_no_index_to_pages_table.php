<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Page uses HasSeo, which exposes no_index, but the pages table never
     * got the column (posts did) — getSeoData() silently fell back to
     * false and the admin toggle could not persist.
     */
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table): void {
            $table->boolean('no_index')->default(false)->after('meta_image');
        });
    }

    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table): void {
            $table->dropColumn('no_index');
        });
    }
};

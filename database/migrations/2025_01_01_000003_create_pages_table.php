<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->json('title');
            $table->string('slug')->unique();
            $table->json('content')->nullable();
            $table->string('template')->default('default');
            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('pages')
                ->cascadeOnDelete();

            // SEO meta fields
            $table->json('meta_title')->nullable();
            $table->json('meta_description')->nullable();
            $table->string('meta_image')->nullable();

            // Publishing fields
            $table->string('status', 20)->default('draft');
            $table->timestamp('published_at')->nullable();

            // Sorting
            $table->integer('sort_order')->default(0);

            $table->timestamps();
            $table->softDeletes();

            // Indexes for common queries
            $table->index('parent_id');
            $table->index('status');
            $table->index(['status', 'published_at']);
            $table->index('published_at');
            $table->index('template');
            $table->index('sort_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            // Case Study content
            $table->json('challenge')->nullable()->after('description');
            $table->json('solution')->nullable()->after('challenge');
            $table->json('results')->nullable()->after('solution');

            // Metrics & achievements
            $table->json('metrics')->nullable()->after('technologies');
            $table->json('achievements')->nullable()->after('metrics');
            $table->json('gallery')->nullable()->after('image');

            // Testimonial (embedded for case study specific quote)
            $table->json('testimonial_quote')->nullable()->after('achievements');
            $table->string('testimonial_author', 100)->nullable()->after('testimonial_quote');
            $table->string('testimonial_role', 100)->nullable()->after('testimonial_author');
            $table->string('testimonial_company', 100)->nullable()->after('testimonial_role');
            $table->string('testimonial_avatar')->nullable()->after('testimonial_company');

            // Project details
            $table->string('video_url')->nullable()->after('url');
            $table->string('duration', 50)->nullable()->after('video_url');
            $table->string('team_size', 50)->nullable()->after('duration');
            $table->string('industry', 100)->nullable()->after('category');

            // Index for industry filtering
            $table->index('industry');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropIndex(['industry']);

            $table->dropColumn([
                'challenge',
                'solution',
                'results',
                'metrics',
                'achievements',
                'gallery',
                'testimonial_quote',
                'testimonial_author',
                'testimonial_role',
                'testimonial_company',
                'testimonial_avatar',
                'video_url',
                'duration',
                'team_size',
                'industry',
            ]);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('post_related', function (Blueprint $table) {
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('related_post_id')->constrained('posts')->cascadeOnDelete();
            $table->primary(['post_id', 'related_post_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_related');
    }
};

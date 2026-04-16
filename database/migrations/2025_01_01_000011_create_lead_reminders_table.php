<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lead_reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->dateTime('due_at');
            $table->dateTime('completed_at')->nullable();
            $table->string('priority', 20)->default('normal');
            $table->boolean('notification_sent')->default(false);
            $table->timestamps();

            $table->index(['user_id', 'due_at']);
            $table->index(['lead_id', 'completed_at']);
            $table->index('due_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_reminders');
    }
};

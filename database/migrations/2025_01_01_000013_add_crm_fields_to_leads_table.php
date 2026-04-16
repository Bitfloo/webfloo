<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->foreignId('assigned_to')->nullable()->after('status')
                ->constrained('users')->nullOnDelete();
            $table->decimal('estimated_value', 12, 2)->nullable()->after('assigned_to');
            $table->string('currency', 3)->default('PLN')->after('estimated_value');
            $table->dateTime('last_contacted_at')->nullable()->after('currency');
            $table->dateTime('converted_at')->nullable()->after('last_contacted_at');
            $table->string('external_id', 100)->nullable()->after('metadata');

            $table->index('assigned_to');
            $table->index('last_contacted_at');
            $table->index('external_id');
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropForeign(['assigned_to']);
            $table->dropIndex(['assigned_to']);
            $table->dropIndex(['last_contacted_at']);
            $table->dropIndex(['external_id']);

            $table->dropColumn([
                'assigned_to',
                'estimated_value',
                'currency',
                'last_contacted_at',
                'converted_at',
                'external_id',
            ]);
        });
    }
};

<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Dodaj index na leads.converted_at.
 *
 * Powód: LeadConversionChart + CRM dashboard filtrują po `converted_at`
 * w 6-miesięcznym window (`whereBetween`) + status='converted'. Bez indeksu
 * każdy render dashboardu = full table scan po leads. Wraz ze wzrostem
 * tabeli (kampanie, import CSV) koszt rośnie liniowo.
 *
 * performance-auditor HIGH priority finding.
 *
 * Idempotent — sprawdzamy istnienie indeksu przed dodaniem (MySQL-specific).
 */
return new class extends Migration
{
    public function up(): void
    {
        $hasIndex = collect(DB::select('SHOW INDEX FROM leads'))
            ->contains(fn (object $row): bool => ($row->Key_name ?? null) === 'leads_converted_at_index');

        if ($hasIndex) {
            return;
        }

        Schema::table('leads', function (Blueprint $table): void {
            $table->index('converted_at', 'leads_converted_at_index');
        });
    }

    public function down(): void
    {
        $hasIndex = collect(DB::select('SHOW INDEX FROM leads'))
            ->contains(fn (object $row): bool => ($row->Key_name ?? null) === 'leads_converted_at_index');

        if (! $hasIndex) {
            return;
        }

        Schema::table('leads', function (Blueprint $table): void {
            $table->dropIndex('leads_converted_at_index');
        });
    }
};

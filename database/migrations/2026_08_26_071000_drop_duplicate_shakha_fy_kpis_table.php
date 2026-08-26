<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Prefer shakha_annual_kpis (full column set used by the KPI feature).
        Schema::dropIfExists('shakha_fy_kpis');
    }

    public function down(): void
    {
        // no-op
    }
};

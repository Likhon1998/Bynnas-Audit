<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audit_reports', function (Blueprint $table) {
            $table->foreignId('monthly_assignment_id')
                ->nullable()
                ->after('shakha_id')
                ->constrained('monthly_assignments')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('audit_reports', function (Blueprint $table) {
            $table->dropConstrainedForeignId('monthly_assignment_id');
        });
    }
};

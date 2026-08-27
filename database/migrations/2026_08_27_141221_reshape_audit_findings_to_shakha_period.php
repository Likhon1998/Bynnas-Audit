<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Move findings off audit_reports onto shakha + month + year
     * (Excel X-axis cell keyed by branch × rule × period).
     */
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('audit_findings');
        Schema::enableForeignKeyConstraints();

        Schema::create('audit_findings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shakha_id')->constrained('shakhas')->cascadeOnDelete();
            $table->foreignId('audit_indicator_id')->constrained('audit_indicators')->cascadeOnDelete();
            $table->unsignedTinyInteger('audit_month');
            $table->unsignedSmallInteger('audit_year');
            $table->decimal('amount', 15, 2)->nullable();
            $table->unsignedInteger('sample_size_checked')->nullable();
            $table->unsignedInteger('irregularity_count')->nullable();
            $table->text('observation')->nullable();
            $table->string('responsible_staff_name')->nullable();
            $table->timestamps();

            $table->unique(
                ['shakha_id', 'audit_indicator_id', 'audit_month', 'audit_year'],
                'audit_findings_shakha_indicator_period_unique'
            );
            $table->index(['audit_month', 'audit_year']);
            $table->index('audit_indicator_id');
        });
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('audit_findings');
        Schema::enableForeignKeyConstraints();

        Schema::create('audit_findings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('audit_report_id')->constrained('audit_reports')->cascadeOnDelete();
            $table->foreignId('audit_indicator_id')->constrained('audit_indicators')->cascadeOnDelete();
            $table->decimal('amount', 15, 2)->nullable();
            $table->unsignedInteger('sample_size_checked')->nullable();
            $table->unsignedInteger('irregularity_count')->nullable();
            $table->text('observation')->nullable();
            $table->string('responsible_staff_name')->nullable();
            $table->timestamps();

            $table->unique(['audit_report_id', 'audit_indicator_id']);
            $table->index('audit_indicator_id');
        });
    }
};

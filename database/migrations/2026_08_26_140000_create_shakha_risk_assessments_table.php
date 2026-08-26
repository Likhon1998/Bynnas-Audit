<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shakha_risk_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shakha_id')->constrained('shakhas')->cascadeOnDelete();

            $table->unsignedTinyInteger('assessment_month');
            $table->unsignedSmallInteger('assessment_year');
            $table->unsignedInteger('distance_from_area_office_km')->default(0);

            $table->decimal('total_income', 15, 2)->default(0);
            $table->decimal('total_expenditure', 15, 2)->default(0);
            $table->decimal('write_off_principal_amount', 15, 2)->default(0);
            $table->decimal('savings_adjustment_amount', 15, 2)->default(0);
            $table->decimal('overdue_principal_31_365_days', 15, 2)->default(0);

            $table->boolean('has_both_bm_and_abm')->default(false);
            $table->boolean('special_audit_last_two_years')->default(false);

            $table->unsignedInteger('total_weighted_score')->default(0);
            $table->string('risk_category', 50);

            $table->timestamps();

            $table->unique(
                ['shakha_id', 'assessment_month', 'assessment_year'],
                'shakha_risk_assessments_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shakha_risk_assessments');
    }
};

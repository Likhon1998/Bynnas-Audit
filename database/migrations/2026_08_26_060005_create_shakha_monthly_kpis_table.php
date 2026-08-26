<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shakha_monthly_kpis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shakha_id')->constrained('shakhas')->cascadeOnDelete();

            $table->unsignedTinyInteger('report_month');
            $table->unsignedSmallInteger('report_year');

            $table->unsignedInteger('total_samities')->default(0);
            $table->unsignedInteger('total_members')->default(0);
            $table->unsignedInteger('total_borrowers')->default(0);
            $table->unsignedInteger('total_od_borrowers')->default(0);
            $table->unsignedInteger('monthly_members_admitted')->default(0);
            $table->unsignedInteger('monthly_members_dropout')->default(0);
            $table->unsignedInteger('field_officer_count')->default(0);

            $table->decimal('savings_balance', 15, 2)->default(0);
            $table->decimal('loan_outstanding', 15, 2)->default(0);
            $table->decimal('total_od_taka', 15, 2)->default(0);
            $table->decimal('monthly_savings_collection', 15, 2)->default(0);
            $table->decimal('monthly_savings_withdrawal', 15, 2)->default(0);
            $table->decimal('monthly_disbursement_amount', 15, 2)->default(0);
            $table->decimal('monthly_loan_recovery', 15, 2)->default(0);
            $table->decimal('monthly_current_recovery', 15, 2)->default(0);
            $table->decimal('monthly_recoverable', 15, 2)->default(0);
            $table->decimal('due_loanee_loan_outstanding', 15, 2)->default(0);

            $table->timestamps();

            $table->unique(['shakha_id', 'report_month', 'report_year'], 'shakha_monthly_kpis_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shakha_monthly_kpis');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shakhas', function (Blueprint $table) {
            $table->date('opening_date')->nullable()->after('status');
            $table->string('focal_person_name')->nullable()->after('opening_date');
        });

        Schema::create('shakha_annual_kpis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shakha_id')->constrained('shakhas')->cascadeOnDelete();
            $table->string('fy_label', 9); // e.g. 2025-2026

            // Snapshot / counts
            $table->unsignedInteger('fo_count')->default(0);
            $table->unsignedInteger('total_samities')->default(0);
            $table->unsignedInteger('total_members')->default(0);
            $table->unsignedInteger('total_borrowers')->default(0);
            $table->unsignedInteger('total_od_borrowers')->default(0);
            $table->unsignedInteger('fy_members_admission')->default(0);
            $table->unsignedInteger('fy_members_dropout')->default(0);
            $table->unsignedInteger('fy_disbursement_borrowers')->default(0);
            $table->unsignedInteger('fy_fully_repayment_borrowers')->default(0);

            // Money
            $table->decimal('fy_savings_collection', 15, 2)->default(0);
            $table->decimal('fy_savings_withdrawal', 15, 2)->default(0);
            $table->decimal('savings_balance', 15, 2)->default(0);
            $table->decimal('fy_disbursement_amount', 15, 2)->default(0);
            $table->decimal('fy_loan_recovery', 15, 2)->default(0);
            $table->decimal('loan_outstanding', 15, 2)->default(0);
            $table->decimal('recoverable', 15, 2)->default(0);
            $table->decimal('current_recovery', 15, 2)->default(0);
            $table->decimal('due_recovery', 15, 2)->default(0);
            $table->decimal('total_od_taka', 15, 2)->default(0);
            $table->decimal('due_loanee_loan_outstanding', 15, 2)->default(0);
            $table->decimal('own_fund_until_prior_june', 15, 2)->default(0);
            $table->decimal('surplus_deficit_fy', 15, 2)->default(0);
            $table->decimal('new_due', 15, 2)->default(0);
            $table->decimal('due_increase_this_month', 15, 2)->default(0);

            $table->timestamps();

            $table->unique(['shakha_id', 'fy_label'], 'shakha_annual_kpis_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shakha_annual_kpis');

        Schema::table('shakhas', function (Blueprint $table) {
            $table->dropColumn(['opening_date', 'focal_person_name']);
        });
    }
};

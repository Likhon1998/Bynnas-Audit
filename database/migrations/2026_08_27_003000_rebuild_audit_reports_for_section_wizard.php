<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('audit_findings');
        Schema::dropIfExists('audit_reports');

        Schema::create('audit_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shakha_id')->constrained('shakhas')->cascadeOnDelete();
            $table->string('status')->default('draft'); // draft|completed

            // Cover page (page 1)
            $table->string('memo_no')->nullable();
            $table->date('report_date')->nullable();
            $table->string('control_rating')->nullable(); // Satisfactory, Minor, Medium, Major, Unsatisfactory
            $table->string('shakha_display_name')->nullable();
            $table->string('area_display_name')->nullable();
            $table->string('audit_period_label')->nullable(); // নিরীক্ষাকাল text
            $table->date('audit_start_date')->nullable();
            $table->date('audit_end_date')->nullable();
            $table->unsignedSmallInteger('working_days')->nullable();
            $table->string('period_scope')->nullable(); // সময়ের উপর ...
            $table->date('draft_sent_date')->nullable();
            $table->date('comments_received_date')->nullable();
            $table->string('auditor_name')->nullable();
            $table->string('auditor_designation')->nullable();

            // Future pages as JSON blobs keyed by page slug
            $table->json('pages_data')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_reports');
    }
};

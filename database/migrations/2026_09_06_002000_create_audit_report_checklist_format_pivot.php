<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_report_checklist_format', function (Blueprint $table) {
            $table->id();
            $table->foreignId('audit_report_id')->constrained('audit_reports')->cascadeOnDelete();
            $table->foreignId('audit_checklist_format_id')->constrained('audit_checklist_formats')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['audit_report_id', 'audit_checklist_format_id'], 'arcf_report_format_uq');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_report_checklist_format');
    }
};

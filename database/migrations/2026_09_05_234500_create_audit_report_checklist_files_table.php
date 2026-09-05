<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('audit_report_checklist_items');

        Schema::create('audit_report_checklist_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('audit_report_id')->constrained('audit_reports')->cascadeOnDelete();
            $table->string('original_name');
            $table->string('stored_path');
            $table->string('mime_type', 120)->nullable();
            $table->unsignedBigInteger('size_bytes')->default(0);
            $table->timestamps();

            $table->index('audit_report_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_report_checklist_files');

        Schema::create('audit_report_checklist_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('audit_report_id')->constrained('audit_reports')->cascadeOnDelete();
            $table->string('title');
            $table->boolean('is_done')->default(false);
            $table->text('notes')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['audit_report_id', 'sort_order']);
            $table->index(['audit_report_id', 'is_done']);
        });
    }
};

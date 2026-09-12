<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_report_review_annotations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('audit_report_id')->constrained('audit_reports')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('color', 20)->default('yellow');
            $table->text('quote');
            $table->string('prefix', 255)->nullable();
            $table->string('suffix', 255)->nullable();
            $table->text('body')->nullable();
            $table->timestamps();

            $table->index(['audit_report_id', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_report_review_annotations');
    }
};

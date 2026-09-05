<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_checklist_formats', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('format_number')->unique();
            $table->string('code', 40)->unique();
            $table->string('heading');
            $table->string('org_name')->default('ডিএসকে');
            $table->string('dept_name')->default('অভ্যন্তরীণ নিরীক্ষা ও পরিদর্শন বিভাগ');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('heading');
        });

        Schema::create('audit_checklist_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('audit_checklist_format_id')->constrained('audit_checklist_formats')->cascadeOnDelete();
            $table->string('heading');
            $table->string('shakha_name')->nullable();
            $table->string('audit_period')->nullable();
            $table->json('payload')->nullable();
            $table->text('summary')->nullable();
            $table->string('status', 20)->default('draft');
            $table->timestamp('saved_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'audit_checklist_format_id'], 'acs_user_format_idx');
            $table->index('heading');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_checklist_submissions');
        Schema::dropIfExists('audit_checklist_formats');
    }
};

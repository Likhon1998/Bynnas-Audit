<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('donor')->nullable();
            $table->boolean('is_pksf')->default(false);
            $table->boolean('is_maternity')->default(false);
            $table->boolean('has_project_audit')->default(true);
            $table->boolean('has_project_monitoring')->default(true);
            $table->string('status', 20)->default('active');
            $table->timestamps();
        });

        Schema::create('project_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('division')->nullable();
            $table->string('status', 20)->default('active');
            $table->timestamps();

            $table->unique(['project_id', 'name']);
        });

        Schema::create('hq_departments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('status', 20)->default('active');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('audit_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('fy_label')->unique();
            $table->date('start_date');
            $table->date('end_date');
            $table->string('status', 20)->default('draft');
            $table->timestamp('generated_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('audit_policies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('audit_plan_id')->constrained()->cascadeOnDelete();
            $table->string('category', 40);
            $table->unsignedTinyInteger('frequency_per_year')->default(1);
            $table->unsignedTinyInteger('interval_months')->nullable();
            $table->string('pattern', 40)->default('interval');
            $table->json('custom_month_indexes')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['audit_plan_id', 'category']);
        });

        Schema::create('plan_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('audit_plan_id')->constrained()->cascadeOnDelete();
            $table->string('category', 40);
            $table->string('schedulable_type');
            $table->unsignedBigInteger('schedulable_id');
            $table->unsignedTinyInteger('month_index');
            $table->date('planned_date');
            $table->unsignedTinyInteger('occurrence')->default(1);
            $table->string('status', 20)->default('planned');
            $table->boolean('is_manual')->default(false);
            $table->foreignId('auditor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->index(['audit_plan_id', 'category', 'month_index']);
            $table->index(['schedulable_type', 'schedulable_id']);
            $table->unique(
                ['audit_plan_id', 'category', 'schedulable_type', 'schedulable_id', 'month_index', 'occurrence'],
                'plan_schedules_unique_occurrence'
            );
        });

        Schema::create('plan_executions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_schedule_id')->constrained()->cascadeOnDelete();
            $table->date('actual_date')->nullable();
            $table->string('status', 20)->default('pending');
            $table->text('remarks')->nullable();
            $table->foreignId('completed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plan_executions');
        Schema::dropIfExists('plan_schedules');
        Schema::dropIfExists('audit_policies');
        Schema::dropIfExists('audit_plans');
        Schema::dropIfExists('hq_departments');
        Schema::dropIfExists('project_locations');
        Schema::dropIfExists('projects');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('category_map', 40)->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('monthly_work_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('audit_plan_id')->constrained('audit_plans')->cascadeOnDelete();
            $table->string('fy_label', 20);
            $table->unsignedTinyInteger('month_index');
            $table->string('category', 40);
            $table->foreignId('activity_type_id')->constrained('activity_types')->restrictOnDelete();
            $table->nullableMorphs('schedulable');
            $table->foreignId('plan_schedule_id')->nullable()->constrained('plan_schedules')->nullOnDelete();
            $table->string('source', 20)->default('yearly');
            $table->string('status', 20)->default('unassigned');
            $table->string('entity_label')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(
                ['audit_plan_id', 'month_index', 'category', 'schedulable_type', 'schedulable_id', 'source'],
                'monthly_work_items_natural_unique'
            );
            $table->index(['fy_label', 'month_index', 'status']);
        });

        Schema::create('monthly_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('monthly_work_item_id')->constrained('monthly_work_items')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->restrictOnDelete();
            $table->date('visit_date')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->unsignedSmallInteger('duration_days')->default(1);
            $table->string('duration_mode', 20)->default('calendar');
            $table->string('purpose')->nullable();
            $table->text('remarks')->nullable();
            $table->date('last_audit_upto')->nullable();
            $table->boolean('last_audit_upto_override')->default(false);
            $table->boolean('is_override_conflict')->default(false);
            $table->date('original_start_date')->nullable();
            $table->date('original_end_date')->nullable();
            $table->text('reschedule_reason')->nullable();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['employee_id', 'start_date', 'end_date']);
        });

        Schema::create('visit_executions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('monthly_assignment_id')->constrained('monthly_assignments')->cascadeOnDelete();
            $table->string('status', 20)->default('planned');
            $table->date('actual_start_date')->nullable();
            $table->date('actual_end_date')->nullable();
            $table->unsignedSmallInteger('actual_duration_days')->nullable();
            $table->foreignId('actual_employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique('monthly_assignment_id');
        });

        Schema::create('assignment_status_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('monthly_assignment_id')->constrained('monthly_assignments')->cascadeOnDelete();
            $table->string('from_status', 40)->nullable();
            $table->string('to_status', 40);
            $table->text('reason')->nullable();
            $table->json('meta')->nullable();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assignment_status_logs');
        Schema::dropIfExists('visit_executions');
        Schema::dropIfExists('monthly_assignments');
        Schema::dropIfExists('monthly_work_items');
        Schema::dropIfExists('activity_types');
    }
};

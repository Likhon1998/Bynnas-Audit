<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_reviewer_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('auditor_user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->foreignId('reviewer_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('reviewer_user_id');
        });

        Schema::table('audit_reports', function (Blueprint $table) {
            $table->foreignId('reviewer_user_id')->nullable()->after('user_id')->constrained('users')->nullOnDelete();
            $table->timestamp('submitted_for_review_at')->nullable()->after('completed_at');
            $table->timestamp('reviewed_at')->nullable()->after('submitted_for_review_at');
            $table->unsignedSmallInteger('review_round')->default(0)->after('reviewed_at');
            $table->boolean('review_cc_superadmin')->default(false)->after('review_round');
        });

        Schema::create('audit_report_review_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('audit_report_id')->constrained('audit_reports')->cascadeOnDelete();
            $table->foreignId('actor_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('action', 32);
            $table->text('body')->nullable();
            $table->timestamps();

            $table->index(['audit_report_id', 'created_at']);
            $table->index('action');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_report_review_events');

        Schema::table('audit_reports', function (Blueprint $table) {
            $table->dropConstrainedForeignId('reviewer_user_id');
            $table->dropColumn([
                'submitted_for_review_at',
                'reviewed_at',
                'review_round',
                'review_cc_superadmin',
            ]);
        });

        Schema::dropIfExists('audit_reviewer_assignments');
    }
};

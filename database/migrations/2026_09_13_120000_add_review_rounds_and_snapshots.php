<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audit_report_review_events', function (Blueprint $table) {
            if (! Schema::hasColumn('audit_report_review_events', 'review_round')) {
                $table->unsignedSmallInteger('review_round')->default(1)->after('action');
            }
            if (! Schema::hasColumn('audit_report_review_events', 'meta')) {
                $table->json('meta')->nullable()->after('body');
            }
        });

        Schema::table('audit_report_review_annotations', function (Blueprint $table) {
            if (! Schema::hasColumn('audit_report_review_annotations', 'review_round')) {
                $table->unsignedSmallInteger('review_round')->default(1)->after('user_id');
            }
            if (! Schema::hasColumn('audit_report_review_annotations', 'addressed_at')) {
                $table->timestamp('addressed_at')->nullable()->after('snapshot_path');
            }
        });

        if (! Schema::hasTable('audit_report_review_snapshots')) {
            Schema::create('audit_report_review_snapshots', function (Blueprint $table) {
                $table->id();
                $table->foreignId('audit_report_id')->constrained('audit_reports')->cascadeOnDelete();
                $table->unsignedSmallInteger('review_round');
                $table->boolean('is_resubmit')->default(false);
                $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
                $table->string('pages_fingerprint', 64)->nullable();
                $table->json('pages_data')->nullable();
                $table->text('maker_note')->nullable();
                $table->json('addressed_annotation_ids')->nullable();
                $table->json('change_summary')->nullable();
                $table->timestamps();

                $table->unique(['audit_report_id', 'review_round']);
                $table->index(['audit_report_id', 'is_resubmit']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_report_review_snapshots');

        Schema::table('audit_report_review_annotations', function (Blueprint $table) {
            if (Schema::hasColumn('audit_report_review_annotations', 'addressed_at')) {
                $table->dropColumn('addressed_at');
            }
            if (Schema::hasColumn('audit_report_review_annotations', 'review_round')) {
                $table->dropColumn('review_round');
            }
        });

        Schema::table('audit_report_review_events', function (Blueprint $table) {
            if (Schema::hasColumn('audit_report_review_events', 'meta')) {
                $table->dropColumn('meta');
            }
            if (Schema::hasColumn('audit_report_review_events', 'review_round')) {
                $table->dropColumn('review_round');
            }
        });
    }
};

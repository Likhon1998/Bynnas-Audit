<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audit_checklist_submissions', function (Blueprint $table) {
            $table->foreignId('audit_report_id')
                ->nullable()
                ->after('user_id')
                ->constrained('audit_reports')
                ->nullOnDelete();

            $table->index('audit_report_id', 'acs_report_idx');
        });
    }

    public function down(): void
    {
        Schema::table('audit_checklist_submissions', function (Blueprint $table) {
            $table->dropForeign(['audit_report_id']);
            $table->dropIndex('acs_report_idx');
            $table->dropColumn('audit_report_id');
        });
    }
};

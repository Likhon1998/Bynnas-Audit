<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audit_reports', function (Blueprint $table) {
            if (! Schema::hasColumn('audit_reports', 'report_month')) {
                $table->unsignedTinyInteger('report_month')->nullable()->after('shakha_id');
            }
            if (! Schema::hasColumn('audit_reports', 'report_year')) {
                $table->unsignedSmallInteger('report_year')->nullable()->after('report_month');
            }
        });
    }

    public function down(): void
    {
        Schema::table('audit_reports', function (Blueprint $table) {
            if (Schema::hasColumn('audit_reports', 'report_month')) {
                $table->dropColumn('report_month');
            }
            if (Schema::hasColumn('audit_reports', 'report_year')) {
                $table->dropColumn('report_year');
            }
        });
    }
};

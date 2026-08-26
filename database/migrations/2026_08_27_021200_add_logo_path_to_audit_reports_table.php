<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audit_reports', function (Blueprint $table) {
            if (! Schema::hasColumn('audit_reports', 'logo_path')) {
                $table->string('logo_path')->nullable()->after('auditor_designation');
            }
        });
    }

    public function down(): void
    {
        Schema::table('audit_reports', function (Blueprint $table) {
            if (Schema::hasColumn('audit_reports', 'logo_path')) {
                $table->dropColumn('logo_path');
            }
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audit_reports', function (Blueprint $table) {
            if (! Schema::hasColumn('audit_reports', 'review_perfect')) {
                $table->boolean('review_perfect')->default(false)->after('review_cc_superadmin');
            }
        });
    }

    public function down(): void
    {
        Schema::table('audit_reports', function (Blueprint $table) {
            if (Schema::hasColumn('audit_reports', 'review_perfect')) {
                $table->dropColumn('review_perfect');
            }
        });
    }
};

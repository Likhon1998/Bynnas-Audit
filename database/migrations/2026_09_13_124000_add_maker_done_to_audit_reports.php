<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audit_reports', function (Blueprint $table) {
            if (! Schema::hasColumn('audit_reports', 'maker_done_at')) {
                $table->timestamp('maker_done_at')->nullable()->after('review_perfect');
            }
            if (! Schema::hasColumn('audit_reports', 'maker_done_by')) {
                $table->foreignId('maker_done_by')->nullable()->after('maker_done_at')->constrained('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('audit_reports', function (Blueprint $table) {
            if (Schema::hasColumn('audit_reports', 'maker_done_by')) {
                $table->dropConstrainedForeignId('maker_done_by');
            }
            if (Schema::hasColumn('audit_reports', 'maker_done_at')) {
                $table->dropColumn('maker_done_at');
            }
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audit_report_review_annotations', function (Blueprint $table) {
            $table->string('snapshot_path')->nullable()->after('rect_h');
        });
    }

    public function down(): void
    {
        Schema::table('audit_report_review_annotations', function (Blueprint $table) {
            $table->dropColumn('snapshot_path');
        });
    }
};

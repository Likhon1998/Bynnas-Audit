<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audit_report_review_annotations', function (Blueprint $table) {
            $table->string('type', 20)->default('text')->after('user_id');
            $table->decimal('rect_x', 8, 4)->nullable()->after('body');
            $table->decimal('rect_y', 8, 4)->nullable()->after('rect_x');
            $table->decimal('rect_w', 8, 4)->nullable()->after('rect_y');
            $table->decimal('rect_h', 8, 4)->nullable()->after('rect_w');
        });
    }

    public function down(): void
    {
        Schema::table('audit_report_review_annotations', function (Blueprint $table) {
            $table->dropColumn(['type', 'rect_x', 'rect_y', 'rect_w', 'rect_h']);
        });
    }
};

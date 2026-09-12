<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audit_reports', function (Blueprint $table) {
            $table->timestamp('review_ready_at')->nullable()->after('reviewed_at');
            $table->timestamp('review_sent_to_maker_at')->nullable()->after('review_ready_at');
        });
    }

    public function down(): void
    {
        Schema::table('audit_reports', function (Blueprint $table) {
            $table->dropColumn(['review_ready_at', 'review_sent_to_maker_at']);
        });
    }
};

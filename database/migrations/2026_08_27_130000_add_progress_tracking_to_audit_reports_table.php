<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audit_reports', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->string('current_tab')->nullable()->after('status');
            $table->unsignedTinyInteger('progress_pct')->default(0)->after('current_tab');
            $table->timestamp('last_saved_at')->nullable()->after('progress_pct');
            $table->timestamp('completed_at')->nullable()->after('last_saved_at');
        });
    }

    public function down(): void
    {
        Schema::table('audit_reports', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
            $table->dropColumn(['current_tab', 'progress_pct', 'last_saved_at', 'completed_at']);
        });
    }
};

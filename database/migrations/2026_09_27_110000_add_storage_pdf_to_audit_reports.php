<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audit_reports', function (Blueprint $table) {
            $table->string('storage_pdf_path')->nullable()->after('maker_done_by');
            $table->timestamp('storage_pdf_at')->nullable()->after('storage_pdf_path');
        });
    }

    public function down(): void
    {
        Schema::table('audit_reports', function (Blueprint $table) {
            $table->dropColumn(['storage_pdf_path', 'storage_pdf_at']);
        });
    }
};

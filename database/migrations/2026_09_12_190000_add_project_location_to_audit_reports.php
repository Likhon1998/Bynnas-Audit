<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audit_reports', function (Blueprint $table) {
            $table->dropForeign(['shakha_id']);
        });

        Schema::table('audit_reports', function (Blueprint $table) {
            $table->unsignedBigInteger('shakha_id')->nullable()->change();
            $table->foreign('shakha_id')
                ->references('id')
                ->on('shakhas')
                ->nullOnDelete();

            $table->foreignId('project_location_id')
                ->nullable()
                ->after('shakha_id')
                ->constrained('project_locations')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('audit_reports', function (Blueprint $table) {
            $table->dropConstrainedForeignId('project_location_id');
            $table->dropForeign(['shakha_id']);
        });

        Schema::table('audit_reports', function (Blueprint $table) {
            $table->unsignedBigInteger('shakha_id')->nullable(false)->change();
            $table->foreign('shakha_id')
                ->references('id')
                ->on('shakhas')
                ->cascadeOnDelete();
        });
    }
};

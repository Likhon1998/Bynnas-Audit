<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->dropForeignKeyIfExists('audit_reports', 'shakha_id');

        Schema::table('audit_reports', function (Blueprint $table) {
            $table->unsignedBigInteger('shakha_id')->nullable()->change();
        });

        if (! $this->foreignKeyExists('audit_reports', 'shakha_id')) {
            Schema::table('audit_reports', function (Blueprint $table) {
                $table->foreign('shakha_id')
                    ->references('id')
                    ->on('shakhas')
                    ->nullOnDelete();
            });
        }

        if (! Schema::hasColumn('audit_reports', 'project_location_id')) {
            Schema::table('audit_reports', function (Blueprint $table) {
                $table->foreignId('project_location_id')
                    ->nullable()
                    ->after('shakha_id')
                    ->constrained('project_locations')
                    ->nullOnDelete();
            });
        } elseif (! $this->foreignKeyExists('audit_reports', 'project_location_id')) {
            Schema::table('audit_reports', function (Blueprint $table) {
                $table->foreign('project_location_id')
                    ->references('id')
                    ->on('project_locations')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('audit_reports', 'project_location_id')) {
            $this->dropForeignKeyIfExists('audit_reports', 'project_location_id');
            Schema::table('audit_reports', function (Blueprint $table) {
                $table->dropColumn('project_location_id');
            });
        }

        $this->dropForeignKeyIfExists('audit_reports', 'shakha_id');

        Schema::table('audit_reports', function (Blueprint $table) {
            $table->unsignedBigInteger('shakha_id')->nullable(false)->change();
        });

        if (! $this->foreignKeyExists('audit_reports', 'shakha_id')) {
            Schema::table('audit_reports', function (Blueprint $table) {
                $table->foreign('shakha_id')
                    ->references('id')
                    ->on('shakhas')
                    ->cascadeOnDelete();
            });
        }
    }

    private function dropForeignKeyIfExists(string $table, string $column): void
    {
        $name = $this->foreignKeyName($table, $column);
        if (! $name) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($name) {
            $blueprint->dropForeign($name);
        });
    }

    private function foreignKeyExists(string $table, string $column): bool
    {
        return $this->foreignKeyName($table, $column) !== null;
    }

    private function foreignKeyName(string $table, string $column): ?string
    {
        $database = DB::getDatabaseName();

        $row = DB::selectOne(
            'SELECT CONSTRAINT_NAME as name
             FROM information_schema.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = ?
               AND TABLE_NAME = ?
               AND COLUMN_NAME = ?
               AND REFERENCED_TABLE_NAME IS NOT NULL
             LIMIT 1',
            [$database, $table, $column]
        );

        return $row->name ?? null;
    }
};

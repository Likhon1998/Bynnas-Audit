<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const TABLE = 'audit_reviewer_month_assignments';

    /** MySQL caps identifiers at 64 chars; the auto-generated unique name is 72. */
    private const UNIQUE = 'arma_auditor_year_month_unique';

    public function up(): void
    {
        // A failed MySQL run can leave the table behind without its indexes (DDL is not transactional).
        if (Schema::hasTable(self::TABLE) && DB::table(self::TABLE)->doesntExist()) {
            Schema::drop(self::TABLE);
        }

        if (! Schema::hasTable(self::TABLE)) {
            Schema::create(self::TABLE, function (Blueprint $table) {
                $table->id();
                $table->foreignId('auditor_user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('reviewer_user_id')->constrained('users')->cascadeOnDelete();
                $table->unsignedSmallInteger('year');
                $table->unsignedTinyInteger('month');
                $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->unique(['auditor_user_id', 'year', 'month'], self::UNIQUE);
                $table->index(['year', 'month']);
                $table->index('reviewer_user_id');
            });

            return;
        }

        $this->completePartialTable();
    }

    public function down(): void
    {
        Schema::dropIfExists(self::TABLE);
    }

    private function completePartialTable(): void
    {
        $foreignColumns = collect(Schema::getForeignKeys(self::TABLE))->flatMap(fn ($fk) => $fk['columns'])->all();

        Schema::table(self::TABLE, function (Blueprint $table) use ($foreignColumns) {
            if (! Schema::hasIndex(self::TABLE, self::UNIQUE)) {
                $table->unique(['auditor_user_id', 'year', 'month'], self::UNIQUE);
            }
            if (! Schema::hasIndex(self::TABLE, ['year', 'month'])) {
                $table->index(['year', 'month']);
            }
            if (! Schema::hasIndex(self::TABLE, ['reviewer_user_id'])) {
                $table->index('reviewer_user_id');
            }
            if (! in_array('auditor_user_id', $foreignColumns, true)) {
                $table->foreign('auditor_user_id')->references('id')->on('users')->cascadeOnDelete();
            }
            if (! in_array('reviewer_user_id', $foreignColumns, true)) {
                $table->foreign('reviewer_user_id')->references('id')->on('users')->cascadeOnDelete();
            }
            if (! in_array('assigned_by', $foreignColumns, true)) {
                $table->foreign('assigned_by')->references('id')->on('users')->nullOnDelete();
            }
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ensure employee_code is unique org-wide before adding the index.
        $this->dedupeEmployeeCodes();

        if ($this->hasIndex('shakha_employees', 'shakha_employees_shakha_id_employee_code_unique')) {
            Schema::table('shakha_employees', function (Blueprint $table) {
                $table->dropUnique(['shakha_id', 'employee_code']);
            });
        }

        if (! $this->hasIndex('shakha_employees', 'shakha_employees_employee_code_unique')) {
            Schema::table('shakha_employees', function (Blueprint $table) {
                $table->unique('employee_code');
            });
        }

        if (! Schema::hasTable('shakha_employee_transfers')) {
            Schema::create('shakha_employee_transfers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('shakha_employee_id')->constrained('shakha_employees')->cascadeOnDelete();
                $table->foreignId('from_shakha_id')->constrained('shakhas')->cascadeOnDelete();
                $table->foreignId('to_shakha_id')->constrained('shakhas')->cascadeOnDelete();
                $table->timestamp('transferred_at');
                $table->text('note')->nullable();
                $table->foreignId('transferred_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index(['shakha_employee_id', 'transferred_at'], 'shakha_emp_xfer_emp_at_idx');
            });
        } elseif (! $this->hasIndex('shakha_employee_transfers', 'shakha_emp_xfer_emp_at_idx')) {
            Schema::table('shakha_employee_transfers', function (Blueprint $table) {
                $table->index(['shakha_employee_id', 'transferred_at'], 'shakha_emp_xfer_emp_at_idx');
            });
        }

        if (! Schema::hasColumn('audit_findings', 'responsible_staff_ids')) {
            Schema::table('audit_findings', function (Blueprint $table) {
                $table->json('responsible_staff_ids')->nullable()->after('responsible_staff_name');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('audit_findings', 'responsible_staff_ids')) {
            Schema::table('audit_findings', function (Blueprint $table) {
                $table->dropColumn('responsible_staff_ids');
            });
        }

        Schema::dropIfExists('shakha_employee_transfers');

        if ($this->hasIndex('shakha_employees', 'shakha_employees_employee_code_unique')) {
            Schema::table('shakha_employees', function (Blueprint $table) {
                $table->dropUnique(['employee_code']);
            });
        }

        if (! $this->hasIndex('shakha_employees', 'shakha_employees_shakha_id_employee_code_unique')) {
            Schema::table('shakha_employees', function (Blueprint $table) {
                $table->unique(['shakha_id', 'employee_code']);
            });
        }
    }

    private function dedupeEmployeeCodes(): void
    {
        $dupes = DB::table('shakha_employees')
            ->select('employee_code')
            ->groupBy('employee_code')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('employee_code');

        foreach ($dupes as $code) {
            $rows = DB::table('shakha_employees')
                ->where('employee_code', $code)
                ->orderBy('id')
                ->get(['id', 'shakha_id', 'employee_code']);

            foreach ($rows as $index => $row) {
                if ($index === 0) {
                    continue;
                }
                DB::table('shakha_employees')
                    ->where('id', $row->id)
                    ->update([
                        'employee_code' => $row->employee_code.'-S'.$row->shakha_id.'-'.$row->id,
                    ]);
            }
        }
    }

    private function hasIndex(string $table, string $indexName): bool
    {
        foreach (Schema::getIndexes($table) as $index) {
            if (($index['name'] ?? '') === $indexName) {
                return true;
            }
        }

        return false;
    }
};

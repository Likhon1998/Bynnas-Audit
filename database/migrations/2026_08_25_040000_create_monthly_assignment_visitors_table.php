<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('monthly_assignment_visitors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('monthly_assignment_id')->constrained('monthly_assignments')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->restrictOnDelete();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['monthly_assignment_id', 'employee_id'], 'mav_assignment_employee_unique');
            $table->index(['employee_id', 'monthly_assignment_id'], 'mav_employee_assignment_index');
        });

        // Backfill primary visitor from existing assignments.
        $rows = DB::table('monthly_assignments')
            ->select('id', 'employee_id', 'created_at', 'updated_at')
            ->whereNotNull('employee_id')
            ->get();

        foreach ($rows as $row) {
            DB::table('monthly_assignment_visitors')->insert([
                'monthly_assignment_id' => $row->id,
                'employee_id' => $row->employee_id,
                'sort_order' => 0,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('monthly_assignment_visitors');
    }
};

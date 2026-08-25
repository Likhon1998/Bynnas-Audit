<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calendar_holidays', function (Blueprint $table) {
            $table->id();
            $table->date('holiday_date');
            $table->string('name', 160);
            $table->string('type', 20)->default('national'); // national | government
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['holiday_date', 'type'], 'calendar_holidays_date_type_unique');
            $table->index('holiday_date');
        });

        Schema::table('monthly_assignments', function (Blueprint $table) {
            $table->boolean('count_off_days')->default(false)->after('duration_mode');
        });
    }

    public function down(): void
    {
        Schema::table('monthly_assignments', function (Blueprint $table) {
            $table->dropColumn('count_off_days');
        });

        Schema::dropIfExists('calendar_holidays');
    }
};

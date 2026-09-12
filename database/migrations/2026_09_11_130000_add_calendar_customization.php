<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calendar_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 64)->unique();
            $table->json('value')->nullable();
            $table->timestamps();
        });

        Schema::table('calendar_holidays', function (Blueprint $table) {
            $table->string('notes', 255)->nullable()->after('type');
            $table->foreignId('created_by')->nullable()->after('is_active')->constrained('users')->nullOnDelete();
        });

        // Default NGO weekend: Friday + Saturday (editable later from Calendar UI).
        DB::table('calendar_settings')->updateOrInsert(
            ['key' => 'weekend_days'],
            [
                'value' => json_encode([5, 6]),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    public function down(): void
    {
        Schema::table('calendar_holidays', function (Blueprint $table) {
            $table->dropConstrainedForeignId('created_by');
            $table->dropColumn('notes');
        });

        Schema::dropIfExists('calendar_settings');
    }
};

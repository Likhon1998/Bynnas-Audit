<?php

use App\Models\PerformanceRule;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('performance_rules', function (Blueprint $table) {
            $table->id();
            $table->string('key', 40)->unique();
            $table->string('label');
            $table->string('description')->nullable();
            $table->decimal('points', 6, 2)->default(0);
            $table->unsignedInteger('monthly_cap')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        $now = now();
        DB::table('performance_rules')->insert(array_map(
            fn (array $rule) => collect($rule)->except('source')->all() + ['created_at' => $now, 'updated_at' => $now],
            PerformanceRule::defaults()
        ));
    }

    public function down(): void
    {
        Schema::dropIfExists('performance_rules');
    }
};

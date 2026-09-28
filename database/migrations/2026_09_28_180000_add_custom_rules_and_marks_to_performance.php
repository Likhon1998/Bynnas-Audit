<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('performance_rules', function (Blueprint $table) {
            $table->string('source', 16)->default('auto')->after('key');
        });

        Schema::create('performance_marks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('performance_rule_id')->nullable()->constrained()->nullOnDelete();
            $table->string('label', 120);
            $table->decimal('points', 8, 2);
            $table->text('note')->nullable();
            $table->date('awarded_on');
            $table->foreignId('awarded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['user_id', 'awarded_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('performance_marks');

        Schema::table('performance_rules', function (Blueprint $table) {
            $table->dropColumn('source');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kpi_laws', function (Blueprint $table) {
            $table->id();
            $table->string('key', 64)->unique();
            $table->string('label');
            $table->string('category', 32)->default('ratio');
            $table->string('operation', 16);
            $table->string('left_operand', 64);
            $table->string('right_operand', 64);
            $table->string('formula_display');
            $table->text('description')->nullable();
            $table->string('format', 16)->default('ratio');
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kpi_laws');
    }
};

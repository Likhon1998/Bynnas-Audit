<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shakhas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('area_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code')->nullable()->unique();
            $table->string('status', 20)->default('active');
            $table->timestamps();

            $table->unique(['area_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shakhas');
    }
};

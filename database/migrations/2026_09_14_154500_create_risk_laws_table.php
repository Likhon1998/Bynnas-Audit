<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('risk_laws', function (Blueprint $table) {
            $table->id();
            $table->string('key', 64)->unique();
            $table->string('label');
            $table->string('group_key', 32)->default('factors');
            $table->string('unit', 16)->default('percent');
            $table->string('direction', 24)->default('lower_better');
            $table->json('bands');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('affects_live_score')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('risk_laws');
    }
};

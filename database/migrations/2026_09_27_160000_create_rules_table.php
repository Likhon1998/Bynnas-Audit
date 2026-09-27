<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rules', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('serial')->default(1);
            $table->string('title');
            $table->text('statement');
            $table->string('article')->nullable();
            $table->string('reference_where')->nullable();
            $table->string('reference_when')->nullable();
            $table->string('reference_who')->nullable();
            $table->string('source_name')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rules');
    }
};

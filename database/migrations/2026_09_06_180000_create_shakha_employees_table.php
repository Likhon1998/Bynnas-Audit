<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shakha_employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shakha_id')->constrained()->cascadeOnDelete();
            $table->string('employee_code', 80);
            $table->string('name', 160);
            $table->string('designation', 160);
            $table->string('phone', 40)->nullable();
            $table->string('email', 160)->nullable();
            $table->date('joined_organization_at')->nullable();
            $table->date('joined_shakha_at')->nullable();
            $table->string('status', 20)->default('active');
            $table->unsignedInteger('sort_order')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['shakha_id', 'employee_code']);
            $table->index(['shakha_id', 'status']);
            $table->index('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shakha_employees');
    }
};

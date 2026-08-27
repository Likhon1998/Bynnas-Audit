<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Y-axis of the Excel matrix — the audit rules / indicators.
     */
    public function up(): void
    {
        Schema::create('audit_indicators', function (Blueprint $table) {
            $table->id();
            $table->string('category')->nullable();
            $table->string('sub_category')->nullable();
            $table->string('indicator_code')->unique();
            $table->text('title');
            $table->string('risk_rating')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('category');
            $table->index('sub_category');
            $table->index('risk_rating');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_indicators');
    }
};

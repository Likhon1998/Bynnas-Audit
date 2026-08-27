<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Align audit_indicators with Excel columns A–E
     * (category, sub_category, indicator_code, title, risk_rating).
     */
    public function up(): void
    {
        if (! Schema::hasTable('audit_indicators')) {
            return;
        }

        Schema::disableForeignKeyConstraints();

        if (Schema::hasTable('audit_findings')) {
            DB::table('audit_findings')->delete();
        }

        Schema::dropIfExists('audit_indicators');

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

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        if (! Schema::hasTable('audit_indicators')) {
            return;
        }

        Schema::disableForeignKeyConstraints();

        if (Schema::hasTable('audit_findings')) {
            DB::table('audit_findings')->delete();
        }

        Schema::dropIfExists('audit_indicators');

        Schema::create('audit_indicators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('audit_category_id')->constrained('audit_categories')->cascadeOnDelete();
            $table->string('code');
            $table->text('title');
            $table->string('risk_rating', 32)->default('Medium');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique('code');
            $table->index(['audit_category_id', 'sort_order']);
            $table->index('risk_rating');
        });

        Schema::enableForeignKeyConstraints();
    }
};

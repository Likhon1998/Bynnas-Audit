<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_reviewer_month_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('auditor_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('reviewer_user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('month');
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['auditor_user_id', 'year', 'month']);
            $table->index(['year', 'month']);
            $table->index('reviewer_user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_reviewer_month_assignments');
    }
};

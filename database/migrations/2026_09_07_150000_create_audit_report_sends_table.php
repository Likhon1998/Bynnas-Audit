<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_report_sends', function (Blueprint $table) {
            $table->id();
            $table->foreignId('audit_report_id')->constrained('audit_reports')->cascadeOnDelete();
            $table->foreignId('sent_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('from_name');
            $table->string('from_email');
            $table->string('to_email');
            $table->string('cc_email')->nullable();
            $table->string('subject');
            $table->text('body');
            $table->boolean('attached_pdf')->default(false);
            $table->string('status', 20)->default('sent');
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index(['audit_report_id', 'sent_at']);
            $table->index(['sent_by_user_id', 'sent_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_report_sends');
    }
};

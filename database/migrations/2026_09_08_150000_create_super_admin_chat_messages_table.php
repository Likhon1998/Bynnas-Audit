<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('super_admin_chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->uuid('thread_uuid');
            $table->string('intent', 80)->nullable();
            $table->longText('question');
            $table->longText('answer')->nullable();
            $table->string('status', 20)->default('answered');
            $table->timestamps();

            $table->index(['user_id', 'thread_uuid', 'created_at'], 'super_admin_chat_history_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('super_admin_chat_messages');
    }
};

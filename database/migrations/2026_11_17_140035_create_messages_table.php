<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();

            $table->foreignId('conversation_id')
                ->constrained('conversations')
                ->cascadeOnDelete();

            $table->string('external_message_id', 128)->nullable();
            $table->string('direction', 8)->index();
            $table->string('content_type', 16)->default('text');
            $table->text('text')->nullable();
            $table->jsonb('attachments')->nullable();
            $table->string('external_author_id', 128)->nullable();
            $table->string('status', 16)->default('sent')->index();
            $table->text('error_message')->nullable();
            $table->timestamp('external_created_at')->nullable()->index();

            $table->foreignId('lead_id')
                ->nullable()
                ->constrained('leads')
                ->nullOnDelete();

            $table->foreignId('sent_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
            $table->unique(['conversation_id', 'external_message_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
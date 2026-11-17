<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('source_id')
                ->constrained('sources')
                ->restrictOnDelete();

            $table->string('external_chat_id', 128);
            $table->foreignId('client_id')
                ->nullable()
                ->constrained('clients')
                ->nullOnDelete();

            $table->foreignId('listing_id')
                ->nullable()
                ->constrained('listings')
                ->nullOnDelete();

            $table->string('type', 16)->default('inbound')->index();
            $table->timestamp('last_message_at')->nullable()->index();
            $table->text('last_message_preview')->nullable();
            $table->unsignedInteger('unread_count')->default(0);
            $table->string('status', 16)->default('open')->index();
            $table->timestamps();
            $table->unique(['source_id', 'external_chat_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};
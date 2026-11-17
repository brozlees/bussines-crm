<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();

            $table->foreignId('listing_id')
                ->constrained('listings')
                ->cascadeOnDelete();

            $table->foreignId('client_id')
                ->nullable()
                ->constrained('clients')
                ->nullOnDelete();

            $table->unsignedTinyInteger('relevance_score')->default(0)->index();
            $table->text('ai_reasoning')->nullable();
            $table->text('proposal_text')->nullable();

            $table->string('delivery_mode', 32)->index();
            $table->string('channel', 32)->nullable()->index();
            $table->text('platform_warning')->nullable();

            $table->string('status', 16)->default('draft')->index();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('sent_at')->nullable()->index();

            $table->foreignId('decided_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->unique('listing_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
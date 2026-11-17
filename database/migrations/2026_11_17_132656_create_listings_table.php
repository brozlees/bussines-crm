<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('listings', function (Blueprint $table) {
            $table->id();

            $table->foreignId('source_id')
                ->constrained('sources')
                ->restrictOnDelete();

            $table->string('external_id', 128);
            $table->string('direction', 16)->index();

            $table->string('title', 500)->nullable();
            $table->text('description')->nullable();
            $table->string('category', 255)->nullable();
            $table->string('city', 128)->nullable();
            $table->decimal('price', 12, 2)->nullable();
            $table->string('url', 1024)->nullable();

            $table->string('contact_name', 255)->nullable();
            $table->string('contact_email', 255)->nullable()->index();
            $table->string('contact_phone', 32)->nullable()->index();

            $table->jsonb('raw_payload')->nullable();

            $table->timestamp('published_at')->nullable()->index();
            $table->timestamp('fetched_at')->nullable();
            $table->boolean('is_active')->default(true)->index();

            $table->timestamps();

            $table->unique(['source_id', 'external_id']);
            $table->index(['direction', 'category', 'city']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('listings');
    }
};
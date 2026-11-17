<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consents', function (Blueprint $table) {
            $table->id();

            $table->foreignId('client_id')
                ->constrained('clients')
                ->cascadeOnDelete();

            $table->string('channel', 32)->index();
            $table->text('consent_text');
            $table->string('document_version', 32)->nullable();
            $table->timestamp('granted_at');
            $table->timestamp('revoked_at')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 512)->nullable();
            $table->string('source', 32)->default('site');
            $table->timestamps();
            $table->unique(['client_id', 'channel', 'granted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consents');
    }
};
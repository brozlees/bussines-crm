<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();

            $table->string('source_type', 32)->index();
            $table->string('external_id', 128)->nullable()->index();
            $table->string('name', 255)->nullable();
            $table->string('email', 255)->nullable()->index();
            $table->string('phone', 32)->nullable()->index();
            $table->jsonb('meta')->nullable();
            $table->boolean('marketing_allowed')->default(false)->index();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['source_type', 'external_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
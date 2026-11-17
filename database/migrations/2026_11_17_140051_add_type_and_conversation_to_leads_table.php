<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->string('type', 16)->default('outbound_cold')->after('id')->index();

            $table->foreignId('conversation_id')
                ->nullable()
                ->after('client_id')
                ->constrained('conversations')
                ->nullOnDelete();
        });

        Schema::table('leads', function (Blueprint $table) {
            $table->dropUnique(['listing_id']);
            $table->index('listing_id');
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropForeign(['conversation_id']);
            $table->dropColumn(['conversation_id', 'type']);
        });

        Schema::table('leads', function (Blueprint $table) {
            $table->dropIndex(['listing_id']);
            $table->unique('listing_id');
        });
    }
};
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('card_categories', function (Blueprint $table) {
            $table->enum('provider_type', ['stock', 'api'])->default('stock')->after('is_active');
            $table->string('api_provider')->nullable()->after('provider_type');
            $table->json('api_config')->nullable()->after('api_provider');
        });
    }

    public function down(): void
    {
        Schema::table('card_categories', function (Blueprint $table) {
            $table->dropColumn(['provider_type', 'api_provider', 'api_config']);
        });
    }
};

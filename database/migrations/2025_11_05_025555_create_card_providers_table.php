<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCardProvidersTable extends Migration
{
    public function up(): void
    {
        Schema::create('card_providers', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Tên nhà cung cấp
            $table->string('code')->unique(); // Code: viettel_api, mobifone_api
            $table->text('description')->nullable();
            $table->string('api_url')->nullable();
            $table->string('api_key')->nullable();
            $table->string('api_secret')->nullable();
            $table->json('api_config')->nullable(); // Cấu hình khác
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('card_providers');
    }
}

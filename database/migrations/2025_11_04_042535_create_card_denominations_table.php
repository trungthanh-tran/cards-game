<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCardDenominationsTable extends Migration
{
    public function up()
    {
        Schema::create('card_denominations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('card_categories')->onDelete('cascade');
            $table->decimal('value', 15, 2);
            $table->decimal('price', 15, 2);
            $table->integer('stock')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('card_denominations');
    }
}
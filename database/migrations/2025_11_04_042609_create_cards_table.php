<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCardsTable extends Migration
{
    public function up()
    {
        Schema::create('cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('denomination_id')->constrained('card_denominations')->onDelete('cascade');
            $table->string('serial')->unique();
            $table->string('code');
            $table->date('expiry_date');
            $table->enum('status', ['available', 'sold', 'expired'])->default('available');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('cards');
    }
}
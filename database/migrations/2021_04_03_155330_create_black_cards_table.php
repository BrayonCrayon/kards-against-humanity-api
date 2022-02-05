<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBlackCardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('black_cards', function (Blueprint $table) {
            $table->id();
            $table->text('text');
            $table->unsignedInteger('pick');
            $table->unsignedBigInteger('expansion_id');
            $table->timestamp('created_at')->nullable()->default(now());
            $table->timestamp('updated_at')->nullable()->default(now());
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('black_cards');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSettingsTable extends Migration
{
    public function up() : void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->smallInteger('selection_timer')->nullable();
            $table->uuid('game_id');
            $table->timestamps();

            $table->foreign('game_id')->references('id')->on('games');
        });
    }

    public function down() : void
    {
        Schema::dropIfExists('settings');
    }
}

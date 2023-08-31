<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSelectionEndsAtToGamesTable extends Migration
{

    public function up() : void
    {
        Schema::table('games', function (Blueprint $table) {
            $table->unsignedBigInteger('selection_ends_at')->nullable();
        });
    }

    public function down() : void
    {
        Schema::table('games', function (Blueprint $table) {
            $table->dropColumn('selection_ends_at');
        });
    }
}

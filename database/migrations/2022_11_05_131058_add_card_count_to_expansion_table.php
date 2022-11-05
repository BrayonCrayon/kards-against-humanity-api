<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCardCountToExpansionTable extends Migration
{

    public function up() : void
    {
        Schema::table('expansions', function (Blueprint $table) {
            $table->integer('card_count')->default(0);
            $table->dropColumn('white_card_count');
        });
    }

    public function down() : void
    {
        Schema::table('expansions', function (Blueprint $table) {
            $table->dropColumn('card_count');
            $table->integer('white_card_count')->default(0);
        });
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateGamesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('games', function (Blueprint $table) {
            $table->uuid('id')->unique();
            $table->string('name');
            $table->timestamps();
            $table->softDeletes();
        });
        DB::statement("CREATE EXTENSION IF NOT EXISTS \"uuid-ossp\";");
        DB::statement("ALTER TABLE games ALTER COLUMN id SET DEFAULT uuid_generate_v4();");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('games');
    }
}

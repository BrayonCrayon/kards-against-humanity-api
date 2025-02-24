<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Laravel\Telescope\Telescope;

class LoadCardsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Telescope::stopRecording();
        Artisan::call('kah:import-cards --dir=expansions/existing');
        Artisan::call('kah:import-cards --dir=expansions/nsfw');
        Artisan::call('kah:import-cards --dir=expansions/sfw');
        Telescope::startRecording();
    }
}

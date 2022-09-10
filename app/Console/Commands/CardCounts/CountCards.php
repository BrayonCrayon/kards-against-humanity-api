<?php

namespace App\Console\Commands\CardCounts;

use App\Models\Expansion;
use App\Models\WhiteCard;
use Illuminate\Console\Command;

class CountCards extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'count:cards';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'count all white cards for each expansion';

    public function handle()
    {
        $expansions = Expansion::all();
        $expansions->each(function ($expansion) {
            $expansion->white_card_count = WhiteCard::where('expansion_id', $expansion->id)->count();
            $expansion->save();
        });
    }
}

<?php

namespace App\Console\Commands\CardCounts;

use App\Models\BlackCard;
use App\Models\Expansion;
use App\Models\WhiteCard;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class CountCards extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kah:count-cards';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'count white and black cards for each expansion';

    public function handle()
    {
        $this->info('Starting to count all cards for each expansion.');
        Expansion::all()
            ->chunk(100)
            ->each(function (Collection $expansions) {
                $expansions->each(fn ($expansion) => $this->updateCardCount($expansion));
            });
        $this->info('Finished counting cards.');
    }

    private function updateCardCount(Expansion $expansion)
    {
        $whiteCardCount = WhiteCard::where('expansion_id', $expansion->id)->count();
        $blackCardCount = BlackCard::where('expansion_id', $expansion->id)->count();
        $expansion->update([
            'card_count' => $whiteCardCount + $blackCardCount
        ]);
    }
}

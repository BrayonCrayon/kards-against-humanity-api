<?php

namespace App\Console\Commands\ImportCards;

use App\Models\BlackCard;
use App\Models\Expansion;
use App\Models\WhiteCard;
use Illuminate\Console\Command;

class ImportCards extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kah:import-cards';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import all cards';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $startLoading = now();
        $this->loadCards();
        $endLoading = now();
        $this->info("Loading Deck: {$endLoading->diffInMilliseconds($startLoading)}}");
        return 0;
    }

    private function loadCards()
    {
        $cardData = $this->loadDecks();

        Expansion::insert($cardData['expansions']);
        WhiteCard::insert($cardData['white_cards']);
        BlackCard::insert($cardData['black_cards']);
    }

    private function loadDecks()
    {
        $jsonString = file_get_contents(app_path('Console/Commands/ImportCards/card-data.json'));

        return json_decode($jsonString, true);
    }
}

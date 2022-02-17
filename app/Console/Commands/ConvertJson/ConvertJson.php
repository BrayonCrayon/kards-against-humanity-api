<?php

namespace App\Console\Commands\ConvertJson;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ConvertJson extends Command
{
    private $cahData;
    private $expansions;
    private $whiteCards;
    private $blackCards;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'convert:json';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Converts cah-cards-full.json file into json that would be easily to read into the DB';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->expansions = collect();
        $this->whiteCards = collect();
        $this->blackCards = collect();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->loadJsonFile();
        $this->cahData->each(function ($deck, $index) {
            $this->parseExpansions($deck, $index);
            $this->parseWhiteCards($deck);
            $this->parseBlackCards($deck);
        });
        $this->saveDbCompatibleJsonFile();
        return 0;
    }

    private function loadJsonFile()
    {
        $jsonString = file_get_contents(app_path('Console/Commands/ConvertJson/cah-cards-full.json'));

        $this->cahData = collect(json_decode($jsonString, true));
    }

    private function parseExpansions($deck, $index)
    {
        $this->expansions->add([
            'name' => $deck["name"],
            "id" => $index + 1,
        ]);
    }

    private function parseWhiteCards($deck)
    {
        $whiteCardsData = collect($deck["white"]);

        $whiteCardsData->each(function ($card) {
            $this->whiteCards->add([
                'text' => $card['text'],
                'expansion_id' => $card['pack'] + 1
            ]);
        });
    }

    private function parseBlackCards($deck)
    {
        $blackCardsData = collect($deck["black"]);

        $blackCardsData->each(function ($card) {
            $this->blackCards->add([
                'text' => $card['text'],
                'pick' => $card['pick'],
                'expansion_id' => $card['pack'] + 1
            ]);
        });
    }

    private function saveDbCompatibleJsonFile()
    {
        $cardData = [
            'expansions' => $this->expansions,
            'white_cards' => $this->whiteCards,
            'black_cards' => $this->blackCards,
        ];

        File::put(app_path("Console/Commands/ImportCards/card-data.json"), json_encode($cardData));
    }
}

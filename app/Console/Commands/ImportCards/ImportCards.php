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
    protected $signature = 'import:cards';

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
//        collect($this->loadDeck())->each(fn ($expansionData) => $this->createExpansion($expansionData));
        $endLoading = now();
        $this->info("Loading Deck: {$endLoading->diffInMilliseconds($startLoading)}}");
        return 0;
    }

    private function loadCards()
    {
        $cardData = $this->loadDecks();

//        $expansions = collect($cardData['expansions'])->map(fn($expansion) => Expansion::make([
//            'id' => $expansion['id'],
//            'name' => $expansion['name']
//        ]))->toArray();

//        $whiteCards = collect($cardData['white_cards'])->map(fn($whiteCard) => WhiteCard::make([
//            'text' => $whiteCard['text'],
//            'expansion_id' => $whiteCard['expansion_id']
//        ]))->toArray();

//        $blackCards = collect($cardData['black_cards'])->map(fn($blackCard) => BlackCard::make([
//            'text' => $blackCard['text'],
//            'pick' => $blackCard['pick'],
//            'expansion_id' => $blackCard['expansion_id']
//        ]))->toArray();

//        Expansion::insert($expansions);
//        WhiteCard::insert($whiteCards);
//        BlackCard::insert($blackCards);

        Expansion::insert($cardData['expansions']);
        WhiteCard::insert($cardData['white_cards']);
        BlackCard::insert($cardData['black_cards']);
    }

    private function createExpansion($expansionData)
    {
        $this->info("Importing: {$expansionData['name']}");

        $expansion = Expansion::create([
            'name' => $expansionData['name']
        ]);

        $whiteCards = collect($expansionData['white'])->map(fn($whiteCard) => WhiteCard::make([
            'text' => $whiteCard['text'],
            'expansion_id' => $expansion->id
        ]));
        $blackCards = collect($expansionData['black'])->map(fn($blackCard) => BlackCard::make([
            'text' => $blackCard['text'],
            'pick' => $blackCard['pick'],
            'expansion_id' => $expansion->id
        ]));

        $this->info("Starting array conversion");
        $test = $whiteCards->toArray();
        $test2 = $blackCards->toArray();
        $this->info("Finished converting white and black cards to arrays");
        $this->info("starting inserting");
        WhiteCard::insert($test);
        $this->info("Finished inserting White cards");
        BlackCard::insert($test2);
        $this->info("Finished inserting Black cards");
    }

    private function loadDecks()
    {
        $jsonString = file_get_contents(app_path('Console/Commands/ImportCards/card-data.json'));

        return json_decode($jsonString, true);
    }
}

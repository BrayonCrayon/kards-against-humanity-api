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
        collect($this->loadDeck())->each(fn ($expansionData) => $this->createExpansion($expansionData));

        return 0;
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

        WhiteCard::insert($whiteCards->toArray());
        BlackCard::insert($blackCards->toArray());
    }

    private function loadDeck()
    {
        $jsonString = file_get_contents(app_path('Console/Commands/ImportCards/cah-cards-full.json'));

        return json_decode($jsonString, true);
    }
}

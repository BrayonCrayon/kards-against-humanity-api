<?php

namespace App\Console\Commands\ConvertJson;

use App\Models\Expansion;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ConvertJson extends Command
{
    private Collection $cahData;
    private Collection $expansions;
    private Collection $whiteCards;
    private Collection $blackCards;

    protected $signature = 'kah:convert-json';

    protected $description = 'Converts cah-cards-full.json file into json that would be easily to read into the DB';

    public function handle(): int
    {
        $this->loadJsonFile();
        $this->expansions = collect($this->cahData['expansions']);
        $this->whiteCards = collect($this->cahData['white_cards']);
        $this->blackCards = collect($this->cahData['black_cards']);
        $this->createExpansionFiles();
        return 0;
    }

    private function createExpansionFiles(): void
    {
        $this->info("Starting to Import Expansions");
        $this->expansions->each(function ($expansion) {
            $expansionData = [
                'expansion' => ['name' => $expansion['name']],
                'white_cards' => $this->whiteCards->filter(fn($whiteCard) => $whiteCard['expansion_id'] === $expansion['id'])
                    ->map(fn($whiteCard) => ['text' => $whiteCard['text']]),
                'black_cards' => $this->blackCards->filter(fn($blackCard) => $blackCard['expansion_id'] === $expansion['id'])
                    ->map(fn($blackCard) => ['text' => $blackCard['text'], 'pick' => $blackCard['pick']])
            ];

            $fileName = Str::kebab(Str::replace(["/", ":", ",", "[", "]", "(", ")", "\"", "*", "!", "?"],"", $expansion['name'])) . '.json';
            Storage::disk()->put('expansions/existing/' . $fileName, json_encode($expansionData));
        });
        $this->info("Finished Importing Expansions");
    }

    private function loadJsonFile(): void
    {
        $jsonString = Storage::disk()->get('card-data.json');

        $this->cahData = collect(json_decode($jsonString, true));
    }
}

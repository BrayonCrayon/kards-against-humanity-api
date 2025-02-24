<?php

use Illuminate\Support\Facades\Storage;
use Tests\Traits\GameUtilities;
use function Pest\Laravel\artisan;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;

uses(GameUtilities::class);

describe('ImportCards command', function () {
    beforeEach(function () {
        Storage::fake('local');

        $this->validData = [
            'expansion' => [
                "name" => "Teenagers",
            ],
            'black_cards' => [
                [
                    "text" => "My high school yearbook quote combines _______, _______, and _______.",
                    "pick" => 3,
                ]
            ],
            'white_cards' => [
                ["text" => "The back of the school bus"],
                ["text" => "That one bathroom no one uses"],
                ["text" => "The mall food court at 3 PM"],
            ]
        ];
    });

    it('imports cards from a file', function () {
        Storage::disk()->put('test.json', json_encode($this->validData));

        artisan('kah:import-cards --file=test.json')->assertSuccessful();

        assertDatabaseHas('expansions', $this->validData['expansion']);
        collect($this->validData['black_cards'])
            ->each(function ($blackCard) {
                assertDatabaseHas('black_cards', $blackCard);
            });
        collect($this->validData['white_cards'])
            ->each(function ($whiteCard) {
                assertDatabaseHas('white_cards', $whiteCard);
            });
    });

    it('fails if the given file does not exist', function () {
        Storage::assertMissing('test.json');

        artisan('kah:import-cards --file=test.json')->assertFailed();

        assertDatabaseCount('expansions', 0);
    });

    it('imports cards from the files in a directory', function () {

        $expansions = collect(array_fill(1, 3, $this->validData))
            ->map(function (array $expansionData, int $index) {
                $expansionData['expansion']['name'] = "Teenagers {$index}";
                return $expansionData;
            });

        $expansions->each(function (array $fileData, int $index) {
            Storage::disk()->put("testDir/test{$index}.json", json_encode($fileData));
        });

        artisan('kah:import-cards --dir=testDir')->assertSuccessful();

        $expansions->each(function (array $expansionData) {
            assertDatabaseHas('expansions', $expansionData['expansion']);
            collect($expansionData['black_cards'])
                ->each(function ($blackCard) {
                    assertDatabaseHas('black_cards', $blackCard);
                });
            collect($expansionData['white_cards'])
                ->each(function ($whiteCard) {
                    assertDatabaseHas('white_cards', $whiteCard);
                });
        });
    });

    it('fails if the given directory does not exist', function () {
        Storage::assertMissing('testDir');

        artisan('kah:import-cards --dir=testDir')->assertFailed();

        assertDatabaseCount('expansions', 0);
        assertDatabaseCount('white_cards', 0);
        assertDatabaseCount('black_cards', 0);
    });

    it('fails if neither --file nor --dir is provided', function () {
        artisan('kah:import-cards')
            ->expectsOutput('Either the --file or --dir option must be provided.')
            ->assertFailed();
    });
});


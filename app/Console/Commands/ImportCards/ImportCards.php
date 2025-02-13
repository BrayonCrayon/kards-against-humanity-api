<?php

namespace App\Console\Commands\ImportCards;

use App\Models\BlackCard;
use App\Models\Expansion;
use App\Models\WhiteCard;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;
use PHPUnit\Runner\FileDoesNotExistException;

class ImportCards extends Command
{
    protected $signature = 'kah:import-cards {--file=} {--dir=}]';
    protected $description = 'Import all cards';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $startLoading = now();

        try {
            $this->loadExpansions();
        } catch (FileDoesNotExistException|InvalidArgumentException $e) {
            $this->error($e->getMessage());
            return 1;
        }

        $endLoading = now();
        $this->info("Loading Deck: {$endLoading->diffInMilliseconds($startLoading)}}");

        return 0;
    }

    private function loadExpansions(): void
    {
        $this->getFileNames()->each(fn($fileName) => $this->loadCards($fileName));
    }

    private function loadCards(string $fileName): void
    {
        $cardData = $this->loadDecks($fileName);
        $expansion = Expansion::query()->create($cardData['expansion']);

        $blackCards = collect($cardData['black_cards'])->map(function ($blackCard) use ($expansion) {
            return [
                ...$blackCard,
                'expansion_id' => $expansion->id,
            ];
        })->toArray();

        $whiteCards = collect($cardData['white_cards'])->map(function ($whiteCards) use ($expansion) {
            return [
                ...$whiteCards,
                'expansion_id' => $expansion->id,
            ];
        })->toArray();

        WhiteCard::query()->insert($whiteCards);
        BlackCard::query()->insert($blackCards);
    }

    private function loadDecks(string $fileName): array
    {
        return json_decode(Storage::disk()->get($fileName), true);
    }

    public function getFileNames(): Collection
    {
        if(!$this->option('file') && !$this->option('dir')) {
            throw new InvalidArgumentException('Either the --file or --dir option must be provided.');
        }

        return $this->validateFiles($this->option('file') ?? $this->option('dir'));
    }

    private function validateFiles(string $path): Collection
    {
        if (!Storage::disk('local')->exists($path)) {
            throw new FileDoesNotExistException($path);
        }

        if(Storage::disk('local')->directoryExists($path)) {
            return collect(Storage::disk('local')->allFiles($path))
                ->filter(fn($file) => Str::endsWith($file, '.json'));
        }

        return collect([$path]);
    }
}

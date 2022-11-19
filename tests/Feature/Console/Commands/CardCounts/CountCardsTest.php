<?php

namespace Tests\Feature\Console\Commands\CardCounts;

use App\Models\Expansion;
use Tests\TestCase;

class CountCardsTest extends TestCase
{
    /** @test */
    public function it_add_correct_white_card_count_to_expansion()
    {
        $expansions = Expansion::factory()
            ->count(2)
            ->hasBlackCards(5)
            ->hasWhiteCards(5)
            ->create();

        $expansions->each(fn($expansion) => $this->assertEquals(0, $expansion->card_count));

        $this->artisan('kah:count-cards');

        $expansions->each(fn($expansion) => $this->assertEquals(10, $expansion->fresh()->card_count));
    }

}

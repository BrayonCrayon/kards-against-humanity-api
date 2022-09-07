<?php

namespace Tests\Feature\Console\Commands\CardCounts;

use App\Models\Expansion;
use Tests\TestCase;

class CountCardsTest extends TestCase
{
    /** @test */
    public function it_add_correct_white_card_count_to_expansion()
    {
        $expansions = Expansion::factory()->count(2)->hasWhiteCards(5)->create();

        $expansions->each(fn($expansion) => $this->assertEquals(0, $expansion->white_card_count));

        $this->artisan('count:cards');

        $expansions->each(fn($expansion) => $this->assertEquals(5, $expansion->fresh()->white_card_count));
    }

}

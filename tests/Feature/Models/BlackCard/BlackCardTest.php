<?php

namespace Tests\Feature\Models\BlackCard;

use App\Models\BlackCard;
use App\Models\Expansion;
use Tests\TestCase;

class BlackCardTest extends TestCase
{
    /** @test */
    public function expansion_relationship_brings_back_expansion_type()
    {
        $blackCard = BlackCard::factory()->create();
        $this->assertInstanceOf(Expansion::class, $blackCard->expansion);
    }
}

<?php

namespace Tests\Feature\Models\WhiteCard;

use App\Models\Expansion;
use App\Models\WhiteCard;
use Tests\TestCase;

class WhiteCardTest extends TestCase
{

    /** @test */
    public function expansion_relationship_brings_back_expansion_type()
    {
        $whiteCard = WhiteCard::first();
        $this->assertInstanceOf(Expansion::class, $whiteCard->expansion);
    }
}

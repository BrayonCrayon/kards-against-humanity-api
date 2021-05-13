<?php

namespace Tests\Unit\WhiteCard;

use App\Models\Expansion;
use App\Models\WhiteCard;
use tests\TestCase;

class WhiteCardRelationshipTest extends TestCase
{

    /** @test */
    public function expansion_relationship_brings_back_expansion_type()
    {
        $whiteCard = WhiteCard::first();
        $this->assertInstanceOf(Expansion::class, $whiteCard->expansion);
    }
}

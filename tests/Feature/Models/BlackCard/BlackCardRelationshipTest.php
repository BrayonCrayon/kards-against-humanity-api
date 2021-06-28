<?php

namespace Tests\Unit\BlackCard;

use App\Models\BlackCard;
use App\Models\Expansion;
use Tests\TestCase;

class BlackCardRelationshipTest extends TestCase
{

    /** @test */
    public function expansion_relationship_brings_back_expansion_type()
    {
        $blackCard = BlackCard::first();
        $this->assertInstanceOf(Expansion::class, $blackCard->expansion);
    }
}

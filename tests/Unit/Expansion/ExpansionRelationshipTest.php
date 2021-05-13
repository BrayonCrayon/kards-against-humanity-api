<?php

namespace Tests\Unit\Expansion;

use App\Models\BlackCard;
use App\Models\Expansion;
use App\Models\WhiteCard;
use tests\TestCase;

class ExpansionRelationshipTest extends TestCase
{

    /** @test */
    public function white_cards_relationship_brings_back_white_card_type()
    {
        $expansion = Expansion::first();
        $this->assertInstanceOf(WhiteCard::class, $expansion->whiteCards->first());
    }


    /** @test */
    public function black_cards_relationship_brings_back_black_card_types()
    {
        $expansion = Expansion::first();
        $this->assertInstanceOf(BlackCard::class, $expansion->blackCards->first());
    }
}

<?php

namespace Tests\Feature\Models\Expansion;

use App\Models\BlackCard;
use App\Models\Expansion;
use App\Models\WhiteCard;
use Tests\TestCase;

class ExpansionTest extends TestCase
{
    /** @test */
    public function scope_query_ids_in_brings_back_correct_expansion()
    {
        $expansion = Expansion::factory()->create();
        $this->assertEquals(1, Expansion::idsIn([$expansion->id])->count());
        $this->assertEquals($expansion->id, Expansion::idsIn([$expansion->id])->first()->id);
    }


    /** @test */
    public function white_cards_relationship_brings_back_white_card_type()
    {
        $expansion = Expansion::factory()->hasWhiteCards()->create();
        $this->assertInstanceOf(WhiteCard::class, $expansion->whiteCards->first());
    }


    /** @test */
    public function black_cards_relationship_brings_back_black_card_types()
    {
        $expansion = Expansion::factory()->hasBlackCards()->create();
        $this->assertInstanceOf(BlackCard::class, $expansion->blackCards->first());
    }
}

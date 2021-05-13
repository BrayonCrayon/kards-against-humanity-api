<?php

namespace Tests\Unit\Expansion;

use App\Models\Expansion;
use tests\TestCase;

class ExpansionScopeQueryTest extends TestCase
{

    /** @test */
    public function scope_query_ids_in_brings_back_correct_expansion()
    {
        $expansion = Expansion::first();
        $this->assertCount(1, Expansion::idsIn([0 => $expansion->id])->get());
        $this->assertEquals($expansion->id, Expansion::idsIn([0 => $expansion->id])->get()->first()->id);
    }
}

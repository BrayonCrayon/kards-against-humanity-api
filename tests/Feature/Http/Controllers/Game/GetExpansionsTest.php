<?php

namespace Tests\Feature\Http\Controllers\Game;

use App\Models\Expansion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class GetExpansionsTest extends TestCase
{
    /** @test */
    public function it_can_fetch_expansions()
    {
        $expectedExpansion = Expansion::factory()->create();

        $this->getJson(route('api.expansions.index'))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment([
                'name' => $expectedExpansion->name,
                'created_at' => $expectedExpansion->created_at,
                'updated_at' => $expectedExpansion->updated_at,
            ]);
    }
}

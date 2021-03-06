<?php

namespace App\Http\Controllers\Game;

use App\Http\Controllers\Controller;
use App\Http\Resources\Game\ExpansionResource;
use App\Models\Expansion;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Cache;

class GetExpansionsController
{

    public function __invoke(Request $request): AnonymousResourceCollection
    {
        return ExpansionResource::collection(
            Cache::remember('expansions-all',
                Carbon::SECONDS_PER_MINUTE * Carbon::MINUTES_PER_HOUR * Carbon::HOURS_PER_DAY,
                fn() => Expansion::all()
            )
        );
    }
}

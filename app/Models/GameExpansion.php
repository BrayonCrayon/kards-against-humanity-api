<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;

class GameExpansion extends Pivot
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'game_expansions';

    protected $guarded = [];

    /*
     ********************************
     *        Relationships         *
     ********************************
     */

    /**
     * @return BelongsTo
     */
    public function game()
    {
        return $this->belongsTo(Game::class);
    }


    /**
     * @return BelongsTo
     */
    public function expansion()
    {
        return $this->belongsTo(Expansion::class);
    }
}

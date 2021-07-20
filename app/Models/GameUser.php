<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;

class GameUser extends Pivot
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'game_users';

    protected $guarded = [];

    /*
     ********************************
     *        Relationships         *
     ********************************
     */

    /**
     * @return BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo
     */
    public function game()
    {
        return $this->belongsTo(Game::class);
    }
}

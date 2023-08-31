<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserGameWhiteCard extends Model
{
    protected $guarded = [];
    use HasFactory;
    use SoftDeletes;

    protected $table = 'user_game_white_cards';

    /*
     ********************************
     *        Relationships         *
     ********************************
     */

    public function user() : BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function whiteCard() : BelongsTo
    {
        return $this->belongsTo(WhiteCard::class);
    }

    public function game() : BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function scopeSelected($query)
    {
        return $query->where('selected', true);
    }

    public function scopeGame($query, string $gameId)
    {
        return $query->where('game_id', $gameId);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserGameWhiteCards extends Model
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

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function whiteCard()
    {
        return $this->belongsTo(WhiteCard::class);
    }
}

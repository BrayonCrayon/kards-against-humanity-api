<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoundWinner extends Model
{
    use HasFactory;
    use HasFactory;

    protected $guarded = [];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function game()
    {
        return $this->belongsTo(Game::class);
    }

    public function whiteCard()
    {
        return $this->belongsTo(WhiteCard::class);
    }

    public function blackCard()
    {
        return $this->belongsTo(BlackCard::class);
    }
}

<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;

class GameBlackCards extends Pivot
{
    protected $guarded = [];
    use HasFactory;
    use SoftDeletes;

    protected $table = 'game_black_cards';

    /*
     ********************************
     *        Relationships         *
     ********************************
     */

    public function blackCard()
    {
        return $this->belongsTo(BlackCard::class);
    }
}

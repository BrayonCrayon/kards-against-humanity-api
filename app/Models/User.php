<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
    ];

    /*
     ********************************
     *        Relationships         *
     ********************************
     */

    /**
     * @return BelongsToMany
     */
    public function games()
    {
        return $this->belongsToMany(Game::class, 'game_users');
    }

    /**
     * @return BelongsToMany
     */
    public function whiteCards()
    {
        return $this->belongsToMany(WhiteCard::class, 'user_game_white_cards');
    }

    /**
     * @return BelongsToMany
     */
    public function blackCards()
    {
        return $this->belongsToMany(BlackCard::class, 'user_game_black_cards');
    }
}

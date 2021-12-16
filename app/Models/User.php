<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
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
     *          Attributes          *
     ********************************
     */

    /**
     * @return bool
     */
    public function getHasSubmittedWhiteCardsAttribute(): bool
    {
        return $this->whiteCardsInGame->where('selected', true)->count() > 0;
    }

    /**
     * @return Collection
     */
    public function getSubmittedWhiteCardIdsAttribute(): Collection
    {
        return $this->whiteCardsInGame->where('selected', true)->pluck('white_card_id');
    }

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
     * @return HasMany
     */
    public function whiteCardsInGame()
    {
        return $this->hasMany(UserGameWhiteCards::class);
    }
}

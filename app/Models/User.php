<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;


    protected $fillable = [
        'name',
    ];

    /*
     ********************************
     *          Attributes          *
     ********************************
     */

    public function getHasSubmittedWhiteCardsAttribute(): bool
    {
        return $this->hand->where('selected', true)->count() > 0;
    }

    public function getSubmittedWhiteCardIdsAttribute(): Collection
    {
        return $this->hand->where('selected', true)->pluck('white_card_id');
    }

    public function getScoreAttribute(): int
    {
        return $this->roundsWon->count();
    }

    /*
     ********************************
     *        Relationships         *
     ********************************
     */

    public function games() : BelongsToMany
    {
        return $this->belongsToMany(Game::class, 'game_users')
            ->as('gameState');
    }

    public function whiteCards() : BelongsToMany
    {
        return $this->belongsToMany(WhiteCard::class, 'user_game_white_cards')
            ->wherePivotNull('deleted_at');
    }

    public function hand() : HasMany
    {
        return $this->hasMany(UserGameWhiteCard::class);
    }

    public function roundsWon() : HasMany
    {
        return $this->hasMany(RoundWinner::class)->distinct('black_card_id');
    }


}

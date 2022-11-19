<?php

namespace App\Models;

use App\Models\Traits\UuidPrimaryKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

class Game extends Model
{
    use HasFactory;
    use SoftDeletes;
    use UuidPrimaryKey;

    protected $guarded = [];

    protected $casts = [
        'id' => 'string'
    ];

    protected $primaryKey = 'id';

    const HAND_LIMIT = 7;

    /*
     ********************************
     *        Relationships         *
     ********************************
     */

    public function getBlackCardAttribute()
    {
        return $this->blackCards->firstOrFail();
    }

    public function getBlackCardPickAttribute()
    {
        return $this->blackCard->pick;
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'game_users')
            ->as('gameState')
            ->withPivot(['redraw_count', 'is_spectator'])
            ->where('game_users.is_spectator', false)
            ->orderBy('id');
    }

    public function players(): BelongsToMany
    {
        return $this->users()->as('gameState')
            ->withPivot('redraw_count')
            ->orderBy('id');
    }

    public function spectators() : BelongsToMany
    {
        return $this->belongsToMany(User::class, 'game_users')
            ->as('gameState')
            ->withPivot(['is_spectator', 'redraw_count'])
            ->where('game_users.is_spectator', true)
            ->orderBy('id');
    }

    /**
     * @return BelongsToMany
     */
    public function expansions()
    {
        return $this->belongsToMany(Expansion::class, 'game_expansions');
    }

    public function gameBlackCards(): HasMany
    {
        return $this->hasMany(GameBlackCards::class);
    }

    public function blackCards(): BelongsToMany
    {
        return $this->belongsToMany(BlackCard::class, 'game_black_cards')
            ->whereNull('deleted_at')
            ->withTimestamps()
            ->withPivot(['deleted_at']);
    }

    public function getAvailableWhiteCardsAttribute(): Collection
    {
        return $this->expansions()
            ->with(['whiteCards' => function ($query) {
                return $query->whereNotIn('id', UserGameWhiteCard::whereGameId($this->id)->get()->pluck('white_card_id'));
            }])
            ->get()
            ->pluck('whiteCards')
            ->flatten();
    }

    public function deletedBlackCards(): BelongsToMany
    {
        return $this->belongsToMany(BlackCard::class, 'game_black_cards')
            ->whereNotNull('deleted_at')
            ->withTimestamps()
            ->withPivot(['deleted_at']);
    }

    public function judge(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'judge_id');
    }

    public function nonJudgeUsers() : BelongsToMany
    {
        return $this->players()->where('users.id', '<>', $this->judge_id);
    }

    public function scopeByCode($query, $gameCode)
    {
        return $query->where('code', $gameCode);
    }

    public function getPlayer(string $id) : User {
        return $this->players()->whereUserId($id)->first();
    }
}

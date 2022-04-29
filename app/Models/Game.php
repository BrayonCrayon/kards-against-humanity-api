<?php

namespace App\Models;

use App\Models\Traits\UuidPrimaryKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

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

    public function getCurrentBlackCardAttribute()
    {
        return $this->blackCards->firstOrFail();
    }

    public function getBlackCardPickAttribute()
    {
        return $this->currentBlackCard->pick;
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'game_users')
            ->as('gameState')
            ->withPivot('redraw_count')
            ->orderBy('id');
    }

    public function expansions(): BelongsToMany
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
        return $this->users()->where('users.id', '<>', $this->judge_id);
    }

    public function scopeByCode($query, $gameCode)
    {
        return $query->where('code', $gameCode);
    }

    public function getUser(string $id) : User {
        return $this->users()->whereUserId($id)->first();
    }
}

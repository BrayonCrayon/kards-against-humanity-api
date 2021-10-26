<?php

namespace App\Models;

use App\Models\Traits\UuidPrimaryKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
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

    /**
     * @return BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'game_users')->orderBy('id');
    }

    /**
     * @return BelongsToMany
     */
    public function expansions()
    {
        return $this->belongsToMany(Expansion::class, 'game_expansions');
    }

    /**
     * @return HasMany
     */
    public function gameBlackCards()
    {
        return $this->hasMany(GameBlackCards::class);
    }

    public function blackCards()
    {
        return $this->belongsToMany(BlackCard::class, 'game_black_cards')
            ->whereNull('deleted_at')
            ->withTimestamps()
            ->withPivot(['deleted_at']);
    }

    public function deletedBlackCards()
    {
        return $this->belongsToMany(BlackCard::class, 'game_black_cards')
            ->whereNotNull('deleted_at')
            ->withTimestamps()
            ->withPivot(['deleted_at']);
    }

    public function judge()
    {
        return $this->hasOne(User::class, 'id', 'judge_id');
    }
}

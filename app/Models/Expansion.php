<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Expansion extends Model
{
    use HasFactory;

    protected $guarded = [];

    /*
     ********************************
     *        Relationships         *
     ********************************
     */

    public function games()
    {
        return $this->hasMany(Game::class);
    }

    /**
     * @return HasMany
     */
    public function whiteCards()
    {
        return $this->hasMany(WhiteCard::class);
    }

    /**
     * @return HasMany
     */
    public function blackCards()
    {
        return $this->hasMany(BlackCard::class);
    }

    /*
     ********************************
     *        Scoped Queries        *
     ********************************
     */

    /**
     * @param $query
     * @param $ids
     * @return mixed
     */
    public function scopeIdsIn($query, $ids)
    {
        return $query->whereIn('id', $ids);
    }
}

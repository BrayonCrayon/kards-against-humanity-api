<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Game extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'id' => 'string'
    ];

    protected $primaryKey = 'id';

    /*
     ********************************
     *        Relationships         *
     ********************************
     */

    /**
     * @return BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'game_users');
    }

    /**
     * @return BelongsToMany
     */
    public function expansions()
    {
        return $this->belongsToMany(Expansion::class, 'game_expansions');
    }
}

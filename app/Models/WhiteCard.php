<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhiteCard extends Model
{
    use HasFactory;

    protected $guarded = [];

    /*
     ********************************
     *        Relationships         *
     ********************************
     */

    public function expansion() : BelongsTo
    {
        return $this->belongsTo(Expansion::class);
    }

}

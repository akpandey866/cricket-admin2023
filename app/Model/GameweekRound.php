<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class GameweekRound extends Model
{
    protected $table = 'gameweek_rounds';
    public function getBonusCard()
    {

        return $this->hasMany('App\Model\TeamPower', 'club_id');
    }
}

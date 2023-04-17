<?php

namespace App\Model;


use Illuminate\Database\Eloquent\Model;


class TeamOfTheWeek extends Model
{

    protected $table = 'team_of_the_week';


    public function get_scorecard()
    {

        return $this->hasMany('App\Model\FixtureScorcard', 'player_id', 'player_id');
    }
}

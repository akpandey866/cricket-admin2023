<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class FixtureScorcard extends Model
{
    protected $table = 'fixture_scorecards';
    protected $guarded = [];


    public function fixture()
    {
        return $this->belongsTo('App\Model\Fixture', 'fixture_id');
    }

    public function player()
    {
        return $this->belongsTo('App\Model\Player', 'player_id');
    }
}

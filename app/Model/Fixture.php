<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Fixture extends Model
{
    protected $table = 'fixtures';


    public function fixture_scorecard()
    {
        return $this->hasMany('App\Model\FixtureScorcard', 'fixture_id');
    }
    public function teamdata()
    {
        return $this->belongsTo('App\Model\Team', 'team');
    }
}

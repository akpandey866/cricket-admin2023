<?php

namespace App\Model;

use App;
use App\Model\PlayerFollow;
use Auth;
use Illuminate\Database\Eloquent\Model;

class Player extends Model
{
    protected $table = 'players';

    public function get_players()
    {
        $getList = Player::where('is_active', 1)->orderBy('full_name', 'ASC')->pluck('full_name', 'id')->all();
        return $getList;
    }
    public function get_players_by_club($club_id)
    {
        $getList = Player::where('is_active', 1)->where('club', $club_id)->orderBy('full_name', 'ASC')->select('full_name', 'id')->get();
        return $getList;
    }
    public function get_scorecard()
    {

        return $this->hasMany('App\Model\FixtureScorcard', 'player_id');
    }
    public function getFollowAttribute()
    {
        $checkFollowPlayer = 0;
        $isFollowed = 0;
        if (!empty(Auth::guard('web')->user())) {
            $checkFollowPlayer = PlayerFollow::where('player_id', $this->id)->where('user_id', Auth::guard('web')->user()->id)->count();
        }

        if ($checkFollowPlayer == 1) {
            $isFollowed = 1;
        }
        return $isFollowed;
    }
    public function get_FixtureVoting()
    {

        return $this->hasMany('App\Model\FixtureVoting', 'player_id');
    }
}

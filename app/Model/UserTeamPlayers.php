<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class UserTeamPlayers extends Model

{
    public $timestamps = true;
    protected $table = 'user_team_players';
    protected $fillable = ['team_id', 'player_id'];


    public function players()
    {
        return $this->belongsTo('App\Model\Player', 'player_id');
    }
    public function teams()
    {
        return $this->belongsTo('App\Model\UserTeams', 'team_id');
    }
}

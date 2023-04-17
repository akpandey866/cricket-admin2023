<?php
namespace App\Model; 

use Eloquent,Session,App,DB;

class UserTeamPlayers extends Eloquent 
    
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

<?php
namespace App\Model; 

use Eloquent,Session,App,DB;

class UserTeamsPlayerTrack extends Eloquent 
    
{
	public $timestamps = true;
	protected $table = 'user_teams_player_track';
    protected $fillable = ['team_id', 'player_id', 'created_at', 'updated_at'];


   public function players()
    {
        return $this->belongsTo('App\Model\Player', 'player_id');
    }
   public function teams()
    {
        return $this->belongsTo('App\Model\UserTeams', 'team_id');
    }


}

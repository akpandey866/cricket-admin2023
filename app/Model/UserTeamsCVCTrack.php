<?php
namespace App\Model; 

use Eloquent,Session,App,DB;

class UserTeamsCVCTrack extends Eloquent 
    
{
	public $timestamps = true;
	protected $table = 'user_teams_c_vc_track';
    protected $fillable = ['team_id', 'player_id', 'remove_date', 'c_vc', 'created_at', 'updated_at'];


   public function players()
    {
        return $this->belongsTo('App\Model\Player', 'player_id');
    }
   public function teams()
    {
        return $this->belongsTo('App\Model\UserTeams', 'team_id');
    }

     public function player()
    {
        return $this->belongsTo('App\Model\Player', 'player_id');
    }
}

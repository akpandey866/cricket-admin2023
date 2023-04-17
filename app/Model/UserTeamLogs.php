<?php
namespace App\Model; 

use Eloquent,Session,App,DB;

class UserTeamLogs extends Eloquent 
    
{
	public $timestamps = true;
	protected $table = 'user_team_logs';
    protected $fillable = ['id', 'log_type', 'user_id', 'club_id', 'team_id', 'created_at', 'updated_at'];

 //    public function getTeamPlayer(){
	// 	return $this->hasMany('App\Model\UserTeamPlayers','team_id');
	// }


	// public function get_team_by_club($club){ 
	// 	$clubList		=	UserTeams::where('club_id',$club)->pluck('my_team_name','id')->all();
	// 	return $clubList;
	// }
    

}

<?php
namespace App\Model; 

use Eloquent,Session,App,DB;

class UserTeamPlayerLogs extends Eloquent 
    
{
	public $timestamps = false;
	protected $table = 'user_team_player_logs';
    protected $fillable = ['id', 'team_id', 'player_id', 'created_at', 'updated_at'];

    public function player()
    {
        return $this->belongsTo('App\Model\Player', 'player_id');
    }

}

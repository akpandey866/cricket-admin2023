<?php
namespace App\Model; 

use Eloquent,Session,App,DB;

class UserTeamsGWExtraPTTrack extends Eloquent 
    
{
  	public $timestamps = true;
  	protected $table = 'user_teams_gw_extra_pt_track';
    protected $fillable = ['team_id', 'gw_extra_pt', 'gw_end_date', 'created_at', 'updated_at'];


   public function teams()
    {
        return $this->belongsTo('App\Model\UserTeams', 'team_id');
    }


}

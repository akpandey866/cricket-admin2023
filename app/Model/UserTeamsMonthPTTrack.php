<?php
namespace App\Model; 

use Eloquent,Session,App,DB;

class UserTeamsMonthPTTrack extends Eloquent 
    
{
  	public $timestamps = true;
  	protected $table = 'user_teams_month_pt_track';
    protected $fillable = ['team_id', 'month_pt', 'overall_pt', 'month_rank', 'overall_rank', 'month_end_date', 'created_at', 'updated_at'];


   public function teams()
    {
        return $this->belongsTo('App\Model\UserTeams', 'team_id');
    }


}

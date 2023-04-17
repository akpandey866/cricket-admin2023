<?php
namespace App\Model; 

use Eloquent,Session,App,DB;

class UserTeamValueLog extends Eloquent 
    
{
	public $timestamps = true;
	protected $table = 'user_team_value_log';
    protected $fillable = ['id', 'team_id', 'team_value', 'created_at', 'updated_at'];


}


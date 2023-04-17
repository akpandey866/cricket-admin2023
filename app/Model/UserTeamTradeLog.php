<?php
namespace App\Model; 

use Eloquent,Session,App,DB;

class UserTeamTradeLog extends Eloquent 
    
{
	public $timestamps = false;
	protected $table = 'user_team_trade_log';
    protected $fillable = ['id', 'team_id', 'no_of_trade', 'created_at', 'updated_at'];


}


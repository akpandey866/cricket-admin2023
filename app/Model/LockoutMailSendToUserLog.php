<?php
namespace App\Model; 

use Eloquent,Session,App,DB;

class LockoutMailSendToUserLog extends Eloquent 
    
{
	public $timestamps = true;
	protected $table = 'lockout_mail_send_to_user_log';
    protected $fillable = ['id', 'user_id', 'team_id', 'club_id', 'mail_send_date', 'created_at', 'updated_at'];

}

<?php
namespace App\Model; 

use Eloquent,Session,App,DB;

class SentNotification extends Eloquent 
    
{
	public $timestamps = true;
	protected $table = 'sent_notifications';
}

<?php
namespace App\Model; 

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Eloquent,DB,App;

class LockoutLog extends Eloquent 
{
	protected $table = 'lockout_logs';    
}

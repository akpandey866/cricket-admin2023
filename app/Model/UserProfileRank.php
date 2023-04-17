<?php
namespace App\Model; 

use Eloquent,Session,App,DB;

class UserProfileRank extends Eloquent 
    
{
	public $timestamps = true;
	protected $table = 'user_profile_rank';
}

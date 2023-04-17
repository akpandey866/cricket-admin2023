<?php
namespace App\Model; 
use Eloquent;
class AdminScript extends Eloquent
{
	protected $table = 'admin_scripts';
	public function getClubNameAttribute()
	{	
			
		$getName = User::where('id',$this->club_id)->value('club_name');
      	return $getName;
	}
	public function getFullNameAttribute()
	{	
			
		$getName = User::where('id',$this->club_id)->value('full_name');
      	return $getName;
	}

	public function images()
    {
        
        return $this->hasMany('App\Model\AdminScriptImage', 'admin_script_id');
    }
}

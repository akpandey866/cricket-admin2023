<?php
namespace App\Model;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Eloquent,DB,App;

class Club extends Eloquent implements AuthenticatableContract,
                                    AuthorizableContract,
                                    CanResetPasswordContract
{


	protected $table = 'clubs';





	/**
	* Function for get country list
	*
	* @param null
	*
	* return query
	*/
	public function get_club_list(){
		$clubList		=	DB::table('users')->where('is_active',1)->where('user_role_id',3)->where('is_deleted',0)->pluck('club_name','id')->all();
		return $clubList;
	}

	/**
	* Function for get club listing using mode
	*
	* @param null
	*
	* return query
	*/
	public function get_club_mode($mode = null){
		$clubList		=	DB::table('users')->where('is_active',1)->where('is_game_activate', 1)->where(['user_role_id'=>3,'game_mode'=>$mode])->where('is_deleted',0)->pluck('club_name','id')->all();
		return $clubList;
	}


	public function addNew($input)
    {
        $check = static::where('facebook_id',$input['facebook_id'])->first();


        if(is_null($check)){
            return static::create($input);
        }


        return $check;
    }
}

<?php
namespace App\Model;

use App;
use Eloquent;
use Illuminate\Database\Eloquent\Model;

class PaidUser extends Eloquent {
	protected $guarded = [];

	public function userdata() {
		return $this->belongsTo('App\Model\User', 'user_id');
	}

}

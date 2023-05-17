<?php

namespace App\Model;

use App;
use Illuminate\Database\Eloquent\Model;

class PaidUser extends Model
{
    protected $guarded = [];

    public function userdata()
    {
        return $this->belongsTo('App\Model\User', 'user_id');
    }
}

<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Eloquent, DB, App;

class VerifyUser extends Model
{

    protected $table = 'verify_users';

    public function playerData()
    {
        return $this->belongsTo('App\Model\Player', 'player_id');
    }
}

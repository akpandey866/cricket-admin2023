<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Eloquent, DB, App;

class MultiPlayer extends Model
{
    //protected $table = 'multi_player';
    public function getSalary()
    {
        return $this->hasOne('App\Model\MultiPlayerSalary', 'multi_player_id');
    }
}

<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Availability extends Model
{
    protected $table = 'availabilities';

    public function player_data()
    {
        return $this->belongsTo('App\Model\Player', 'player');
    }
}

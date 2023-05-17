<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class VerifyUser extends Model
{

    protected $table = 'verify_users';

    public function playerData()
    {
        return $this->belongsTo('App\Model\Player', 'player_id')
            ->leftJoin('dropdown_managers', 'dropdown_managers.id', '=', 'players.position')
            ->select('dropdown_managers.name', 'players.svalue');
    }
}

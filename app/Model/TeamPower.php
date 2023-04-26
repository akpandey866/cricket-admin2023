<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class TeamPower extends Model
{
    protected $table = 'team_powers';
    public function getTeamPlayer()
    {
        return $this->hasMany('App\Model\TeamPowerPlayer', 'team_power_id')
            ->leftJoin('players', 'team_power_players.player_id', '=', 'players.id')
            ->select('team_power_players.*', 'players.full_name');
    }
}

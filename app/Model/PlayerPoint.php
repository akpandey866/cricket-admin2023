<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class PlayerPoint extends Model
{
    protected $table = 'player_points';
    protected $fillable = ['fixture_id', 'inning'];
}

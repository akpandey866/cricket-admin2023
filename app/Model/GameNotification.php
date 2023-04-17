<?php

namespace App\Model;

use App;
use Illuminate\Database\Eloquent\Model;

class GameNotification extends Model
{
    public $timestamps = true;
    protected $table = 'game_notifications';
}

<?php
namespace App\Model;

use App;
use Eloquent;

class GameUserNotification extends Eloquent {
	public $timestamps = true;
	protected $table = 'game_user_notifications';
}

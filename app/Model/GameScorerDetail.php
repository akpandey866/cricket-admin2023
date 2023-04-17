<?php
namespace App\Model; 
use Eloquent;
class GameScorerDetail extends Eloquent
{
	protected $table = 'game_scorer_details';

	protected $fillable = ['club_id','time_from','time_till','day','agree'];
}

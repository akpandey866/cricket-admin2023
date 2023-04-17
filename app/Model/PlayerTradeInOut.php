<?php
namespace App\Model; 
use Eloquent;
/**
 * Contact Model
 */
 
class PlayerTradeInOut extends Eloquent   {
	
	/**
	 * The database collection used by the model.
	 *
	 * @var string
	 */
 
protected $table = 'player_trade_in_out';


    public function player()
    {
        return $this->belongsTo('App\Model\Player', 'player_id');
    }

    public function team_data()
	{
	    return $this->belongsTo('App\Model\UserTeams','team_id');
	}

    public function getTeamNameAttribute()
	{	
		
	    $getName  = UserTeams::where('user_teams.id',$this->team_id)->value('my_team_name');
      	return $getName;
	}

	public function getUserNameAttribute()
	{	
			
		$userId = UserTeams::where('user_teams.id',$this->team_id)->value('user_id');
	    $getName  = User::where('id',$userId)->value('full_name');
      	return $getName;
	}

	public function getUserSlugAttribute()
	{	
			
		$userId = UserTeams::where('user_teams.id',$this->team_id)->value('user_id');
	    $getName  = User::where('id',$userId)->value('slug');
      	return $getName;
	}

	public function getPlayerNameAttribute()
	{	
			
	    $getName  = Player::where('id',$this->player_id)->value('full_name');
      	return $getName;
	}

} // end Contact class

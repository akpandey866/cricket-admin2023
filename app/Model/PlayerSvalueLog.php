<?php
namespace App\Model; 

use Eloquent,Session,App,DB;

class PlayerSvalueLog extends Eloquent 
    
{
	public $timestamps = true;
	protected $table = 'player_svalue_log';
    protected $fillable = ['player_id', 'svalue', 'created_at', 'updated_at'];


   public function players()
    {
        return $this->belongsTo('App\Model\Player', 'player_id');
    }

}

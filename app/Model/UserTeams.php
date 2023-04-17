<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class UserTeams extends Model

{
    public $timestamps = true;
    protected $table = 'user_teams';
    protected $fillable = ['user_id', 'player_ids', 'created_at', 'updated_at'];

    public function getTeamPlayer()
    {
        return $this->hasMany('App\Model\UserTeamPlayers', 'team_id');
    }
    public function userdata()
    {
        return $this->belongsTo('App\Model\User', 'user_id');
    }

    public function get_paid_users()
    {
        return $this->belongsTo('App\Model\PaidUser', 'user_id', 'user_id');
    }

    public function get_team_by_club($club)
    {
        $clubList        =    UserTeams::where('club_id', $club)->pluck('my_team_name', 'id')->all();
        return $clubList;
    }
    public function clubdata()
    {
        return $this->belongsTo('App\Model\User', 'club_id');
    }

    public function getUserSlugAttribute()
    {
        $userSlug = User::where('id', $this->user_id)->value('slug');
        return $userSlug;
    }
}

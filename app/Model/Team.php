<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Team extends Model
{

    protected $table = 'teams';


    /**
     * Function for get Team List
     *
     * @param null
     *
     * return query
     */
    public function get_team()
    {
        $clubList        =    Team::where('is_active', 1)->pluck('name', 'id')->all();
        return $clubList;
    }

    /**
     * Function for get Team List by club id
     *
     * @param null
     *
     * return query
     */
    public function get_team_by_club($club)
    {
        $clubList        =    Team::where('is_active', 1)->where('club', $club)->pluck('name', 'id')->all();
        return $clubList;
    }

    public function get_grade()
    {
        return $this->belongsTo('App\Model\Grade', 'grade_name');
    }
}

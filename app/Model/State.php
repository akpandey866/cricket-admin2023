<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class State extends Model
{

    protected $table = 'states';
    /**
     * Function for get country list
     *
     * @param null
     *
     * return query
     */
    public static function get_states($id = null)
    {
        $states = State::where('country_id', $id)->pluck('name', 'id')->all();
        return $states;
    }
}

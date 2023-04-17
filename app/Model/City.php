<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class City extends Model
{

    protected $table = 'cities';
    /**
     * Function for get cities list
     *
     * @param null
     *
     * return query
     */
    public static function get_cities($id = null)
    {
        $cities = City::where('state_id', $id)->pluck('name', 'id')->all();
        return $cities;
    }
}

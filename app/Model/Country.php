<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;


class Country extends Model
{

    protected $table = 'countries';
    /**
     * Function for get country list
     *
     * @param null
     *
     * return query
     */
    public static function get_country()
    {
        $countries = Country::pluck('name', 'id')->all();
        return $countries;
    }
}

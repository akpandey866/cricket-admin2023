<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Eloquent, Session, App, DB;

/**
 * DropDown Model
 */

class DropDown extends Model
{

    /**
     * The database table used by the model.
     *
     * @var string
     */

    protected $table = 'dropdown_managers';


    /**
     * Function to list using type
     *
     * @param null
     *
     * @return query
     */
    public static function get_master_list($dropdown_type = null)
    {
        $result = DB::table('dropdown_managers')->where('dropdown_type', $dropdown_type)->where('is_active', 1)->orderBy('name', 'ASC')->select('name', 'id')->get();
        return $result;
    }

    public static function get_voting_list($dropdown_type = null)
    {
        $result    =    DB::table('dropdown_managers')->where('dropdown_type', $dropdown_type)->where('is_active', 1)->orderBy('name', 'DESC')->pluck('name', 'id')->all();
        return $result;
    }
}// end DropDown class

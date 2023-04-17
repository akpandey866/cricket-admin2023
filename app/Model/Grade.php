<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Eloquent, DB, App;

class Grade extends Model
{
    protected $table = 'grades';
    public static function get_grade($club_id = null)
    {
        // echo $club_id;die;
        if (!empty($club_id)) {
            $result    =    DB::table('grades')->where('is_active', 1)->where('club', $club_id)->orderBy('grade', 'ASC')->pluck('grade', 'id')->all();
        } else {
            $result    =    DB::table('grades')->where('is_active', 1)->orderBy('grade', 'ASC')->pluck('grade', 'id')->all();
        }
        return $result;
    }



    public function club_name()
    {
        return $this->belongsTo('App\Model\User', 'club');
    }
}

<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Branding extends Model
{
    protected $table = 'game_branding';

    public function getClubNameAttribute()
    {

        $clubName = User::where('id', $this->club_id)->value('club_name');
        return $clubName;
    }
}

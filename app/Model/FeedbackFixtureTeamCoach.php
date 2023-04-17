<?php
namespace App\Model; 

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Eloquent,DB,App;

class FeedbackFixtureTeamCoach extends Eloquent 
{

	protected $table = 'feedback_fixture_team_coaches';

	public function fixturePoints()
    {
        return $this->hasMany('App\Model\FeedbackFixturePoint', 'coach_id', 'user_id')
            ->leftJoin('players' , 'feedback_fixture_points.player_id' , '=' , 'players.id')
            ->leftJoin('fixtures' , 'feedback_fixture_points.fixture_id' , '=' , 'fixtures.id')
            ->leftJoin('grades', 'grades.id', '=', 'fixtures.grade')
            ->leftJoin('teams', 'teams.id', '=', 'fixtures.team')
            ->leftJoin('users', 'users.id', '=', 'feedback_fixture_points.coach_id')
            ->select('feedback_fixture_points.*','players.full_name as player_name','fixtures.start_date as start_date','fixtures.end_date as end_date', 'grades.grade as grade','teams.name as team_name','users.full_name as coach_name')
            ->groupBy(['fixture_id', 'player_id']);
    }
}

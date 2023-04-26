<?php

/**
 * Forum Controller
 */

namespace App\Http\Controllers;

use App\Model\FixtureVoting;
use App\Model\Player;
use App\Model\Fixture;
use App\Model\Team;
use App\Model\DropDown;

use Illuminate\Http\Request;


class FixtureVotingController extends BaseController
{

    private $userDetail = null;
    public function __construct()
    {
        $this->userDetail = auth('api')->user();
    }
    /**
     * Function for edit gameprize
     *
     * @param null
     *
     * @return view page.
     */
    public function index()
    {
        $result = FixtureVoting::leftJoin('fixtures', 'fixtures.id', 'fixture_voting.fixture_id')
            ->leftJoin('teams', 'fixtures.team', '=', 'teams.id')
            ->leftJoin('users', 'users.id', '=', 'fixture_voting.user_id')
            ->where('fixture_voting.club_id', $this->userDetail->id)
            ->select('fixture_voting.id', 'fixture_voting.fixture_id', 'fixtures.start_date', 'fixtures.end_date', 'teams.name  as team_name', 'users.full_name as username')
            ->groupBy('fixture_voting.fixture_id')
            ->get();

        $data['success'] = true;
        $data['status'] = 200;
        $data['data'] = $result;
        return response()->json($data);
    }
    public function getFixtureVote(Request $request)
    {
        $fixtureId = $request->fixture_id;
        $drop_down =    new DropDown();
        $votingListing = $drop_down->get_voting_list("fixture-voting");
        $getPlayerId = FixtureVoting::where('fixture_id', $fixtureId)->groupBy('player_id')->pluck('player_id', 'player_id')->all();
        $result = Player::whereIn('id', $getPlayerId)->with(['get_FixtureVoting' => function ($q) use ($fixtureId) {
            $q->where('fixture_id', '=', $fixtureId);
        }])
            ->select('players.id', 'players.full_name')->get();
        $data = [];
        $map = $result->map(function ($item) use ($votingListing) {
            $data['full_name'] = $item->full_name;
            $votingValue = 0;
            foreach ($votingListing as $key1 => $value) {
                $data['three_votes'] = $item['get_FixtureVoting']->where('voting_id', 90)->count();
                $data['two_votes'] = $item['get_FixtureVoting']->where('voting_id', 89)->count();
                $data['one_votes'] = $item['get_FixtureVoting']->where('voting_id', 88)->count();
                $getVlasdasd = $item['get_FixtureVoting']->where('voting_id', $key1)->count();
                $votingValue +=  (int)preg_replace('/[^0-9]/', '', $value) * $getVlasdasd;
            }
            $data['total_votes'] = $votingValue;
            return $data;
        });

        $data['success'] = true;
        $data['status'] = 200;
        $data['data'] = $map;
        return response()->json($data);
    }

    public function getAverageFitureVote(Request $request)
    {
        $fixtureId = $request->fixture_id;
        $drop_down =    new DropDown();
        $votingListing = $drop_down->get_voting_list("fixture-voting");
        $getPlayerId = FixtureVoting::where('fixture_id', $fixtureId)->groupBy('player_id')->pluck('player_id', 'player_id')->all();

        $result = Player::whereIn('id', $getPlayerId)->with(['get_FixtureVoting' => function ($q) use ($fixtureId) {
            $q->where('fixture_id', '=', $fixtureId);
        }])
            ->select('players.id', 'players.full_name')->get();

        $data = [];
        $map = $result->map(function ($item) use ($votingListing, $fixtureId) {
            $data['full_name'] = $item->full_name;
            $votingValue = 0;
            foreach ($votingListing as $key1 => $value) {
                $getVlasdasd = $item['get_FixtureVoting']->where('voting_id', $key1)->count();
                $votingValue +=  (int)preg_replace('/[^0-9]/', '', $value) * $getVlasdasd;
            }
            $data['total_votes'] = $votingValue;
            $getAverage = FixtureVoting::where('fixture_id', $fixtureId)->avg('votes');

            $data['avg_votes'] = number_format($getAverage, 2);
            return $data;
        });
        $data['success'] = true;
        $data['status'] = 200;
        $data['data'] = $map;
        return response()->json($data);
    }


    public function userPlayerRating(Request $request)
    {
        $fixtureId = $request->fixture_id;
        $result = FixtureVoting::leftJoin('users', 'users.id', 'fixture_voting.user_id')
            ->leftJoin('dropdown_managers', 'dropdown_managers.id', '=', 'fixture_voting.voting_id')
            ->where('fixture_id', $fixtureId)
            ->select('fixture_voting.*', 'users.full_name as username', 'dropdown_managers.name as rating_star')
            ->groupBy('user_id')
            ->get();
        $data['success'] = true;
        $data['status'] = 200;
        $data['data'] = $result;
        return response()->json($data);
    }

    public function userPlayerRatingModal(Request $request)
    {
        $fixtureId = $request->fixture_id;
        $userId = $request->user_id;

        $result = FixtureVoting::leftJoin('players', 'players.id', 'fixture_voting.player_id')
            ->leftJoin('dropdown_managers', 'dropdown_managers.id', '=', 'fixture_voting.voting_id')
            ->where('fixture_id', $fixtureId)
            ->where('user_id', $userId)
            ->select('fixture_voting.*', 'players.full_name as player_name', 'dropdown_managers.name as rating_star')
            ->get();

        $data['success'] = true;
        $data['status'] = 200;
        $data['data'] = $result;
        return response()->json($data);
    }
} // end ClubController class

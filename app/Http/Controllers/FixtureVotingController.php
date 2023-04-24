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

    public function teamFixtureVoting(Request $request)
    {
        //$fixtureId = $request->fixture_id;
        $team =    new Team();
        $drop_down =    new DropDown();
        $votingListing = $drop_down->get_voting_list("fixture-voting");
        $teamListing = $team->get_team_by_club(Auth::guard('admin')->user()->id);


        $getFixtures = FixtureVoting::leftJoin('fixtures', 'fixtures.id', 'fixture_voting.fixture_id')
            ->where('fixture_voting.club_id', Auth::guard('admin')->user()->id)
            ->groupBy('fixture_voting.fixture_id')
            ->pluck('fixture_id', 'fixture_id')->all();

        $getPlayerId = FixtureVoting::whereIn('fixture_id', $getFixtures)->groupBy('player_id')->pluck('player_id', 'player_id')->all();

        $getFixtureIds = FixtureVoting::where('club_id', Auth::guard('admin')->user()->id)
            ->groupBy('fixture_id')
            ->pluck('fixture_id', 'fixture_id')->all();

        $getTeams = Fixture::leftJoin('teams', 'teams.id', 'fixtures.team')
            ->leftJoin('grades', 'grades.id', 'fixtures.grade')
            ->whereIn('fixtures.id', $getFixtureIds)
            ->select('teams.name as team_name', 'grades.grade as grade_name', 'fixtures.id as fixture_id', 'teams.id as team_id')
            ->groupBy('teams.id')
            ->get();


        $result = Player::leftJoin('teams', 'teams.id', 'players.team_id')
            ->leftJoin('grades', 'grades.id', 'teams.grade_name')
            ->whereIn('players.id', $getPlayerId)->with('get_FixtureVoting')
            ->select('players.id', 'players.full_name', 'teams.name as team_name', 'grades.grade as grade_name')
            ->get();
        // prd($getTeams);
        return View::make('admin.fixture_voting.team_fixture_voting', compact('result', 'votingListing', 'getTeams'));
    }

    public function teamFixtureVotingModal(Request $request)
    {
        $teamId = $request->team_id;
        $drop_down =    new DropDown();
        $votingListing = $drop_down->get_voting_list("fixture-voting");

        $getFixtures = FixtureVoting::leftJoin('fixtures', 'fixtures.id', 'fixture_voting.fixture_id')
            ->where('fixture_voting.club_id', Auth::guard('admin')->user()->id)
            ->where('fixtures.team', $teamId)
            ->groupBy('fixture_voting.fixture_id')
            ->pluck('fixture_id', 'fixture_id')->all();

        $getPlayerId = FixtureVoting::whereIn('fixture_id', $getFixtures)->groupBy('player_id')->pluck('player_id', 'player_id')->all();

        $result = Player::whereIn('id', $getPlayerId)->with(['get_FixtureVoting' => function ($q) use ($getFixtures) {
            $q->whereIn('fixture_id', $getFixtures);
        }])
            ->select('players.id', 'players.full_name')->get();
        return View::make('admin.fixture_voting.team_fixture_voting_modal', compact('result', 'votingListing'));
    }

    public function overallPlayerVoting(Request $request)
    {
        $searchData = Input::get();

        unset($searchData['display']);

        unset($searchData['_token']);

        if (isset($searchData['order'])) {

            unset($searchData['order']);
        }

        if (isset($searchData['sortBy'])) {

            unset($searchData['sortBy']);
        }

        $drop_down =    new DropDown();
        $votingListing = $drop_down->get_voting_list("fixture-voting");
        $getPlayerId = FixtureVoting::where('club_id', Auth::guard('admin')->user()->id)
            ->groupBy('player_id')->pluck('player_id', 'player_id')->all();


        $getFixtures = FixtureVoting::leftJoin('fixtures', 'fixtures.id', 'fixture_voting.fixture_id')
            ->where('fixture_voting.club_id', Auth::guard('admin')->user()->id)
            ->groupBy('fixture_voting.fixture_id')
            ->pluck('fixture_id', 'fixture_id')->all();

        $result = Player::whereIn('id', $getPlayerId)->with('get_FixtureVoting')
            ->select('players.id', 'players.full_name')->paginate(10);
        return View::make('admin.fixture_voting.overall_player_voting', compact('result', 'votingListing'));
    }
} // end ClubController class

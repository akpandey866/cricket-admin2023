<?php

/**
 * Forum Controller
 */

namespace App\Http\Controllers;

use App\Model\Fixture;
use App\Model\User;
use App\Model\Player;
use App\Model\TeamPlayer;
use App\Model\FixtureScorcard;
use Illuminate\Http\Request;

class TeamPlayerController extends Controller
{
    private $userDetail = null;
    public function __construct()
    {
        $this->userDetail = auth('api')->user();
    }
    /**
     * Function for display all event
     *
     * @param null
     *
     * @return view page.
     */
    public function index(Request $request)
    {
        $clubId = $this->userDetail->id;
        $fixtureId = $request->fixture_id;
        $DB                     =     Player::query();
        $sortBy = "players.full_name";
        $order = 'ASC';
        if (!empty($request->sort)) {
            $explodeOrderBy = explode("%", $request->sort);
            $sortBy = current($explodeOrderBy);
            $order = end($explodeOrderBy);
        }
        $pageLimit = 50;
        if (!empty($request->limit)) {
            $pageLimit = $request->limit;
        }
        $playerlist    =    $DB->where('players.club', $clubId)
            ->leftJoin('teams', 'players.team_id', '=', 'teams.id')
            ->select('players.*', 'teams.name  as team_name')
            ->where('players.is_active', 1)
            ->orderBy($sortBy, $order)
            ->orderBy('players.full_name', 'ASC')
            ->paginate($pageLimit);

        $ddPlayerList = Player::Where('players.club', $clubId)
            ->leftJoin('teams', 'players.team_id', '=', 'teams.id')
            ->select('players.*', 'teams.name  as team_name')
            ->where('players.is_active', 1)
            ->orderBy('players.full_name', 'ASC')
            ->pluck("full_name", 'id')->all();

        $is_lock = TeamPlayer::where('fixture_id', $fixtureId)->orderBy('id', 'DESC')->value('is_lock');
        $playerCount = TeamPlayer::where('fixture_id', $fixtureId)->count();
        $club =    new User();
        $cludDetails =    $club->get_club_list();
        $playerIds = TeamPlayer::where('fixture_id', $fixtureId)->pluck('player_id', 'player_id')->all();
        $fixtureDetails = Fixture::where('fixtures.id', $fixtureId)
            ->leftJoin('teams', 'fixtures.team', '=', 'teams.id')
            ->leftJoin('grades as grades', 'fixtures.grade', '=', 'grades.id')
            ->leftJoin('dropdown_managers as dropdown_managers2', 'fixtures.match_type', '=', 'dropdown_managers2.id')
            ->select('grades.grade', 'teams.name  as team_name', 'fixtures.start_date', 'fixtures.start_date', 'fixtures.end_date', 'dropdown_managers2.name  as match_type', 'team')
            ->first();

        $result =     TeamPlayer::leftJoin('players', 'team_players.player_id', '=', 'players.id')
            ->select('team_players.*', 'players.full_name as player_name')
            ->where('fixture_id', $fixtureId)
            ->where('players.is_active', 1)
            ->orderBy("orderno", 'asc')
            ->get();
        $data['success'] = true;
        $data['status'] = 200;
        $data['data'] = $playerlist;
        $data['picked_player'] = $result;
        $data['playerIds'] = $fixtureId;
        $data['is_lock'] = $is_lock;
        $data['fixtureDetails'] = $fixtureDetails;
        $data['playerCount'] = $playerCount;
        $data['ddPlayerList'] = $ddPlayerList;
        $data['message'] = "Data fetched sucessfully.";
        return response()->json($data);
    }

    public function teamPlayerListing(Request $request)
    {
        $playerlist    =    Player::where('players.club', $this->userDetail->id)
            ->leftJoin('teams', 'players.team_id', '=', 'teams.id')
            // ->select('players.*', 'teams.name  as team_name')
            ->select('players.full_name', 'players.id')
            ->where('players.is_active', 1)
            ->orderBy('players.full_name', 'ASC')
            ->get();
        $data = [];
        $map = $playerlist->map(function ($item) {

            $data['value'] = $item->id;
            $data['text'] = $item->full_name;
            return $data;
        });
        $data['success'] = true;
        $data['status'] = 200;
        $data['data'] = $map;
        $data['message'] = "Data fetched sucessfully.";
        return response()->json($data);
    }
    public function saveTeamPlayer(Request $request)
    {
        $playersIds = $request->player;
        foreach ($playersIds as $key => $value) {
            $obj =  new TeamPlayer();
            $obj->player_id =  $value;
            $obj->fixture_id =  $request->fixtureId;
            $obj->save();
        }

        $data['success'] = true;
        $data['status'] = 200;
        $data['message'] = "Player have been saved successfully.";
        return response()->json($data);
    } //end saveForum

    /**
     * Function for mark a Forum as deleted
     *
     * @param $Id as id of Forum
     *
     * @return redirect page.
     */

    public function deleteTeamPlayer(Request $request)
    {
        $playerId = $request->playerId;
        $fixtureId = $request->fixtureId;
        $id = TeamPlayer::where(['player_id' => $playerId, 'fixture_id' => $fixtureId])->value('id');
        $userDetails    =    TeamPlayer::findOrFail($id);
        if ($userDetails) {
            FixtureScorcard::where(['player_id' => $userDetails->player_id, 'fixture_id' => $fixtureId])->delete();
            $result =     TeamPlayer::leftJoin('players', 'team_players.player_id', '=', 'players.id')
                ->select('team_players.*', 'players.full_name as player_name')
                ->where('fixture_id', $fixtureId)
                ->where('players.is_active', 1)
                ->orderBy("orderno", 'asc')
                ->get();
        }
        $data['success'] = true;
        $data['status'] = 200;
        $data['picked_player'] = $result;
        $data['message'] = "Player have been deleted successfully.";
        return response()->json($data);
    } // end deleteTeamPlayer


    public function saveMultiTeamPlayer(Request $request)
    {
        $fixtureId = $request->fixtureId;
        $playersId = $request->playerId;
        foreach ($playersId as $key => $value) {
            $obj =  new TeamPlayer();
            $obj->player_id =  $value;
            $obj->fixture_id =  $fixtureId;
            $obj->save();
        }

        $result =     TeamPlayer::leftJoin('players', 'team_players.player_id', '=', 'players.id')
            ->select('team_players.*', 'players.full_name as player_name')
            ->where('fixture_id', $fixtureId)
            ->where('players.is_active', 1)
            ->orderBy("orderno", 'asc')
            ->get();
        $data['success'] = true;
        $data['status'] = 200;
        $data['picked_player'] = $result;
        $data['message'] = "Players added successfully.";
        return response()->json($data);
    }
    public function lockPlayer($fixture_id = null)
    {
        TeamPlayer::where('fixture_id', $fixture_id)->update(['is_lock' => 1]);
        $data['success'] = true;
        $data['status'] = 200;
        $data['message'] = "Player have been locked successfully.";
        return response()->json($data);
    }

    public function validateNumeric($value)
    {
        if (is_numeric($value)) {
            return trim($value) != '' && $value > 0;
        }
        return false;
    }

    public function saveTeamPlayerDirect(Request $request)
    {
        $playerId = $request->playerId;
        $fixtureId = $request->fixtureId;

        $checkUserExists = TeamPlayer::where('player_id', $playerId)->where('fixture_id', $fixtureId)->first();

        if (!empty($checkUserExists)) {
            $userModel        =    TeamPlayer::where(['player_id' => $playerId, 'fixture_id' => $fixtureId])->delete();
            FixtureScorcard::where(['player_id' => $playerId, 'fixture_id' => $fixtureId])->delete();
            return 1;
        }

        $obj =  new TeamPlayer();
        $obj->player_id =  $playerId;
        $obj->fixture_id =  $fixtureId;
        $obj->save();

        $lastOrder = TeamPlayer::where('fixture_id', $fixtureId)->orderBy('orderno', 'desc')->value('orderno');
        if (!empty($lastOrder)) {
            TeamPlayer::where('id', $obj->id)->update(['orderno' => ++$lastOrder]);
        } else {
            TeamPlayer::where('id', $obj->id)->update(['orderno' => 1]);
        }

        $userId    =    $obj->id;
        $sortBy = "players.full_name";
        $order = 'ASC';
        if (!empty($userId)) {
            $result =     TeamPlayer::leftJoin('players', 'team_players.player_id', '=', 'players.id')
                ->select('team_players.*', 'players.full_name as player_name')
                ->where('fixture_id', $fixtureId)
                ->where('players.is_active', 1)
                ->orderBy("orderno", 'asc')
                ->get();
        }
        $data['success'] = true;
        $data['status'] = 200;
        $data['picked_player'] = $result;
        $data['message'] = "Player have been added successfully.";
        return response()->json($data);
    }
}// end ClubController class

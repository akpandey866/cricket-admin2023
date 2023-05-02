<?php

namespace App\Http\Controllers;

use App\Model\Bracket;
use App\Model\BracketRound;
use App\Model\User;
use App\Model\UserTeams;
use App\Model\BracketRoundPoint;
use App\Model\UserTeamsGWExtraPTTrack;
use App\Model\BracketMatch;
use App\Model\TeamOfTheWeek;
use App\Model\TeamPower;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;


class BracketBattleController extends Controller
{
    private $userDetail = null;
    public function __construct()
    {
        $this->userDetail = auth('api')->user();
    }
    public function index(Request $request)
    {
        $temp_club_data = User::where('id', $this->userDetail->id)->first();
        $lastGameweekArray = [];
        $roundIds = [];
        $lastGameweeks = BracketRound::where('club_id', $this->userDetail->id)->get();
        if (!empty($lastGameweeks)) {
            foreach ($lastGameweeks as $key => $value) {
                $lastGameweekArray[] = $value->gameweeks;
                $roundIds[$value->round] = $value->round;
            }
        }
        $combineAllGW = implode(',', $lastGameweekArray);
        $combineGWArray = explode(',', $combineAllGW);
        $temp_lockout_start_date = Carbon::parse($temp_club_data->lockout_start_date)
            ->startOfWeek();
        $temp_lockout_end_date = Carbon::parse($temp_club_data->lockout_end_date)
            ->endOfWeek();
        // GW - GWP - GWR - Trades Made - Overall Pts - Overall Rank - Team Value - More

        $result = array();
        $i = 1;
        $allGameweeksNumber = [];
        $newGW = [];
        while (Carbon::parse($temp_lockout_start_date) < $temp_lockout_end_date) {

            $result[$i]['gw_start'] = $temp_lockout_start_date->copy()
                ->startOfWeek();
            $result[$i]['gw_end'] = $temp_lockout_start_date->copy()
                ->endOfWeek();
            $allGameweeksNumber[$i] = "GW# " . $i;
            $newGW[$i] = $i;
            $i++;
            $temp_lockout_start_date->addWeek();
        }

        $newArray = array_diff($newGW, $combineGWArray);
        if (!empty($newArray)) {
            unset($allGameweeksNumber);
            foreach ($newArray as $key => $value) {
                $allGameweeksNumber[$value] = "GW# " . $value;
            }
        }

        $getRound = BracketRound::where('club_id', $this->userDetail->id)->orderby('round', 'asc')->get();
        $bracketDetails = Bracket::where('club_id', $this->userDetail->id)->first();

        $getRoundList = $this->getRoundUsingStructure($bracketDetails->structure);

        $getRoundFromTable = BracketRound::where('club_id', $this->userDetail->id)->pluck('round', 'round')->all();
        if (!empty($getRoundFromTable)) {
            $getRoundList = array_diff_key($getRoundList, $getRoundFromTable);
        }
        $roundConfigData = array(
            1 => 'Round 1',
            2 => 'Round 2',
            3 => 'Round 3',
            4 => 'Round 4',
            5 => 'Round 5',
            6 => 'Round 6',
            7 => 'Round 7',
            8 => 'Round 8',
            9 => 'Round 8',
            10 => 'Round 10',
        );
        if (!empty($roundIds)) {
            $roundConfigData = array_diff_key($roundConfigData, $roundIds);
        }
        $data['success'] = true;
        $data['status'] = 200;
        $data['data'] = $getRound;
        $data['bracket_details'] = $bracketDetails;
        return response()->json($data);
    }
    public function getRoundUsingStructure($value = null)
    {
        $roudList = [];
        if ($value == 8) {
            $roudList = array(1 => 'Round 1', 2 => 'Round 2', 3 => 'Round 3');
        }
        if ($value == 16) {
            $roudList = array(1 => 'Round 1', 2 => 'Round 2', 3 => 'Round 3', 4 => 'Round 4');
        }
        if ($value == 32) {
            $roudList = array(1 => 'Round 1', 2 => 'Round 2', 3 => 'Round 3', 4 => 'Round 4', 5 => 'Round 5');
        }
        if ($value == 64) {
            $roudList = array(1 => 'Round 1', 2 => 'Round 2', 3 => 'Round 3', 4 => 'Round 4', 5 => 'Round 5', 6 => 'Round 6');
        }
        if ($value == 128) {
            $roudList = array(1 => 'Round 1', 2 => 'Round 2', 3 => 'Round 3', 4 => 'Round 4', 5 => 'Round 5', 6 => 'Round 6', 7 => 'Round 7');
        }
        if ($value == 256) {
            $roudList = array(1 => 'Round 1', 2 => 'Round 2', 3 => 'Round 3', 4 => 'Round 4', 5 => 'Round 5', 6 => 'Round 6', 7 => 'Round 7', 8 => 'Round 8');
        }
        if ($value == 512) {
            $roudList = array(1 => 'Round 1', 2 => 'Round 2', 3 => 'Round 3', 4 => 'Round 4', 5 => 'Round 5', 6 => 'Round 6', 7 => 'Round 7', 8 => 'Round 8', 9 => 'Round 9');
        }
        if ($value == 1024) {
            $roudList = array(1 => 'Round 1', 2 => 'Round 2', 3 => 'Round 3', 4 => 'Round 4', 5 => 'Round 5', 6 => 'Round 6', 7 => 'Round 7', 8 => 'Round 8', 9 => 'Round 9', 9 => 'Round 10');
        }
        return $roudList;
    }
    function saveBracketBattle(Request $request)
    {
        $bracketDetail = Bracket::where('club_id', $this->userDetail->id)->first();
        $obj =  new Bracket;
        if (!empty($bracketDetail)) {
            $obj =  Bracket::find($bracketDetail->id);
        }

        $obj->structure = $request->structure;
        $obj->about = $request->about;
        $obj->bracket_name = $request->bracket_name;
        $obj->club_id = $this->userDetail->id;
        $obj->save();

        $getBraketRound = BracketRound::where('club_id', $this->userDetail->id)->count();
        if ($getBraketRound > 0) {
            BracketRound::where('club_id', $this->userDetail->id)->update(['structure' => $request->structure]);
        }

        $data['success'] = true;
        $data['status'] = 200;
        $data['message'] = "Bracket Setting has been updated successfully.";
        return response()->json($data);
    }
    function battleListing(Request $request)
    {

        $roundId = $request->id;
        $newRoundId = $request->id;
        $userList = UserTeams::leftJoin('users', 'users.id', '=', 'user_teams.user_id')
            ->where('user_teams.club_id', $this->userDetail->id)
            ->where('user_teams.is_active', 1)
            ->where('users.id', '<>', '')
            ->orWhereIn('users.id', [3039, 6139])
            ->select('user_teams.user_id', 'users.full_name as username')->get();
        $getBracketStructure = BracketRound::where('id', $roundId)->first();
        /*Get last round id for that winner users start here*/
        $getLastRound = 0;
        $winnerUserList = [];
        $checkLastRoundCompleted = 0;
        if ($getBracketStructure->round > 1) {
            $getLastRound = $getBracketStructure->round - 1;
            $getBracketRound = BracketRound::where('round', $getLastRound)->where('club_id', $this->userDetail->id)->value('id');
            $checkLastRoundCompleted = BracketMatch::where('round_id', $getBracketRound)->where('winner', '<>', '')->count();
            if ($checkLastRoundCompleted == 0) {
                $data['success'] = false;
                $data['status'] = 500;
                $data['message'] = "Last battle is in process.Pleas mark as complete last battle..";
                return response()->json($data);
            }
            $winnerUserList = BracketMatch::leftJoin('users', 'users.id', '=', 'bracket_matches.winner')->where('bracket_matches.round_id', $getBracketRound)
                ->where('bracket_matches.winner', '<>', '')->pluck('users.full_name', 'bracket_matches.winner')->all();
            $roundId = $getBracketRound;
        } else {
            $roundId = $roundId;
            $checkLastRoundCompleted = BracketMatch::where('round_id', $roundId)->count();
        }

        if (!empty($winnerUserList)) {
            $userList = $winnerUserList;
        }
        /*Get last round id for that winner users start here*/
        $battles = $getBracketStructure->structure / 2;
        $getBattles = [];

        /*Match Combinations start here*/
        if ($getBracketStructure->round > 1) {
            $getPlayingMatchCount = BracketMatch::where('round_id', $getBracketRound)->count();
            $battles = $getPlayingMatchCount / 2;
        }

        /*Match Combinations finish here*/

        $getBattles = BracketMatch::where(['round_id' => $roundId, 'club_id' => $this->userDetail->id])->get();
        if ($getBattles->isEmpty()) {
            $getBattles = [];
            for ($i = 0; $i <= $battles - 1; $i++) {
                $getBattles[$i]['battle'] = $i + 1;
                $getBattles[$i]['first_opponent'] = 0;
                $getBattles[$i]['first_opponent_points'] = 0;
                $getBattles[$i]['second_opponent'] = 0;
                $getBattles[$i]['second_opponent_points'] = 0;
            }
        }
        // $collection = collect($getBattles);
        // /prd($getBattles);
        $combinedUserList = $userList;

        $winnerUserKeys = [];
        foreach ($combinedUserList as $key => $value) {
            $winnerUserKeys[] = $key;
        }
        $getBracketMacthes = BracketMatch::where('round_id', $newRoundId)->exists();
        if ($getBracketMacthes) {
            $getBracketMacthes = BracketMatch::where('round_id', $newRoundId)->get();
        } else {
            $getBracketMacthes = BracketMatch::where('round_id', 9999999)->get();
        }

        $data['success'] = true;
        $data['status'] = 200;
        $data['data'] = $getBracketMacthes;
        $data['getBattles'] = $getBattles;
        $data['user_list'] = $combinedUserList;
        return response()->json($data);
    }

    public function updateRoundBattle(Request $request)
    {

        $bracketMatchData = BracketMatch::where('round_id', $request->roundId)->where('club_id', $this->userDetail->id)->first();
        foreach ($request->data as $key => $value) {
            $obj = new BracketMatch();
            if (!empty($bracketMatchData)) {
                $obj =  BracketMatch::find($value['id']);
            }
            $obj->battle = $value['battle'];
            $obj->club_id =  $this->userDetail->id;
            $obj->first_opponent = !empty($value['first_opponent']) ? $value['first_opponent'] : '';
            $obj->second_opponent = !empty($value['second_opponent']) ? $value['second_opponent'] : '';;
            $obj->round_id = $request->roundId;
            $obj->save();
        }

        $data['success'] = true;
        $data['status'] = 200;
        $data['message'] = "Round battle updated successfully.";
        return response()->json($data);
    }
    public function matchCompletion(Request $request)
    {
        $roundId = $request->roundId;
        $byePlayerId = User::where('user_role_id', 10)->value('id');
        $getRoundGW = BracketRound::where('id', $roundId)->value('gameweeks');
        $cur_club = User::where('id', $this->userDetail->id)->first();
        $userList = UserTeams::where('club_id', $this->userDetail->id)->get();
        if (!empty($userList)) {
            BracketRoundPoint::where('round_id', $roundId)->delete();
            foreach ($userList as $key => $userValues) {
                $getGameWeeksNUmbmer = explode(',', $getRoundGW);
                $totalGWPoints = 0;
                foreach ($getGameWeeksNUmbmer as $value) {
                    $cur_gameweek_date = Carbon::parse($cur_club->lockout_start_date)->startOfWeek()->addWeeks($value)->subDay()->startOfWeek();

                    // For extra bonus points start
                    $totwPlayerList = UserTeamsGWExtraPTTrack::where('gw_end_date', '<=', Carbon::now()->subWeek()->endOfWeek())->where('team_id', $userValues->id)->get();

                    $totwArr = [];
                    foreach ($totwPlayerList as $key => $value) {
                        $totwArr = !empty($value->gw_player_points) ? array_keys(@unserialize($value->gw_player_points)) : array();
                    }
                    $totwPlayers = array_unique($totwArr);

                    $totwPlayersBonus = TeamOfTheWeek::whereBetween('gw_end_date', [$cur_gameweek_date->copy()
                        ->startOfWeek()
                        ->endOfDay(), $cur_gameweek_date->copy()
                        ->endOfWeek()
                        ->endOfDay()])
                        ->whereIn('player_id', $totwPlayers)
                        ->where('club_id', $this->userDetail->id)
                        ->sum('bonus');

                    $geTeamPowerBonus = TeamPower::leftJoin('team_power_players', 'team_power_players.team_power_id', '=', 'team_powers.id')
                        ->whereBetween('gw_end_date', [$cur_gameweek_date->copy()
                            ->startOfWeek()
                            ->endOfDay(), $cur_gameweek_date->copy()
                            ->endOfWeek()
                            ->endOfDay()])
                        ->whereIn('team_power_players.player_id', $totwPlayers)
                        ->sum('team_power_players.bonus');

                    // For extra bonus points end


                    $gameweek_team_value = UserTeamsGWExtraPTTrack::where('team_id', $userValues->id)
                        ->whereBetween('gw_end_date', [$cur_gameweek_date->copy()
                            ->startOfWeek()
                            ->endOfDay()->addDays(1), $cur_gameweek_date->copy()
                            ->endOfWeek()
                            ->endOfDay()->addDays(1)])
                        ->first();

                    $overallPoints =  $totwPlayersBonus + $geTeamPowerBonus;
                    $totalGWPoints += !empty($gameweek_team_value->gw_pt) ? $gameweek_team_value->gw_pt + $overallPoints : 0 + $overallPoints;
                }
                $obj = new BracketRoundPoint();
                $obj->round_id = $roundId;
                $obj->user_id = $userValues->user_id;
                $obj->points = $totalGWPoints;
                $obj->save();
            }
        }
        /*Add winner user and add point his point*/
        $getBattlePlayers = BracketMatch::where('round_id', $roundId)->get();
        $roundPoints = BracketRoundPoint::where('round_id', $roundId)->get();
        if (!$roundPoints->isEmpty() && !$getBattlePlayers->isEmpty()) {
            foreach ($roundPoints as $key => $rp) {
                foreach ($getBattlePlayers as $key => $value) {
                    if ($rp->user_id == $value->first_opponent) {
                        $obj = BracketMatch::find($value->id);
                        $obj->first_opponent_points = $rp->points;
                        $obj->save();
                    }
                    if ($rp->user_id == $value->second_opponent) {
                        $obj = BracketMatch::find($value->id);
                        $obj->second_opponent_points = $rp->points;
                        $obj->save();
                    }
                }
            }
        }
        $getBattlePlayers = BracketMatch::where('round_id', $roundId)->get();
        if (!$getBattlePlayers->isEmpty()) {

            foreach ($getBattlePlayers as $key => $value) {

                if ($value->first_opponent_points > $value->second_opponent_points) {
                    $obj = BracketMatch::find($value->id);
                    $obj->winner =  ($byePlayerId == $value->first_opponent) ? '' : $value->first_opponent;
                    $obj->save();
                }
                if ($value->second_opponent_points > $value->first_opponent_points) {
                    $obj = BracketMatch::find($value->id);
                    $obj->winner = ($byePlayerId == $value->second_opponent) ? '' : $value->second_opponent;
                    $obj->save();
                }
                if ($value->second_opponent_points == $value->first_opponent_points) {
                    if ($byePlayerId == $value->first_opponent) {
                        $obj = BracketMatch::find($value->id);
                        $obj->winner = $value->second_opponent;
                        $obj->save();
                    }
                    if ($byePlayerId == $value->second_opponent) {
                        $obj = BracketMatch::find($value->id);
                        $obj->winner = $value->first_opponent;
                        $obj->save();
                    } else {
                        $obj = BracketMatch::find($value->id);
                        $obj->winner = 'tie';
                        $obj->save();
                    }
                }
            }
            if (!empty($obj->id)) {
                BracketRound::where('id', $roundId)->where('club_id', $this->userDetail->id)->update(['status' => 2]);
                $data['success'] = true;
                $data['status'] = 200;
                $data['message'] = "Status has been changed successfully.";
                return response()->json($data);
            } else {
                $data['success'] = false;
                $data['status'] = 500;
                $data['message'] = "Something went wrong.";
                return response()->json($data);
            }
        } else {
            $data['success'] = true;
            $data['status'] = 200;
            $data['message'] = "You have not save battle.Please save battle first.";
            return response()->json($data);
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Model\GameweekRound;
use App\Model\Player;
use App\Model\BonusCard;
use App\Model\TeamPower;
use App\Model\TeamPowerPlayer;
use App\Model\GamePower;
use App\Model\GameUserControl;
use App\Model\Team;
use App\Model\Branding;
use App\Model\GameExtraInfo;
use App\Model\TeamOfTheWeek;
use App\Model\TeamOfTheWeekPlayer;
use App\Model\User;
use App\Model\VerifyUser;
use App\Model\UserTeams;
use App\Model\MultiPlayer;
use App\Model\MultiPlayerSalary;
use App\Model\Fixture;
use App\Model\TeamPlayer;
use App\Model\UserTeamPlayers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;


class CommonController extends Controller
{
    private $userDetail = null;
    public function __construct()
    {
        $this->userDetail = auth('api')->user();
    }
    public function bonusCard(Request $request)
    {
        $pageLimit = 10;
        if (!empty($request->limit)) {
            $pageLimit = $request->limit;
        }
        $sortBy = "gameweek_rounds.created_at";
        $order = 'ASC';
        if (!empty($request->sort)) {
            $explodeOrderBy = explode("%", $request->sort);
            $sortBy = current($explodeOrderBy);
            $order = end($explodeOrderBy);
        }
        $result  = GameweekRound::where('gameweek_rounds.club_id', $this->userDetail->id)
            ->leftJoin('team_powers', function ($join) {
                $join->on('gameweek_rounds.round', '=', 'team_powers.gw_number')
                    ->on('gameweek_rounds.club_id', '=', 'team_powers.club_id');
            })
            ->select('gameweek_rounds.*', 'team_powers.name as bonus_card_name', 'team_powers.description as decription', 'team_powers.id as team_power_id')
            ->orderBy($sortBy, $order)
            ->get();
        $data['success'] = true;
        $data['status'] = 200;
        $data['data'] = $result;
        $data['message'] = "Data fetched sucessfully.";
        return response()->json($data);
    }

    public function clubPlayer(Request $request)
    {
        $query   = Player::where('club', $this->userDetail->id)->select('full_name', 'id')->orderBy('full_name', 'ASC')->get();

        $data = [];
        $map = $query->map(function ($item) {

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


    public function bonusCardSelectedPlayer(Request $request)
    {
        $query   = Player::where('club', $this->userDetail->id)->select('full_name', 'id')->orderBy('full_name', 'ASC')->get();
        $teamPoweId = TeamPower::where('club_id', $this->userDetail->id)->where('gw_number', $request->round)->value('id');
        $teamPlayers = TeamPowerPlayer::where('team_power_id', $teamPoweId)->pluck('player_id', 'player_id')->all();
        $data = [];
        $selected = false;
        $map = $query->map(function ($item) use ($teamPlayers, $selected) {
            if (in_array($item->id, $teamPlayers)) {
                $selected = true;
            }
            $data['value'] = $item->id;
            $data['text'] = $item->full_name;
            $data['selected'] = $selected;
            return $data;
        });


        $data['success'] = true;
        $data['status'] = 200;
        $data['data'] = $map;
        $data['message'] = "Data fetched sucessfully.";
        return response()->json($data);
    }
    function getBonusCardDetail(Request $request)
    {
        $details = TeamPower::where('club_id', $this->userDetail->id)->where('gw_number', $request->round)->with(['getTeamPlayer'])->first();
        $playerList = TeamPowerPlayer::where('team_power_id', $details->id)->pluck('player_id', 'id')->all();
        $data['success'] = true;
        $data['status'] = 200;
        $data['data'] = $details;
        $data['selected_player'] = $playerList;
        return response()->json($data);
    }

    public function saveBonusCard(Request $request)
    {

        $getGWnumber = TeamPower::where('club_id', $this->userDetail->id)->where('gw_number', $request->round)->first();
        if (!empty($getGWnumber)) {
            $obj = TeamPower::find($getGWnumber->id);
        } else {
            $obj = new TeamPower();
        }
        $obj->name    =  $request->title;
        $obj->description    =  $request->description;
        $obj->club_id    =  $this->userDetail->id;
        $obj->gw_number    =  $request->round;
        $saved = $obj->save();

        if ($saved) {
            if (!empty($request->player)) {
                TeamPowerPlayer::where('team_power_id', $obj->id)->delete();
                foreach ($request->player as $value) {
                    $playerObj = new TeamPowerPlayer;
                    $playerObj->team_power_id = $obj->id;
                    $playerObj->player_id = $value;
                    $playerObj->save();
                }
            }
        }
        $data['success'] = true;
        $data['status'] = 200;
        $data['message'] = "Bonus Card has been added successfully.";
        return response()->json($data);
    }

    public function bonusCardPlayer(Request $request)
    {
        $teamPowerPlayer = TeamPowerPlayer::leftJoin('players', 'players.id', '=', 'team_power_players.player_id')
            ->where('team_power_id', $request->team_power_id)
            ->select('team_power_players.*', 'players.full_name as player_name')->get();

        $data['success'] = true;
        $data['status'] = 200;
        $data['data'] = $teamPowerPlayer;
        return response()->json($data);
    }
    public function saveBonusCardPlayerPoint(Request $request)
    {

        if (!empty($request->data)) {
            foreach ($request->data as $value) {
                $playerObj = TeamPowerPlayer::find($value['id']);
                $playerObj->bonus = $value['bonus'];
                $playerObj->save();
            }
        }
        $data['success'] = true;
        $data['status'] = 200;
        $data['message'] = "Bonus Card Point has been added successfully.";
        return response()->json($data);
    }
    public function powerControl(Request $request)
    {
        $result = GamePower::where('club', $this->userDetail->id)->first();

        if (empty($result)) {
            $obj = new GamePower();
            $obj->trades = 20;
            $obj->trades_status = 1;
            $obj->save();
        }
        $data['success'] = true;
        $data['status'] = 200;
        $data['data'] = $result;
        return response()->json($data);
    }

    public function editPowerControl(Request $request)
    {
        $type = $request->type;
        $result = GamePower::where('club', $this->userDetail->id)->first();
        $obj = new GamePower();
        if (!empty($result)) {
            $obj = GamePower::find($result->id);
        }
        if ($type == "captain_cards") {
            $obj->captain_cards = $request->captain_cards;
        }
        if ($type == "twelfth_men_cards") {
            $obj->twelfth_men_cards = $request->twelfth_men_cards;
        }
        if ($type == "dealer_cards") {
            $obj->dealer_cards = $request->dealer_cards;
        }
        if ($type == "flipper_cards") {
            $obj->flipper_cards = $request->flipper_cards;
        }
        if ($type == "trades_status") {
            $obj->trades = $request->trades;
        }
        $obj->save();
        $result = GamePower::find($obj->id);
        $data['success'] = true;
        $data['status'] = 200;
        $data['data'] = $result;
        $data['message'] = "Power Control updated sucessfully";
        return response()->json($data);
    }
    function updateGamePrivacy(Request $request)
    {
        $privacyObj = GameExtraInfo::firstOrNew(['club_id' =>  $this->userDetail->id]);
        $privacyObj->game_visibility = $request->privacy;
        $privacyObj->game_accept_code = Str::random(8);;
        $privacyObj->save();
        $data['success'] = true;
        $data['status'] = 200;
        $data['data'] = $privacyObj;
        $data['message'] = "Game Privacy updated sucessfully";
        return response()->json($data);
    }

    public function changePowerControlStatus(Request $request)
    {
        $type = $request->type;
        $status = ($request->status) ? 0 : 1;
        // if ($type == "captain_cards_status") {
        //     $checkIsPaid = GamePower::where('club', $this->userDetail->id)->value('tripal_cap_paid');
        // }
        // if ($type == "twelfth_men_cards_status") {
        //     $checkIsPaid = GamePower::where('club', $this->userDetail->id)->value('twelfthman_paid');
        // }
        // if ($type == "flipper_cards_status") {
        //     $checkIsPaid = GamePower::where('club', $this->userDetail->id)->value('fliper_paid');
        // }
        // if ($type == "dealer_cards_status") {
        //     $checkIsPaid = GamePower::where('club', $this->userDetail->id)->value('dealer_paid');
        // }
        // if ($type == "free_trades_paid") {
        //     $checkIsPaid = GamePower::where('club', $this->userDetail->id)->value('free_trades_paid');
        // }


        GamePower::where('club', $this->userDetail->id)->update([$type => $status]);
        $checkedData = GamePower::where('club', $this->userDetail->id)->value($type);
        $data['success'] = true;
        $data['status'] = 200;
        $data['checkedData'] = $checkedData;
        $data['message'] = "Status updated sucessfully";
        return response()->json($data);
    }

    public function updateGameSpot(Request $request)
    {
        $result = GameUserControl::where('club', $this->userDetail->id)->first();

        $obj1 = new GameUserControl;
        if (!empty($result)) {
            $obj1 = GameUserControl::find($result->id);
        }
        $obj1->user_number = $request->user_number;
        $obj1->club = $this->userDetail->id;
        $obj1->save();

        $data['success'] = true;
        $data['status'] = 200;
        $data['data'] = $result;
        $data['message'] = "Game spot updated successfully.";

        return response()->json($data);
    }
    public function branding(Request $request)
    {
        $result  =     $userDetails = Branding::where(['club_id' => $this->userDetail->id])->first();
        $data['success'] = true;
        $data['status'] = 200;
        $data['data'] = $result;
        return response()->json($data);
    }

    public function editBranding(Request $request)
    {
        $result = Branding::where('club_id', $this->userDetail->id)->first();
        $obj = new Branding;
        if (!empty($result)) {
            $obj = Branding::find($result->id);
        }
        $obj->name = $request->name;
        $obj->url = $request->url;
        $obj->payment_description = "Direct Purchased";
        $obj->is_paid = 1;
        $obj->club_id = $this->userDetail->id;

        if (!empty($request->image) && $request->image != "undefined" && $request->image != "null") {
            $extension             =    $request->image->getClientOriginalExtension();
            $newFolder             =     strtoupper(date('M') . date('Y')) . '/';
            @unlink(base_path() . "/public/uploads/branding/" . $obj->logo);
            $folderPath            =     base_path() . "/public/uploads/branding/" . $newFolder;
            if (!File::exists($folderPath)) {
                File::makeDirectory($folderPath, $mode = 0777, true);
            }
            $userImageName = time() . '-branding-main.' . $extension;
            $image = $newFolder . $userImageName;
            if ($request->image->move($folderPath, $userImageName)) {
                $obj->logo        =    $image;
            }
        }
        $obj->save();
        $data['success'] = true;
        $data['status'] = 200;
        $data['message'] = "Branding updated successfully.";
        return response()->json($data);
    }
    public function getBonusPoint(Request $request)
    {
        $result = Team::where('club', $this->userDetail->id)->where('is_active', 1)->orderBy('name', 'ASC')->get();
        $data = [];
        $map = $result->map(function ($item) {

            $data['id'] = $item->id;
            $data['potm_bonus'] = $item->potm_bonus;
            $data['name'] = $item->name;
            $data['potm_bonus_status'] = $item->potm_bonus_status;
            $data['match_bonus'] = $item->match_bonus;
            return $data;
        });
        $data['success'] = true;
        $data['status'] = 200;
        $data['data'] = $map;
        return response()->json($data);
    }
    public function updateBonusPoint(Request $request)
    {
        if ($request->type === "potm") {

            foreach ($request->data as  $value) {
                $obj = Team::find($value['id']);
                $obj->potm_bonus = !empty($value['potm_bonus']) ? $value['potm_bonus'] : $obj->potm_bonus;
                $obj->potm_bonus_status = 1;
                $obj->save();
            }
        }
        if ($request->type === "result_bonus") {
            foreach ($request->data as $value) {
                $obj = Team::find($value['id']);
                $obj->match_bonus = !empty($value['match_bonus']) ? $value['match_bonus'] : $obj->match_bonus;
                $obj->save();
            }
        }
        $data['success'] = true;
        $data['status'] = 200;
        $data['message'] = "Bonus Point has been updated successfully.";
        return response()->json($data);
    }
    public function verifyUsers(Request $request)
    {

        $pageLimit = 10;
        if (!empty($request->limit)) {
            $pageLimit = $request->limit;
        }
        $playerIds = VerifyUser::where('club_id', $this->userDetail->id)->select('player_id', 'player_id')->get();
        $result = VerifyUser::where('club_id', $this->userDetail->id)
            ->where('verify_users.is_approved', 1)
            ->leftJoin('users', 'users.id', '=', 'verify_users.user_id')
            ->leftJoin('players', 'players.id', '=', 'verify_users.player_id')
            ->select('verify_users.*', 'users.full_name as username', 'players.full_name as player_name')
            ->orderBy('created_at', 'desc')
            ->paginate($pageLimit);

        $player = new Player();
        $playerlist = $player->get_players_by_club($this->userDetail->id);
        /*$user = new User();
         $userList = $user->get_user_list();*/
        $userList = UserTeams::leftJoin('users', 'users.id', '=', 'user_teams.user_id')->where('club_id', $this->userDetail->id)
            ->where('user_teams.is_active', 1)
            ->orderBy('full_name', 'ASC')
            ->select('users.full_name', 'user_teams.user_id')
            ->get();


        $data['success'] = true;
        $data['status'] = 200;
        $data['player_ids'] = $playerIds;
        $data['data'] = $result;
        $data['user_list'] = $userList;
        $data['player_list'] = $playerlist;
        return response()->json($data);
    }
    public function saveVerifyUser(Request $request)
    {
        $checkPlayerUserExists = VerifyUser::where(['user_id' => $request->user_id, 'player_id' => $request->player_id])->exists();
        if ($checkPlayerUserExists) {
            $data['status'] = 501;
            $data['message'] = "User already verified.";
            return response()->json($data);
        }
        $obj = new VerifyUser();
        $obj->club_id = $this->userDetail->id;
        $obj->user_id = $request->user_id;
        $obj->player_id = $request->player_id;
        $obj->is_approved = 1;
        $obj->save();

        $result = VerifyUser::where('club_id', $this->userDetail->id)
            ->where('verify_users.is_approved', 1)
            ->leftJoin('users', 'users.id', '=', 'verify_users.user_id')
            ->leftJoin('players', 'players.id', '=', 'verify_users.player_id')
            ->select('verify_users.*', 'users.full_name as username', 'players.full_name as player_name')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $data['success'] = true;
        $data['status'] = 200;
        $data['data'] = $result;
        $data['message'] = "Member verified successfully.";
        return response()->json($data);
    }
    function totwListing(Request $request)
    {
        $result  = GameweekRound::where('gameweek_rounds.club_id', $this->userDetail->id)->get();
        $data['success'] = true;
        $data['status'] = 200;
        $data['data'] = $result;
        return response()->json($data);
    }
    function saveTotw(Request $request)
    {
        $getGWnumber = TeamOfTheWeek::where('club_id', $this->userDetail->id)->where('gw_number', $request->round)->first();
        if (!empty($TeamOfTheWeek)) {
            $obj = TeamOfTheWeek::find($getGWnumber->id);
        } else {
            $obj = new TeamOfTheWeek();
        }
        $obj->club_id    =  $this->userDetail->id;
        $obj->gw_number    =  $request->round;
        $saved = $obj->save();
        if ($saved) {
            if (!empty($request->player)) {
                TeamOfTheWeekPlayer::where('team_of_the_week_id', $obj->id)->delete();
                foreach ($request->player as $value) {
                    $playerObj = new TeamOfTheWeekPlayer;
                    $playerObj->team_of_the_week_id = $obj->id;
                    $playerObj->player_id = $value;
                    $playerObj->bonus = 0;
                    $playerObj->save();
                }
            }
        }
        $data['success'] = true;
        $data['status'] = 200;
        $data['message'] = "Bonus Card has been added successfully.";
        return response()->json($data);
    }
    public function totwSelectedPlayer(Request $request)
    {
        $query   = Player::where('club', $this->userDetail->id)->select('full_name', 'id')->orderBy('full_name', 'ASC')->get();
        $totwId = TeamOfTheWeek::where('club_id', $this->userDetail->id)->where('gw_number', $request->round)->value('id');
        $totwPlayers = TeamOfTheWeekPlayer::where('team_of_the_week_id', $totwId)->pluck('player_id', 'player_id')->all();
        $data = [];
        $selected = false;
        $map = $query->map(function ($item) use ($totwPlayers, $selected) {
            if (in_array($item->id, $totwPlayers)) {
                $selected = true;
            }
            $data['value'] = $item->id;
            $data['text'] = $item->full_name;
            $data['selected'] = $selected;
            return $data;
        });


        $data['success'] = true;
        $data['status'] = 200;
        $data['data'] = $map;
        return response()->json($data);
    }
    function totwPlayerList(Request $request)
    {

        $details = TeamOfTheWeek::where('club_id', $this->userDetail->id)->where('gw_number', $request->round)->first();
        $totwPlayerList = [];
        if (!empty($details)) {
            $totwPlayerList = TeamOfTheWeekPlayer::leftJoin('players', 'players.id', '=', 'team_of_the_week_players.player_id')
                ->where('team_of_the_week_id', $details->id)
                ->select('team_of_the_week_players.*', 'players.full_name as player_name')->get();
        }


        $data['success'] = true;
        $data['status'] = 200;
        $data['data'] = $totwPlayerList;
        return response()->json($data);
    }
    function updateTotwPlayerPoint(Request $request)
    {
        if (!empty($request->data)) {
            foreach ($request->data as $value) {
                $playerObj = TeamOfTheWeekPlayer::find($value['id']);
                $playerObj->bonus = $value['bonus'];
                $playerObj->save();
            }
        }
        $data['success'] = true;
        $data['status'] = 200;
        $data['message'] = "Team of the week player points has been updated successfully.";
        return response()->json($data);
    }

    function editGameStructure(Request $request)
    {
        $msg = "";
        if ($request->type == 1) {
            $savedData = User::where('id', $this->userDetail->id)->update(['multi_players_id' => $request->structure]);

            //MultiPlayer::where('club_id', $this->userDetail->id)->update(['parent_id' => $request->structure]);
            $data = MultiPlayer::where('id', $request->structure)->first();


            // prd($selectedStructureData->max_bats);
            $objM = MultiPlayer::where('club_id', $this->userDetail->id)->first();
            $objM->min_bats = $data->min_bats;
            $objM->max_bats = $data->max_bats;
            $objM->min_bowls = $data->min_bowls;
            $objM->max_bowls = $data->max_bowls;
            $objM->min_ar = $data->min_ar;
            $objM->max_ar = $data->max_ar;
            $objM->min_wks = $data->min_wks;
            $objM->max_wks = $data->max_wks;
            $objM->save();
            // $newUser = MultiPlayerSalary::updateOrCreate([
            //     'club_id'   => $this->userDetail->id,
            // ], [
            //     'multi_player_id' => $request->structure,
            // ]);
            $msg = "Game Structure has been updated successfully.";
        }
        if ($request->type == 2) {
            $multiPlayerId = User::where('id', $this->userDetail->id)->value('multi_players_id');

            MultiPlayer::where('club_id', $this->userDetail->id)->update(['salary' => $request->salary]);

            $id = MultiPlayerSalary::where(['club_id' => $this->userDetail->id])->value('id');
            $obj  = new MultiPlayerSalary;
            if (!empty($id)) {
                $obj  = MultiPlayerSalary::find($id);
            }
            $obj->club_id = $this->userDetail->id;
            $obj->multi_player_id = $multiPlayerId;
            $obj->salary = ($request->salary_type == 1) ? 99 : $request->salary;
            $obj->is_default_salary = $request->salary_type;
            $obj->save();

            $msg = "Salary Cap has been updated successfully.";
        }
        $multiPlayerData = MultiPlayer::where('club_id', $this->userDetail->id)->first();
        $data['success'] = true;
        $data['status'] = 200;
        $data['multiplayer_data'] = $multiPlayerData;
        $data['message'] = $msg;
        return response()->json($data);
    }
    function getGameStrucureInfo(Request $request)
    {
        $getStucture = User::where('id', $this->userDetail->id)->value('multi_players_id');
        $gameSpot = GameUserControl::where('club', $this->userDetail->id)->first();
        $multiPlayerData = MultiPlayer::where('club_id', $this->userDetail->id)->first();
        $gamePrivacy = GameExtraInfo::where(['club_id' =>  $this->userDetail->id])->first();
        if (empty($gamePrivacy)) {
            $gamePrivacy = new GameExtraInfo;
            $gamePrivacy->game_visibility = 1;
            $gamePrivacy->club_id = $this->userDetail->id;
            $gamePrivacy->save();
        }


        // Set Default $100 salary and 11 player structure here
        $checkexists = MultiPlayerSalary::where('club_id', $this->userDetail->id)->exists();
        if (!$checkexists) {
            $multplayerSalObj = new MultiPlayerSalary;
            $multplayerSalObj->multi_player_id = 1002;
            $multplayerSalObj->salary = 100;
            $multplayerSalObj->club_id = $this->userDetail->id;
            $multplayerSalObj->save();
        }
        $data['success'] = true;
        $data['status'] = 200;
        $data['game_spot'] = $gameSpot;
        $data['game_structure'] = $getStucture;
        $data['multiplayer_data'] = $multiPlayerData;
        $data['game_privacy'] = $gamePrivacy;
        return response()->json($data);
    }
    public function deleteVerifyUser(Request $request)
    {
        VerifyUser::where('id', $request->id)->delete();
        $data['success'] = true;
        $data['status'] = 200;
        $data['message'] = "Verified Member deleted.";
        return response()->json($data);
    }
    function getGameActivateInfo()
    {
        $game_info = User::where('id', $this->userDetail->id)->first();  //Comes under game_name, club_name, timezone, country, state
        $round_info = GameweekRound::where('club_id', $this->userDetail->id)->first();
        $data['success'] = true;
        $data['status'] = 200;
        $data['game_info'] = $game_info;
        $data['round_info'] = $round_info;
        return response()->json($data);
    }
    function activateGame(Request $request)
    {
        $obj = User::find($this->userDetail->id);
        $obj->is_completed = $request->gameStatus;
        $obj->save();
        $data['success'] = true;
        $data['status'] = 200;
        $data['message'] = "Game Status has been changed!";
        return response()->json($data);
    }

    public function gameAccount(Request $request)
    {

        $type = "";
        if ($request->running_game == "true") {
            $type = 1;
        }
        if ($request->complete_game == "true") {
            $type = 2;
        }
        if ($request->limit != "undefined") {
            $DB  =     User::query();
            if (!empty($request->full_name)) {
                $DB->where("users.full_name", 'like', '%' . $request->full_name . '%');
            }

            if ($request->is_active == 0 || $request->is_active == 1) {
                $DB->where("users.is_active", 'like', '%' . $request->is_active . '%');
            }
            $sortBy = "users.created_at";
            $order = 'DESC';
            if (!empty($request->sort)) {
                $explodeOrderBy = explode("%", $request->sort);
                $sortBy = current($explodeOrderBy);
                $order = end($explodeOrderBy);
            }
            $pageLimit = 10;
            if (!empty($request->limit)) {
                $pageLimit = $request->limit;
            }
            if (!empty($type)) {
                $result = $DB
                    ->where('users.email', $this->userDetail->email)
                    ->where('users.is_completed', $type)
                    ->orderBy($sortBy, $order)
                    ->paginate($pageLimit);
            } else {
                $result = $DB
                    ->where('users.email', $this->userDetail->email)
                    ->orderBy($sortBy, $order)
                    ->paginate($pageLimit);
            }
        }

        $allGames = User::where(['email' => $this->userDetail->email])->count();
        $completeGames = User::where(['email' => $this->userDetail->email, 'is_completed' => 2])->count();
        $getUserIds = User::where(['email' => $this->userDetail->email])->pluck('id', 'id')->all();
        $allPlayers = Player::whereIn('club', $getUserIds)->count();
        $allMembers = User::where(['senior_club_name' => $getUserIds])->count();

        $newData = [];
        $newData['all_games'] = $allGames;
        $newData['complete_games'] = $completeGames;
        $newData['all_players'] = $allPlayers;
        $newData['all_members'] = $allMembers;

        $data['success'] = true;
        $data['status'] = 200;
        $data['data'] = $result;
        $data['new_data'] = $newData;
        return response()->json($data);
    }

    function dashboardData(Request $request)
    {
        $userList = UserTeams::where('club_id', $this->userDetail->id)->where('is_active', 1)->pluck('user_id')->all();
        $totalUsers = User::where('is_deleted', 0)->whereIn('id', $userList)->count();

        // Male female here
        $maleUsers = User::where('gender', 'male')->where('is_deleted', 0)->whereIn('id', $userList)->count();
        $femaleUsers = User::where('gender', 'female')->where('is_deleted', 0)->whereIn('id', $userList)->count();
        $doNotWishToSpecifyUsers = User::where('gender', 'Do not wish to specify')->where('is_deleted', 0)->whereIn('id', $userList)->count();
        $mPercentageUsers = $dnwtsPercentageUsers = $fPercentageUsers  = 0;
        if (!empty($maleUsers) && !empty($totalUsers)) {
            $mPercentageUsers = ($maleUsers / $totalUsers) * 100;
        }
        if (!empty($femaleUsers) && !empty($totalUsers)) {
            $fPercentageUsers = ($femaleUsers / $totalUsers) * 100;
        }
        if (!empty($doNotWishToSpecifyUsers) && !empty($totalUsers)) {
            $dnwtsPercentageUsers = ($doNotWishToSpecifyUsers / $totalUsers) * 100;
        }

        // Male female here
        $googlePercentage = $fbPercentage = $twitterPercentage = $instPercentage = $womPercentage = $adPercentage = $refClubPercentage = $reFriendPercentage = 0;
        $googDetails = User::where('about_us', 33)->whereIn('id', $userList)->count('id');
        if (!empty($googDetails)) {
            $googlePercentage = ($googDetails / $totalUsers) * 100;
        }
        $fbDetails = User::where('about_us', 34)->whereIn('id', $userList)->count('id');
        if (!empty($fbDetails)) {
            $fbPercentage = ($fbDetails / $totalUsers) * 100;
        }
        $twitDetails = User::where('about_us', 32)->whereIn('id', $userList)->count('id');
        if (!empty($twitDetails)) {
            $twitterPercentage = ($twitDetails / $totalUsers) * 100;
        }
        $instaDetails = User::where('about_us', 47)->whereIn('id', $userList)->count('id');
        if (!empty($instaDetails)) {
            $instPercentage = ($instaDetails / $totalUsers) * 100;
        }
        $womDetails = User::where('about_us', 48)->whereIn('id', $userList)->count('id');
        if (!empty($womDetails)) {
            $womPercentage = ($womDetails / $totalUsers) * 100;
        }
        $adDetails = User::where('about_us', 51)->whereIn('id', $userList)->count('id');
        if (!empty($adDetails)) {
            $adPercentage = ($adDetails / $totalUsers) * 100;
        }
        $refClubDetails = User::where('about_us', 50)->whereIn('id', $userList)->count('id');
        if (!empty($refClubDetails)) {
            $refClubPercentage = ($refClubDetails / $totalUsers) * 100;
        }
        $refFriendDetails = User::where('about_us', 49)->whereIn('id', $userList)->count('id');
        if (!empty($refFriendDetails)) {
            $reFriendPercentage = ($refFriendDetails / $totalUsers) * 100;
        }


        // fixtures data
        $totalFixture = Fixture::where(['club' => $this->userDetail->id])->count('id');
        $notStartedFixture = Fixture::where(['club' => $this->userDetail->id, 'status' => 0])->count('id');
        $inProgressFixture = Fixture::where(['club' => $this->userDetail->id, 'status' => 2])->count('id');
        $completedFixture = Fixture::where(['club' => $this->userDetail->id, 'status' => 3])->count('id');


        $newPercentageData = [];
        $newPercentageData['googlePercentage'] = $googlePercentage;
        $newPercentageData['fbPercentage'] = $fbPercentage;
        $newPercentageData['twitterPercentage'] = $twitterPercentage;
        $newPercentageData['instPercentage'] = $instPercentage;
        $newPercentageData['womPercentage'] = $womPercentage;
        $newPercentageData['adPercentag'] = $adPercentage;
        $newPercentageData['refClubPercentage'] = $refClubPercentage;
        $newPercentageData['reFriendPercentage'] = $reFriendPercentage;

        $newPercentageData['googDetails'] = $googDetails;
        $newPercentageData['fbDetails'] = $fbDetails;
        $newPercentageData['twitDetails'] = $twitDetails;
        $newPercentageData['instaDetails'] = $instaDetails;
        $newPercentageData['womDetails'] = $womDetails;
        $newPercentageData['adDetails'] = $adDetails;
        $newPercentageData['refClubDetails'] = $refClubDetails;
        $newPercentageData['refFriendDetails'] = $refFriendDetails;


        $newPercentageData['mPercentageUsers'] = number_format((float)$mPercentageUsers, 2, '.', '');
        $newPercentageData['fPercentageUsers'] =  number_format((float)$fPercentageUsers, 2, '.', '');
        $newPercentageData['dnwtsPercentageUsers'] =  number_format((float)$dnwtsPercentageUsers, 2, '.', '');

        $newPercentageData['totalFixture'] = $totalFixture;
        $newPercentageData['notStartedFixture'] =  $notStartedFixture;
        $newPercentageData['inProgressFixture'] =  $inProgressFixture;
        $newPercentageData['completedFixture'] =  $completedFixture;


        $data['success'] = true;
        $data['status'] = 200;
        $data['data'] = $newPercentageData;
        return response()->json($data);
    }

    public function dashboardUser(Request $request)
    {

        if ($request->limit != "undefined") {
            $DB  =     User::query();
            if (!empty($request->full_name)) {
                $DB->where("users.full_name", 'like', '%' . $request->full_name . '%');
            }

            if ($request->is_active == 0 || $request->is_active == 1) {
                $DB->where("users.is_active", 'like', '%' . $request->is_active . '%');
            }
            $sortBy = "users.created_at";
            $order = 'DESC';
            if (!empty($request->sort)) {
                $explodeOrderBy = explode("%", $request->sort);
                $sortBy = current($explodeOrderBy);
                $order = end($explodeOrderBy);
            }
            $pageLimit = 10;
            if (!empty($request->limit)) {
                $pageLimit = $request->limit;
            }

            $userList = UserTeams::where('club_id', $this->userDetail->id)
                ->where('user_teams.is_active', 1)
                ->pluck('user_id')
                ->all();

            $result = $DB
                ->with('get_paid_users')
                ->whereIn('users.id', $userList)
                ->orWhere('users.senior_club_name', $this->userDetail->id)
                ->orderBy($sortBy, $order)
                ->paginate($pageLimit);


            $data['success'] = true;
            $data['status'] = 200;
            $data['data'] = $result;
            return response()->json($data);
        }
    }

    function checkItemExists(Request $request)
    {
        $itemType = $request->type;
        $id = $request->id;
        $isExists = 0;
        $messageString = "";
        if ($itemType == 'grade') {
            $status = [2, 3];
            $checkFixtureExists = Fixture::where(['grade' => $id])->whereNotIn('status', $status)->exists();
            $checkTeamExists = Team::where(['grade_name' => $id])->exists();
            if ($checkFixtureExists) {
                $isExists = 1;
                $messageString = "You can not delete this comp. Fixture is in progress/completed.";
            }
            if ($checkTeamExists) {
                $isExists = 1;
                $messageString .= "Team already exists in this comp.";
            }
        }
        if ($itemType == 'team') {
            $status = [2, 3];
            $checkFixtureExists = Fixture::where(['team' => $id])->whereNotIn('status', $status)->exists();
            if ($checkFixtureExists) {
                $isExists = 1;
                $messageString = "You can not delete this grade. Fixture is in progress/completed.";
            }
        }
        if ($itemType == 'round') {
            $status = [2, 3];
            $checkFixtureExists = Fixture::where(['team' => $id])->whereNotIn('status', $status)->exists();
            if ($checkFixtureExists) {
                $isExists = 1;
                $messageString = "You can not delete this grade. Fixture is in progress/completed.";
            }
        }
        if ($itemType == 'fixture') {
            $checkSquad = TeamPlayer::where(['fixture_id' => $id])->exists();
            if ($checkSquad) {
                $isExists = 1;
                $messageString = "You can not delete this fixture. Squad created in this fixture.";
            }
        }
        if ($itemType == 'player') {
            $checkSquadExists = TeamPlayer::where(['player_id' => $id])->exists();
            $checkTeamExists = UserTeamPlayers::where(['player_id' => $id])->exists();
            if ($checkSquadExists) {
                $isExists = 1;
                $messageString = "You can not delete this player.This is exists in fixtures.";
            }
            if ($checkTeamExists) {
                $isExists = 1;
                $messageString .= "This is already picked in team.";
            }
        }
        $data['success'] = true;
        $data['message'] = $messageString;
        $data['status'] = 200;
        $data['data'] = $isExists;
        return response()->json($data);
    }
}

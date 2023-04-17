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
use App\Model\VerifyUser;
use App\Model\UserTeams;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;


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
            TeamPowerPlayer::where('team_power_id', $obj->id)->delete();
            if (!empty($request->player)) {
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
        $tpId = TeamPower::where('club_id', $this->userDetail->id)->where('gw_number', $request->round)->value('id');

        $teamPowerPlayer = TeamPowerPlayer::leftJoin('players', 'players.id', '=', 'team_power_players.player_id')
            ->where('team_power_id', $tpId)
            ->select('players.full_name as player_name', 'team_power_players.player_id as player_id')->get();

        $defaultValueArray = TeamPowerPlayer::leftJoin('players', 'players.id', '=', 'team_power_players.player_id')
            ->where('team_power_id', $tpId)->select('bonus', 'player_id')->get();


        $data['success'] = true;
        $data['status'] = 200;
        $data['data'] = $teamPowerPlayer;
        $data['bonus_card_values'] = $defaultValueArray;
        $data['message'] = "Data fetched sucessfully.";
        return response()->json($data);
    }
    public function saveBonusCardPlayerPoint(Request $request)
    {

        if (!empty($request->player)) {
            foreach ($request->player as $value) {
                $playerObj = TeamPowerPlayer::find($request->gradeId);
                $playerObj->team_power_id = $value->id;
                $playerObj->player_id = $value;
                $playerObj->save();
            }
        }
        $data['success'] = true;
        $data['status'] = 200;
        $data['message'] = "Bonus Card has been added successfully.";
        return response()->json($data);
    }
    public function powerControl(Request $request)
    {
        $result = GamePower::where('club', $this->userDetail->id)->first();

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

    public function changePowerControlStatus(Request $request)
    {
        $type = $request->type;
        $status = ($request->status) ? 0 : 1;
        // if ($type == "captain_cards_status") {
        //     $checkIsPaid = GamePower::where('club', Auth::guard('admin')->user()->id)->value('tripal_cap_paid');
        // }
        // if ($type == "twelfth_men_cards_status") {
        //     $checkIsPaid = GamePower::where('club', Auth::guard('admin')->user()->id)->value('twelfthman_paid');
        // }
        // if ($type == "flipper_cards_status") {
        //     $checkIsPaid = GamePower::where('club', Auth::guard('admin')->user()->id)->value('fliper_paid');
        // }
        // if ($type == "dealer_cards_status") {
        //     $checkIsPaid = GamePower::where('club', Auth::guard('admin')->user()->id)->value('dealer_paid');
        // }
        // if ($type == "free_trades_paid") {
        //     $checkIsPaid = GamePower::where('club', Auth::guard('admin')->user()->id)->value('free_trades_paid');
        // }


        GamePower::where('club', $this->userDetail->id)->update([$type => $status]);
        $checkedData = GamePower::where('club', $this->userDetail->id)->value($type);
        $data['success'] = true;
        $data['status'] = 200;
        $data['checkedData'] = $checkedData;
        $data['message'] = "Status updated sucessfully";
        return response()->json($data);
    }

    public function gameSpot(Request $request)
    {
        $details = GameUserControl::where('club', $this->userDetail->id)->first();
        $data['success'] = true;
        $data['status'] = 200;
        $data['data'] = $details;
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

        if (!empty($request->logo)  && $request->logo != "undefined" && $request->logo != "null") {
            $extension             =    $request->logo->getClientOriginalExtension();
            $newFolder             =     strtoupper(date('M') . date('Y')) . '/';
            $folderPath            =     base_path() . "/public/uploads/branding/" . $newFolder;
            if (!File::exists($folderPath)) {
                File::makeDirectory($folderPath, $mode = 0777, true);
            }
            $userImageName = time() . '-branding-main.' . $extension;
            $image = $newFolder . $userImageName;
            if ($request->logo->move($folderPath, $userImageName)) {
                $obj->image        =    $image;
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
        $data['message'] = "User verified successfully.";
        return response()->json($data);
    }
}

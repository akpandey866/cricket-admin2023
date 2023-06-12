<?php

namespace App\Http\Controllers;

use App\Model\Player;
use App\Model\PlayerPrice;
use App\Model\Team;
use App\Model\DropDown;
use App\Model\User;
use App\Model\MultiPlayer;
use App\Model\VerifyUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Validator;

class PlayerController extends Controller
{
    private $userDetail = null;
    public function __construct()
    {
        $this->userDetail = auth('api')->user();
    }
    public function index(Request $request)
    {

        if ($request->limit != "undefined") {
            $DB                     =     Player::query()->leftJoin('users', 'players.club', '=', 'users.id')
                ->leftJoin('dropdown_managers', 'players.category', '=', 'dropdown_managers.id')
                ->leftJoin('dropdown_managers as dd', 'dd.id', '=', 'players.position')
                ->leftJoin('teams', 'teams.id', '=', 'players.team_id')
                ->select('players.*', 'users.club_name', 'dropdown_managers.name', 'dd.name as position_name', 'teams.name as team_name');
            if (!empty($request->full_name)) {
                $DB->where("players.full_name", 'like', '%' . $request->full_name . '%');
            }
            if (!empty($request->svalue)) {
                $DB->where("players.svalue", 'like', '%' . $request->svalue . '%');
            }
            // if (!empty($request->position_name)) {
            //     $DB->where("players.position_name", 'like', '%' . $request->position_name . '%');
            // }
            if ($request->is_active == 0 || $request->is_active == 1) {
                $DB->where("players.is_active", 'like', '%' . $request->is_active . '%');
            }
            $sortBy = "players.created_at";
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


            $result = $DB
                ->where('players.club', $this->userDetail->id)
                ->orderBy($sortBy, $order)
                ->paginate($pageLimit);
        }

        $data['success'] = true;
        $data['status'] = 200;
        $data['data'] = $result;
        $data['message'] = "Data fetched sucessfully.";
        return response()->json($data);
    }
    public function savePlayer(Request $request)
    {

        $obj =  new Player;
        $fullName = ucwords($request->first_name . " " . $request->last_name);
        $obj->full_name = $fullName;
        $obj->club = $this->userDetail->id;
        $obj->first_name = $request->first_name;
        $obj->last_name = $request->last_name;
        $obj->position = $request->position;
        $obj->svalue = $request->value;
        $obj->initial_value = $request->value;
        $obj->team_id = $request->team;
        $obj->bat_style = !empty($request->bat_style) ? $request->bat_style : 0;
        $obj->bowl_style = !empty($request->bowl_style) ? $request->bowl_style : 0;

        if (!empty($request->image) && $request->image != "undefined" && $request->image != "null") {
            $extension = $request->image->getClientOriginalExtension();
            $newFolder = strtoupper(date('M') . date('Y')) . '/';
            $folderPath  =     base_path() . "/public/uploads/player/" . $newFolder;
            if (!File::exists($folderPath)) {
                File::makeDirectory($folderPath, $mode = 0777, true);
            }
            $userImageName = time() . '-player.' . $extension;
            $image = $newFolder . $userImageName;
            if ($request->image->move($folderPath, $userImageName)) {
                $obj->image        =    $image;
            }
        } else {
            $newFolder = strtoupper(date('M') . date('Y')) . '/';
            $folderPath   =     base_path() . "/public/uploads/player/" . $newFolder;
            $playerDummyImageRootpath = base_path() . "/public/img/Position1/";
            if (!File::exists($folderPath)) {
                File::makeDirectory($folderPath, $mode = 0777, true);
            }
            $image = "Position1a.png";
            if ($request->position == 1) {
                $image = "Position1a.png";
            } elseif ($request->position == 2) {
                $image = "Position2a.png";
            } elseif ($request->position == 3) {
                $image = "Position3a.png";
            } elseif ($request->position == 4) {
                $image = "Position4a.png";
            }
            if (copy($playerDummyImageRootpath . $image, $folderPath . $image)) {
                $obj->image = $newFolder . $image;
            }
        }
        $obj->save();
        $data['success'] = true;
        $data['status'] = 200;
        $data['data'] = $obj;
        $data['message'] = "Player has been added successfully.";
        return response()->json($data);
    }
    public function deletePlayer(Request $request)
    {
        Player::where('id', $request->id)->delete();
        $data['status'] = 200;
        $data['message'] = "Player has been deleted successfully.";
        return response()->json($data);
    }

    public function playerDetail(Request $request)
    {
        $playerDetails =    Player::findOrFail($request->id);
        $drop_down = new DropDown();
        $teamList    =    Team::where('is_active', 1)->where('club', $this->userDetail->id)->select('name', 'id')->get();
        $position    =    DropDown::where('dropdown_type', 'position')->where('is_active', 1)->orderBy('name', 'ASC')->select('name', 'id')->get();
        $batStyle = $drop_down->get_master_list("bat-style");
        $bowlStyle = $drop_down->get_master_list("bowl-style");
        $svalue = PlayerPrice::where('club_id', $this->userDetail->id)->orderby('price', 'desc')->get();

        $data['status'] = 200;
        $data['player_data'] = $playerDetails;
        $data['teamList'] = $teamList;
        $data['position'] = $position;
        $data['batStyle'] = $batStyle;
        $data['bowlStyle'] = $bowlStyle;
        $data['value'] = $svalue;
        return response()->json($data);
    }
    public function editPlayer(Request $request)
    {

        $obj =  Player::find($request->playerId);
        $fullName = ucwords($request->first_name . " " . $request->last_name);
        $obj->full_name = $fullName;
        $obj->first_name = $request->first_name;
        $obj->last_name = $request->last_name;
        $obj->position = $request->position;
        //$obj->svalue = $request->value;
        $obj->team_id = $request->team;
        $obj->bat_style = !empty($request->bat_style) ? $request->bat_style : 0;
        $obj->bowl_style = !empty($request->bowl_style) ? $request->bowl_style : 0;

        $obj->sponsor_link = !empty($request->sponsor_link) ? $request->sponsor_link : 0;
        $obj->external_profile_label = !empty($request->external_profile_label) ? $request->external_profile_label : "";
        $obj->external_profile_link = !empty($request->external_profile_link) ? $request->external_profile_link : "";
        $obj->description = !empty($request->description) ? $request->description : "";

        if (!empty($request->image) && $request->image != "undefined" && $request->image != "null") {

            $extension = $request->image->getClientOriginalExtension();
            $newFolder = strtoupper(date('M') . date('Y')) . '/';
            $folderPath  =     base_path() . "/public/uploads/player/" . $newFolder;
            @unlink(base_path() . "/public/uploads/player/" . $obj->image);
            if (!File::exists($folderPath)) {
                File::makeDirectory($folderPath, $mode = 0777, true);
            }
            $userImageName = time() . '-player.' . $extension;
            $image = $newFolder . $userImageName;
            if ($request->image->move($folderPath, $userImageName)) {
                $obj->image        =    $image;
            }
        } else {
            $newFolder = strtoupper(date('M') . date('Y')) . '/';
            $folderPath   =     base_path() . "/public/uploads/player/" . $newFolder;
            $playerDummyImageRootpath = base_path() . "/public/img/Position1/";
            if (!File::exists($folderPath)) {
                File::makeDirectory($folderPath, $mode = 0777, true);
            }
            $image = "Position1a.png";
            if ($request->position == 1) {
                $image = "Position1a.png";
            } elseif ($request->position == 2) {
                $image = "Position2a.png";
            } elseif ($request->position == 3) {
                $image = "Position3a.png";
            } elseif ($request->position == 4) {
                $image = "Position4a.png";
            }
            if (copy($playerDummyImageRootpath . $image, $folderPath . $image)) {
                $obj->image = $newFolder . $image;
            }
        }

        $obj->save();

        $sortBy = "players.created_at";
        $order = 'DESC';
        $result = Player::where('players.club', $this->userDetail->id)
            ->orderBy($sortBy, $order)
            ->paginate(10);

        $data['status'] = 200;
        $data['data'] = $result;
        $data['message'] = "Player has been updated successfully.";
        return response()->json($data);
    }

    public function getTeamPositionValueBatBowlStyle(Request $request)
    {

        $drop_down = new DropDown();
        $teamList    =    Team::where('is_active', 1)->where('club', $this->userDetail->id)->select('name', 'id')->get();
        $position    =    DropDown::where('dropdown_type', 'position')->where('is_active', 1)->orderBy('name', 'ASC')->select('name', 'id')->get();
        $batStyle = $drop_down->get_master_list("bat-style");
        $bowlStyle = $drop_down->get_master_list("bowl-style");

        $svalue = PlayerPrice::where('club_id', $this->userDetail->id)->orderby('price', 'desc')->get();

        $checkPlayerExists = Player::where('club', $this->userDetail->id)->exists();
        $defaultValue = [
            ['value' => '15.00', 'name' => '$15.00m'],
            ['value' => '14.50', 'name' => '$14.50m'],
            ['value' => '14.00', 'name' => '$14.00m'],
            ['value' => '13.50', 'name' => '$13.50m'],
            ['value' => '13.00', 'name' => '$13.00m'],
            ['value' => '12.50', 'name' => '$12.50m'],
            ['value' => '12.00', 'name' => '$12.00m'],
            ['value' => '11.50', 'name' => '$11.50m'],
            ['value' => '11.00', 'name' => '$11.00m'],
            ['value' => '10.50', 'name' => '$10.50m'],
            ['value' => '10.00', 'name' => '$10.00m'],
            ['value' => '9.50', 'name' => '$9.50m'],
            ['value' => '9.00', 'name' => '$9.00m'],
            ['value' => '8.50', 'name' => '$8.50m'],
            ['value' => '8.00', 'name' => '$8.00m'],
            ['value' => '7.50', 'name' => '$7.50m'],
            ['value' => '7.00', 'name' => '$7.00m'],
        ];

        $data['status'] = 200;
        $data['teamList'] = $teamList;
        $data['position'] = $position;
        $data['batStyle'] = $batStyle;
        $data['bowlStyle'] = $bowlStyle;
        $data['value'] = $svalue;
        $data['player_price_type'] = $this->userDetail->player_price_type;
        $data['player_exists'] = $checkPlayerExists;
        return response()->json($data);
    }
    public function fantasyValue(Request $request)
    {
        $customValue = PlayerPrice::where('club_id', $this->userDetail->id)->orderby('sn', 'asc')->get();
        $defaultPlayerPrice = User::where('id', $this->userDetail->id)->value('player_price_type');
        if ($customValue->isEmpty()) {
            for ($i = 1; $i <= 16; $i++) {
                $obj = new PlayerPrice;
                if ($request->priceId) {
                    $obj = PlayerPrice::findOrFail($request->priceId);
                }
                $obj->club_id = $this->userDetail->id;
                $obj->price = "";
                $obj->price_name = "";
                $obj->sn = $i;
                $obj->save();
            }
        }
        $defaultValue = [
            ['value' => '15.00', 'name' => '$15.00m'],
            ['value' => '14.50', 'name' => '$14.50m'],
            ['value' => '14.00', 'name' => '$14.00m'],
            ['value' => '13.50', 'name' => '$13.50m'],
            ['value' => '13.00', 'name' => '$13.00m'],
            ['value' => '12.50', 'name' => '$12.50m'],
            ['value' => '12.00', 'name' => '$12.00m'],
            ['value' => '11.50', 'name' => '$11.50m'],
            ['value' => '11.00', 'name' => '$11.00m'],
            ['value' => '10.50', 'name' => '$10.50m'],
            ['value' => '10.00', 'name' => '$10.00m'],
            ['value' => '9.50', 'name' => '$9.50m'],
            ['value' => '9.00', 'name' => '$9.00m'],
            ['value' => '8.50', 'name' => '$8.50m'],
            ['value' => '8.00', 'name' => '$8.00m'],
            ['value' => '7.50', 'name' => '$7.50m'],
            ['value' => '7.00', 'name' => '$7.00m'],
        ];

        $playerpriceList = PlayerPrice::where('club_id', $this->userDetail->id)->select('price')->orderby('sn', 'asc')->get();
        $data['status'] = 200;
        $data['custom_value'] = $playerpriceList;
        $data['default_value'] = $defaultValue;
        $data['default_player_price'] = $defaultPlayerPrice;
        $data['playerpriceList'] = $playerpriceList;
        return response()->json($data);
    }
    public function updateFantastValue(Request $request)
    {
        $thisData = $request->all();
        $validator = Validator::make(
            $request->all(),
            array(
                'price.*' => 'required|distinct|numeric|min:2|max:50',

            ),
            array(
                'price.*.required' => "Fantasy Value is required.",
                'price.*.min' => "Fantasy Value must not be less than 2. Two decimal values allowed (example: 2.15).",
                'price.*.max' => "Fantasy Value must not be greater than 99. Two decimal values allowed (example: 12.15).",
            )
        );


        if ($validator->fails()) {
            $errors = $validator->messages();
            $data['status'] = 501;
            $data['success'] = false;
            $data['errors'] = $errors;
            return response()->json($data);
        } else {
            foreach ($request->price as $key => $value) {
                // prd($value);
                $obj = new PlayerPrice;
                $obj->club_id = $this->userDetail->id;
                $obj->price = $value;
                $obj->price_name = "$" . round($value, 2) . "m";
                $obj->save();
            }
            $data['status'] = 200;
            $data['message'] = "Player price has been updated successfully.";
            return response()->json($data);
        }
    }
    function  savePlayerPrice(Request $request)
    {
        foreach ($request->data as $key => $value) {
            $obj = PlayerPrice::find($value['id']);
            $obj->price = $value['price'];
            $obj->price_name = "$" . round($value['price'], 2) . "m";
            $obj->save();
        }
        $data['success'] = true;
        $data['status'] = 200;
        $data['message'] = "Fantasy Values has been saved successfully.";
        return response()->json($data);
    }
    function saveDefaultPriceStructure(Request $request)
    {
        $obj =  User::find($this->userDetail->id);
        $obj->player_price_type = $request->salary_structure;
        $obj->save();
        $data['success'] = true;
        $data['status'] = 200;
        $data['message'] = "Default Player Price updated successfully.";
        return response()->json($data);
    }
    function savePlayeStructure(Request $request)
    {

        $multiPlayerId = MultiPlayer::where('club_id', $this->userDetail->id)->value('id');
        $obj = new MultiPlayer;
        if (!empty($multiPlayerId)) {
            $obj = MultiPlayer::find($multiPlayerId);
        }
        $obj->club_id = $this->userDetail->id;
        $obj->min_bats = $request->min_bat;
        $obj->max_bats = $request->max_bat;
        $obj->min_bowls = $request->min_bowl;
        $obj->max_bowls = $request->max_bowl;
        $obj->min_ar = $request->min_ar;
        $obj->max_ar = $request->max_ar;
        $obj->min_wks = $request->min_wk;
        $obj->max_wks = $request->max_wk;
        $obj->player_allowed = $request->max_wk;
        $saved = $obj->save();

        if ($saved) {
            // User::where('id', $this->userDetail->id)->update(['multi_players_id' => $multiPlayerId]);
        }
        $data['success'] = true;
        $data['status'] = 200;
        $data['message'] = "Player Structure has been saved successfully.";
        return response()->json($data);
    }
    function getPlayerStructureInfo(Request $request)
    {
        $details = MultiPlayer::where('club_id', $this->userDetail->id)->first();
        if (empty($details)) {
            $obj = new MultiPlayer;
            $obj->club_id = $this->userDetail->id;
            $obj->min_bats = 1;
            $obj->max_bats = 5;
            $obj->min_bowls = 1;
            $obj->max_bowls = 5;
            $obj->min_ar = 1;
            $obj->max_ar = 5;
            $obj->min_wks = 1;
            $obj->max_wks = 5;
            $obj->player_allowed = 11;
            $obj->save();
            User::where('id', $this->userDetail->id)->update(['multi_players_id' => 1002]);
        }


        $data['success'] = true;
        $data['status'] = 200;
        $data['data'] = $details;
        return response()->json($data);
    }
    function playerProfileClaimListing(Request $request)
    {
        $verifyPlayer = VerifyUser::leftJoin('users', 'users.id', '=', 'verify_users.user_id')
            ->where(['club_id' => $this->userDetail->id, "verify_users.is_approved" => 3])
            ->with('playerData:players.id,full_name,dropdown_managers.name as position_name,svalue')
            ->select('verify_users.*', 'users.full_name as user_full_name')
            ->get();
        $data['status'] = 200;
        $data['data'] = $verifyPlayer;
        return response()->json($data);
    }
    function verifyPlayerRequest(Request $request)
    {
        $id = $request->id;
        $aproveStatus = $request->status;
        VerifyUser::where('id', '=', $id)->update(array('is_approved' => $aproveStatus));
        $data['status'] = 200;
        $data['message'] = "Player has been approved successfully.";
        return response()->json($data);
    }

    function updateStatus($id = 0, $status = 0)
    {
        Player::where('id', '=', $id)->update(array('is_active' => $status));

        $msg = "";
        if ($status  == 1) {
            $msg = "Player has been activated.";
        } else {
            $msg = "Player has been deactivated.";
        }
        $getData =  Player::findOrFail($id);
        $data['status'] = 200;
        $data['message'] = $msg;
        $data['data'] = $getData;
        return response()->json($data);
    }
}

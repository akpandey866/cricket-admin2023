<?php

namespace App\Http\Controllers;

use App\Model\Player;
use App\Model\PlayerPrice;
use App\Model\Team;
use App\Model\DropDown;
use App\Model\User;
use Illuminate\Http\Request;
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
        $obj->first_name = $request->first_name;
        $obj->last_name = $request->last_name;
        $obj->position = $request->position;
        $obj->svalue = $request->value;
        $obj->initial_value = $request->value;
        $obj->team_id = $request->team;
        $obj->bat_style = !empty($request->bat_style) ? $request->bat_style : 0;
        $obj->bowl_style = !empty($request->bowl_style) ? $request->bowl_style : 0;
        $obj->save();

        $data['success'] = true;
        $data['status'] = 200;
        $data['message'] = "Player has been added successfully.";
        return response()->json($data);
    }
    public function deletePlayer(Request $request)
    {
        try {
            Player::where('id', $request->id)->delete();
            $data['status'] = 200;
            // $data['data'] = $result;
            $data['message'] = "Player has been deleted successfully.";
            return response()->json($data);
        } catch (\Exception $e) {
            $data['success'] = false;
            $data['status'] = 501;
            $data['message'] = $e->getMessage();
        }
    }

    public function playerDetail(Request $request)
    {
        $playerDetails =    Player::findOrFail($request->id);
        $data['status'] = 200;
        $data['data'] = $playerDetails;
        return response()->json($data);
    }
    public function editPlayer(Request $request)
    {

        $obj =  Player::find($request->playerId);
        $obj->full_name    =  $request->name;
        $obj->save();

        $data['status'] = 200;
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

        $data['status'] = 200;
        $data['teamList'] = $teamList;
        $data['position'] = $position;
        $data['batStyle'] = $batStyle;
        $data['bowlStyle'] = $bowlStyle;
        $data['value'] = $svalue;
        return response()->json($data);
    }
    public function fantasyValue(Request $request)
    {
        $customValue = PlayerPrice::where('club_id', $this->userDetail->id)->orderby('price', 'desc')->get();
        $defaultPlayerPrice = User::where('id', $this->userDetail->id)->value('player_price_type');
        if ($customValue->isEmpty()) {
            for ($i = 1; $i <= 20; $i++) {
                $obj = new PlayerPrice;
                if ($request->priceId) {
                    $obj = PlayerPrice::findOrFail($request->priceId);
                }
                $obj->club_id = $this->userDetail->id;
                $obj->price = 0;
                $obj->price_name = "";
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
            ['value' => '6.50', 'name' => '$6.50m'],
            ['value' => '6.00', 'name' => '$6.00m'],
        ];
        $data['status'] = 200;
        $data['custom_value'] = $customValue;
        $data['default_value'] = $defaultValue;
        $data['default_player_price'] = $defaultPlayerPrice;
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
            $obj =     PlayerPrice::find($value['id']);
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
}

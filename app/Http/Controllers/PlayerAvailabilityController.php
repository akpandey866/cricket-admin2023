<?php

namespace App\Http\Controllers;

use App\Model\Player;
use App\Model\Availability;
use Illuminate\Http\Request;
use Validator;

class PlayerAvailabilityController extends Controller
{
    private $userDetail = null;
    public function __construct()
    {
        $this->userDetail = auth('api')->user();
    }
    public function index(Request $request)
    {

        if ($request->limit != "undefined") {
            $DB  = Availability::query();
            if (!empty($request->player_name)) {
                $DB->where("players.full_name", 'like', '%' . $request->full_name . '%');
            }
            if (!empty($request->svalue)) {
                $DB->where("players.svalue", 'like', '%' . $request->svalue . '%');
            }
            if ($request->is_active == 0 || $request->is_active == 1) {
                $DB->where("players.is_active", 'like', '%' . $request->is_active . '%');
            }
            $sortBy = "availabilities.created_at";
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

            $result = $DB->leftJoin('players', 'players.id', '=', 'availabilities.player')
                ->orderBy($sortBy, $order)
                ->where('availabilities.club', $this->userDetail->id)
                ->select('availabilities.*', 'players.full_name as player_name')
                ->paginate($pageLimit);
        }

        $data['success'] = true;
        $data['status'] = 200;
        $data['data'] = $result;
        $data['message'] = "Data fetched sucessfully.";
        return response()->json($data);
    }
    public function deleteAvailability(Request $request)
    {
        $result = Availability::leftJoin('players', 'players.id', '=', 'availabilities.player')
            ->orderBy("availabilities." . "created_at", "desc")
            ->where('availabilities.club', $this->userDetail->id)
            ->select('availabilities.*', 'players.full_name as player_name')
            ->paginate(10);
        Availability::where('id', $request->id)->delete();
        $data['status'] = 200;
        $data['data'] = $result;
        $data['message'] = "Player Availability has been deleted successfully.";
        return response()->json($data);
    }

    public function availabilityDetail(Request $request)
    {
        $playerDetails =    Availability::findOrFail($request->id);
        $data['status'] = 200;
        $data['data'] = $playerDetails;
        return response()->json($data);
    }
    public function editAvailability(Request $request)
    {

        $id = $request->avId;
        $obj =  Availability::find($id);
        $obj->date_from = $request->date_from;
        $obj->date_till = $request->date_till;
        $obj->reason = $request->reason;
        $obj->save();

        $result = Availability::leftJoin('players', 'players.id', '=', 'availabilities.player')
            ->orderBy("availabilities.id", "desc")
            ->where('availabilities.club', $this->userDetail->id)
            ->select('availabilities.*', 'players.full_name as player_name')
            ->paginate(10);

        $data['status'] = 200;
        $data['data'] = $result;
        $data['message'] = "Availability has been upated successfully.";
        return response()->json($data);
    }
    public function saveAvailability(Request $request)
    {

        $playerList = $request->player;
        foreach ($playerList as $key => $value) {

            $availabiltyDetail = Availability::where('club', $this->userDetail->id)
                ->where('player', $value)
                ->where('date_from', $request->date_from)
                ->where('date_till', $request->date_till)
                ->exists();
            if ($availabiltyDetail) {
                $data['status'] = 500;
                $data['message'] = "Player dates already in records.Please try with another dates.";
                return response()->json($data);
            }

            $obj = new Availability;
            $obj->player = $value;
            $obj->date_from = $request->date_from;
            $obj->date_till = $request->date_till;
            $obj->reason = $request->reason;
            $obj->club = $this->userDetail->id;
            $obj->mode = 1;
            $obj->save();


            $result = Availability::leftJoin('players', 'players.id', '=', 'availabilities.player')
                ->orderBy("availabilities.id", "desc")
                ->where('availabilities.club', $this->userDetail->id)
                ->select('availabilities.*', 'players.full_name as player_name')
                ->paginate(10);
            $data['status'] = 200;
            $data['data'] = $result;
            $data['message'] = "Availability has been upated successfully.";
            return response()->json($data);
        }
    }
    function editPlayerList(Request $request)
    {
        $selectedAvailabilityPlayer = Availability::where(['club_id', $this->userDetail->id])->whereNot('player', $request->player_id)->pluck('player')->all();
        $query   = Player::where('club', $this->userDetail->id)->select('full_name', 'id')->whereNotIn('id', $selectedAvailabilityPlayer)->orderBy('full_name', 'ASC')->get();
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
    function updateStatus($id = 0, $status = 0)
    {
        Availability::where('id', '=', $id)->update(array('is_active' => $status));
        $msg = "";
        if ($status  == 1) {
            $msg = "Availability has been activated.";
        } else {
            $msg = "Availability has been deactivated.";
        }
        $data['status'] = 200;
        $data['message'] = $msg;
        return response()->json($data);
    }
}

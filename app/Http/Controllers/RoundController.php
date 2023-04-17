<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Model\GameweekRound;
use App\Model\DropDown;
use Illuminate\Http\Request;


class RoundController extends Controller
{
    private $userDetail = null;
    public function __construct()
    {
        $this->userDetail = auth('api')->user();
    }
    public function index(Request $request)
    {

        if ($request->limit != "undefined") {
            $DB                     =     GameweekRound::query();
            if (!empty($request->lockout_start_time)) {
                $parsDate = Date('Y-m-d h:i:s', strtotime($request->lockout_start_time));
                $DB->where("lockout_start_time", 'like', '%' . $parsDate . '%');
            }
            if (!empty($request->grade)) {
                $DB->where("grade", 'like', '%' . $request->grade . '%');
            }
            $sortBy = "created_at";
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
            $result =   $DB->where('club_id', $this->userDetail->id)
                ->orderBy($sortBy, $order)
                ->paginate($pageLimit);
        }

        $data['success'] = true;
        $data['status'] = 200;
        $data['data'] = $result;
        $data['message'] = "Data fetched sucessfully.";
        return response()->json($data);
    }
    public function saveRound(Request $request)
    {

        $obj =  new GameweekRound;
        $obj->club_id    =  $this->userDetail->id;
        $obj->round    =  $request->round;
        $obj->start_date =  !empty($request->start_date) ?  $request->start_date : date('Y-m-d', time());
        $obj->end_date = !empty($request->end_date) ?  $request->end_date : date('Y-m-d', time());
        $obj->lockout_start_time = !empty($request->lockout_start_time) ?  $request->lockout_start_time : date('Y-m-d H:i', time());
        $obj->lockout_end_time = !empty($request->lockout_end_time) ?  $request->lockout_end_time : date('Y-m-d H:i', time());
        $obj->save();

        $result = GameweekRound::where('club_id', $this->userDetail->id)->orderBy('created_at', 'DESC')->limit(10)->get();
        $data['success'] = true;
        $data['status'] = 200;
        $data['data'] = $result;
        $data['message'] = "Round has been added successfully.";
        return response()->json($data);
    }
    public function deleteRound(Request $request)
    {
        GameweekRound::where('id', $request->id)->delete();
        $result =  GameweekRound::where('club_id', $this->userDetail->id)->orderBy('created_at', 'DESC')->paginate(10);
        $data['status'] = 200;
        $data['data'] = $result;
        $data['message'] = "Round has been deleted successfully.";
        return response()->json($data);
    }

    public function roundDetail(Request $request)
    {
        $gradeDetails =    GameweekRound::findOrFail($request->id);
        $data['status'] = 200;
        $data['data'] = $gradeDetails;
        return response()->json($data);
    }
    public function editRound(Request $request)
    {
        $obj =  GameweekRound::findOrFail($request->round_id);
        $obj->round    =  $request->round;
        $obj->start_date    =  $request->start_date;
        $obj->end_date        =  $request->end_date;
        $obj->lockout_start_time    =  $request->lockout_start_time;
        $obj->lockout_end_time    =  $request->lockout_end_time;
        $obj->save();

        $result = GameweekRound::where('club_id', $this->userDetail->id)->orderBy('created_at', 'DESC')->limit(10)->paginate(10);

        $data['status'] = 200;
        $data['data'] = $result;
        $data['message'] = "Round has been updated successfully.";
        return response()->json($data);
    }
}

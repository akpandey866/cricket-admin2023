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
            $sortBy = "round";
            $order = 'ASC';
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


        $upCommingRound = GameweekRound::where('club_id', $this->userDetail->id)->orderBy('round', 'desc')->value('round');

        $data['success'] = true;
        $data['status'] = 200;
        $data['data'] = $result;
        $data['rd_number'] = !empty($upCommingRound) ? $upCommingRound + 1 : 1;
        $data['message'] = "Data fetched sucessfully.";
        return response()->json($data);
    }
    public function saveRound(Request $request)
    {
        $roundExists = GameweekRound::where('club_id', $this->userDetail->id)->where('round', $request->round)->exists();
        $startDateTime =  $request->start_date . " " . $request->start_time;
        $endDateTime = $request->end_date . " " . $request->end_time;
        // $roundDateExists = GameweekRound::where('club_id', $this->userDetail->id)
        //     ->whereNotBetween('start_date', [$startDateTime, $endDateTime])
        //     ->orWhereNotBetween('end_date', [$startDateTime, $endDateTime])
        //     ->first();
        $startDateExists = GameweekRound::where('club_id', $this->userDetail->id)
            ->where('start_date', '>=', $startDateTime)
            ->exists();
        $endDateExists = GameweekRound::where('club_id', $this->userDetail->id)
            ->where('end_date', '>=', $startDateTime)
            ->exists();

        if ($startDateExists && $endDateExists) {
            if ($startDateExists) {
                $data['success'] = false;
                $data['status'] = 401;
                $data['message'] = "A Round within this Date/Time range already exists! Please select a different Date/Time range.";
                return response()->json($data);
            }
        }
        if ($startDateExists) {
            $data['success'] = false;
            $data['status'] = 401;
            $data['message'] = "An existing Round is set to begin after the set Start Time of your selected Start Date! Please select a different Start Time.";
            return response()->json($data);
        }
        if ($endDateExists) {
            $data['success'] = false;
            $data['status'] = 401;
            $data['message'] = "An existing Round is set to begin after the set End Time of your selected End Date! Please select a different End Time.";
            return response()->json($data);
        }
        if ($roundExists) {
            $data['success'] = false;
            $data['status'] = 401;
            $data['message'] = "Round Already exists. Please change round.";
            return response()->json($data);
        }
        if (!empty($roundDateExists)) {
            $data['success'] = false;
            $data['status'] = 401;
            $data['message'] = "Start date exists in records. Please choose another date or time.";
            return response()->json($data);
        }

        if (!empty($request->start_time) && !empty($request->end_time) && strtotime($request->end_time) <= strtotime($request->start_time)) {
            $data['success'] = false;
            $data['status'] = 401;
            $data['message'] = "End time can not be less then or equal to start time.";
            return response()->json($data);
        }
        $obj =  new GameweekRound;
        $obj->club_id    =  $this->userDetail->id;
        $obj->round    =  $request->round;
        $obj->start_date =  $startDateTime;
        $obj->end_date = $endDateTime;
        $obj->lockout_start_time = !empty($request->start_time) ?  $request->start_time : date('H:i', time());
        $obj->lockout_end_time = !empty($request->end_time) ?  $request->end_time : date('H:i', time());
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
        $startDateTime =  date('Y-m-d', strtotime($request->start_date)) . " " . $request->start_time;
        $endDateTime = date('Y-m-d', strtotime($request->end_date)) . " " . $request->end_time;
        $preRoundDate =  GameweekRound::where('club_id', $this->userDetail->id)->orderBy('round', 'asc')->first();

        if (!empty($preRoundDate) && ($preRoundDate->start_date > $startDateTime)) {
            $data['success'] = false;
            $data['status'] = 401;
            $data['message'] = "Start date should be greater than last rounds start date";
            return response()->json($data);
        }
        $startDateExists = GameweekRound::where('club_id', $this->userDetail->id)
            ->where('round', '<', $request->round)
            ->where('start_date', '<=', $startDateTime)
            ->where('start_date', '>=', $endDateTime)
            ->orderby('start_date', 'desc')
            ->first();

        if (!empty($startDateExists)) {
            if ($startDateExists->start_date > $startDateTime) {
                $data['success'] = false;
                $data['status'] = 401;
                $data['message'] = "Start date should be greater than last rounds start date";
                return response()->json($data);
            }
            // prd($startDateExists->end_date);
            if ($startDateExists->end_date > $endDateTime) {
                $data['success'] = false;
                $data['status'] = 401;
                $data['message'] = "End date should be greater than last rounds start date";
                return response()->json($data);
            }


            if ($startDateExists) {
                $data['success'] = false;
                $data['status'] = 401;
                $data['message'] = "aaa An existing Round is set to begin after the set Start Time of your selected Start Date! Please select a different Start Time.";
                return response()->json($data);
            }
        }
        $betweenStartDateCondition = GameweekRound::where('club_id', $this->userDetail->id)
            ->where('round', '<', $request->round)
            ->where('start_date', '<=', $startDateTime)
            ->where('end_date', '>=', $startDateTime)
            ->orderby('start_date', 'desc')
            ->exists();

        $betweenEndDateCondition = GameweekRound::where('club_id', $this->userDetail->id)
            ->where('round', '<', $request->round)
            ->where('start_date', '<=', $endDateTime)
            ->where('end_date', '>=', $endDateTime)
            ->orderby('start_date', 'desc')
            ->exists();

        if ($betweenStartDateCondition || $betweenEndDateCondition) {
            $data['success'] = false;
            $data['status'] = 401;
            $data['message'] = "A Round within this Date/Time range already exists! Please select a different Date/Time range.";
            return response()->json($data);
        }
        if (!empty($request->start_time) && !empty($request->end_time) && strtotime($request->end_time) <= strtotime($request->start_time)) {
            $data['success'] = false;
            $data['status'] = 401;
            $data['message'] = "End time can not be less then or equal to start time.";
            return response()->json($data);
        }

        $obj =  GameweekRound::findOrFail($request->round_id);
        // $obj->round    =  $request->round;
        $obj->start_date    =  $startDateTime;
        $obj->end_date        =  $endDateTime;
        $obj->lockout_start_time    =  $request->start_time;
        $obj->lockout_end_time    =  $request->end_time;
        $obj->save();

        $result = GameweekRound::where('club_id', $this->userDetail->id)->orderBy('round', 'asc')->limit(10)->paginate(10);

        $data['status'] = 200;
        $data['data'] = $result;
        $data['message'] = "Round has been updated successfully.";
        return response()->json($data);
    }
}

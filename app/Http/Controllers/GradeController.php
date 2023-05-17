<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Model\Grade;
use App\Model\DropDown;
use App\Model\GamePoint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class GradeController extends Controller
{
    private $userDetail = null;
    public function __construct()
    {
        $this->userDetail = auth('api')->user();
    }
    public function index(Request $request)
    {

        if ($request->limit != "undefined") {
            $DB                     =     Grade::query();
            if (!empty($request->sn)) {
                $DB->where("grades.sn", 'like', '%' . $request->sn . '%');
            }
            if (!empty($request->grade)) {
                $DB->where("grades.grade", 'like', '%' . $request->grade . '%');
            }
            if ($request->is_active == 0 || $request->is_active == 1) {
                $DB->where("grades.is_active", 'like', '%' . $request->is_active . '%');
            }
            $sortBy = "grades.created_at";
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
            $result =     $DB->select('grades.*', 'users.game_mode as game_mode', 'users.game_name as game_name')
                ->where('club', $this->userDetail->id)
                ->leftJoin('users', 'grades.club', '=', 'users.id')
                ->orderBy($sortBy, $order)
                ->paginate($pageLimit);
        }

        $data['success'] = true;
        $data['status'] = 200;
        $data['data'] = $result;
        $data['message'] = "Data fetched sucessfully.";
        return response()->json($data);
    }
    public function saveGrade(Request $request)
    {
        $obj =  new Grade;
        $obj->grade    =  $request->grade;
        $obj->club    =  $this->userDetail->id;
        $obj->mode    =  1;
        $obj->save();
        Grade::where('id', '=', $obj->id)->update(array('sn' => 'C' . sprintf("%05d", ($obj->id))));
        $data['success'] = true;
        $data['status'] = 200;
        $data['data'] = $obj;
        $data['message'] = "Grade has been added successfully.";
        return response()->json($data);
    }
    public function deleteGrade(Request $request)
    {
        try {
            Grade::where('id', $request->id)->delete();
            $result =     Grade::select('grades.*', 'users.game_mode as game_mode', 'users.game_name as game_name')
                ->where('club', $this->userDetail->id)
                ->leftJoin('users', 'grades.club', '=', 'users.id')
                ->orderBy('created_at', 'DESC')
                ->paginate(10);
            $data['status'] = 200;
            $data['data'] = $result;
            $data['message'] = "Grade has been deleted successfully.";
            return response()->json($data);
        } catch (\Exception $e) {
            $data['success'] = false;
            $data['status'] = 501;
            $data['message'] = $e->getMessage();
        }
    }

    public function gradeDetail(Request $request)
    {
        $gradeDetails =    Grade::findOrFail($request->id);
        $data['status'] = 200;
        $data['data'] = $gradeDetails;
        return response()->json($data);
    }
    public function editGrade(Request $request)
    {
        $obj =  Grade::findOrFail($request->gradeId);
        $obj->grade    =  $request->grade;
        $obj->club    =  $this->userDetail->id;
        $obj->mode    =  1;
        if (empty($obj->sn)) {
            $obj->sn         =  'G' . sprintf("%05d", ($request->gradeId));
        }
        $obj->save();
        $result =     Grade::select('grades.*', 'users.game_mode as game_mode', 'users.game_name as game_name')
            ->where('club', $this->userDetail->id)
            ->leftJoin('users', 'grades.club', '=', 'users.id')
            ->orderBy('grades.created_at', 'desc')
            ->paginate(10);
        $data['status'] = 200;
        $data['data'] = $result;
        $data['message'] = "Grade has been updated successfully.";
        return response()->json($data);
    }

    public function gradeData(Request $request)
    {
        $gradeName = Grade::where('club', $this->userDetail->id)->select('grade', 'id')->get();
        $drop_down = new DropDown();
        $matchTypeList = $drop_down->get_master_list("matchtype");
        $data['status'] = 200;
        $data['data'] = $gradeName;
        $data['matchTypeList'] = $matchTypeList;
        return response()->json($data);
    }

    public function gradePointSystem(Request $request)
    {

        $rowCount = GamePoint::where('club', $this->userDetail->id)->where('grade_id', $request->gradeId)->count();
        if ($rowCount == 0) {
            $defaultData = GamePoint::where('club', 0)->orderBy('id', 'ASC')->get();
            if (!$defaultData->isEmpty()) {
                foreach ($defaultData as $key => $value) {
                    $gameObj                 = new GamePoint;
                    $gameObj->attribute_name = $value->attribute_name;
                    $gameObj->attribute_key = $value->attribute_key;
                    $gameObj->bowler         = $value->bowler;
                    $gameObj->bats     = $value->bats;
                    $gameObj->wk     = $value->wk;
                    $gameObj->ar     = $value->ar;
                    $gameObj->grade_id = $request->gradeId;
                    $gameObj->club     = $this->userDetail->id;
                    $gameObj->save();
                }
            }
        }
        $result = GamePoint::where('club', $this->userDetail->id)->where('grade_id', $request->gradeId)->get();
        $data['status'] = 200;
        $data['data'] = $result;
        return response()->json($data);
    }
    public function updateGradePointSystem(Request $request)
    {

        foreach ($request->data as $key => $value) {
            $obj    = GamePoint::find($value['id']);
            $obj->bowler     = $value['bowler'];
            $obj->bats     = $value['bats'];
            $obj->wk = $value['wk'];
            $obj->ar = $value['ar'];
            $obj->club = $this->userDetail->id;
            $obj->grade_id = !empty($value['grade_id']) ? $value['grade_id'] : '';
            $obj->save();
        }
        $data['success'] = true;
        $data['status'] = 200;
        $data['message'] = "Game Points has been updated successfully.";
        return response()->json($data);
    }

    public function resetToDefault(Request $request)
    {
        $gradeId = $request->gradeId;
        $defaultData = GamePoint::where('club', 0)->orderBy('id', 'ASC')->get();
        if (!$defaultData->isEmpty()) {
            if (!empty($gradeId)) {
                GamePoint::where('club', $this->userDetail->id)->where('grade_id', $gradeId)->delete();
            } else {
                GamePoint::where('club', $this->userDetail->id)->delete();
            }

            foreach ($defaultData as $key => $value) {
                $gameObj                 = new GamePoint;
                $gameObj->attribute_name = $value->attribute_name;
                $gameObj->attribute_key = $value->attribute_key;
                $gameObj->bowler         = $value->bowler;
                $gameObj->bats     = $value->bats;
                $gameObj->wk     = $value->wk;
                $gameObj->ar     = $value->ar;
                $gameObj->club      = $this->userDetail->id;
                $gameObj->grade_id = $gradeId;
                $gameObj->save();
            }

            $data['success'] = true;
            $data['status'] = 200;
            $data['message'] = "Game Points has been set to MCT default.";
            return response()->json($data);
        }
    }

    public function multiplyGrade(Request $request)
    {
        $gradeId = $request->gradeId;
        $mNumber =  $request->point;
        $gamePointsVariable = GamePoint::where('grade_id', $gradeId)->get();
        GamePoint::where('grade_id', $gradeId)->delete();
        foreach ($gamePointsVariable as $key => $value) {
            $gameObj                 = new GamePoint;
            $gameObj->attribute_name = $value->attribute_name;
            $gameObj->attribute_key = $value->attribute_key;
            $gameObj->bowler         = $value->bowler * $mNumber;
            $gameObj->bats     = $value->bats * $mNumber;
            $gameObj->wk     = $value->wk * $mNumber;
            $gameObj->ar     = $value->ar * $mNumber;
            $gameObj->club   = $this->userDetail->id;
            $gameObj->grade_id = $gradeId;
            $gameObj->save();
        }
        $data['success'] = true;
        $data['status'] = 200;
        $data['message'] = "Grade has been multiplied successfully.";
        return response()->json($data);
    }

    public function copyGrade(Request $request)
    {
        $grade = $request->grade;
        $gradeId =  $request->gradeId;
        $fromGrade = GamePoint::where('grade_id', $grade)->get();
        GamePoint::where('grade_id', $gradeId)->delete();
        foreach ($fromGrade as $key => $value) {
            $gameObj = new GamePoint;
            $gameObj->attribute_name = $value->attribute_name;
            $gameObj->attribute_key = $value->attribute_key;
            $gameObj->bowler = $value->bowler;
            $gameObj->bats = $value->bats;
            $gameObj->wk = $value->wk;
            $gameObj->ar  = $value->ar;
            $gameObj->club = $this->userDetail->id;
            $gameObj->grade_id = $gradeId;
            $gameObj->save();
        }
        $gradeName = Grade::where('id', $gradeId)->value('grade');
        $data['success'] = true;
        $data['status'] = 200;
        $data['message'] =  $gradeName . " has been copied successfully.";
        return response()->json($data);
    }
}

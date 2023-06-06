<?php

namespace App\Http\Controllers;

use App\Model\Team;
use App\Model\Grade;
use App\Model\DropDown;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;


class TeamController extends Controller
{
    private $userDetail = null;
    public function __construct()
    {
        $this->userDetail = auth('api')->user();
    }
    public function index(Request $request)
    {

        if ($request->limit != "undefined") {
            $DB   =     Team::query();
            if (!empty($request->sn)) {
                $DB->where("teams.sn", 'like', '%' . $request->sn . '%');
            }
            if (!empty($request->name)) {
                $DB->where("teams.name", 'like', '%' . $request->name . '%');
            }
            if ($request->is_active == 0 || $request->is_active == 1) {
                $DB->where("teams.is_active", 'like', '%' . $request->is_active . '%');
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


            $result =     $DB->leftJoin('users', 'teams.club', '=', 'users.id')
                ->leftJoin('dropdown_managers', 'teams.team_category', '=', 'dropdown_managers.id')
                ->select('teams.*', 'teams.name as team_name', 'users.club_name', 'dropdown_managers.name')
                ->where('teams.club', $this->userDetail->id)
                ->orderBy("teams." . $sortBy, $order)
                ->paginate($pageLimit);
        }

        $data['success'] = true;
        $data['status'] = 200;
        $data['data'] = $result;
        $data['message'] = "Data fetched sucessfully.";
        return response()->json($data);
    }
    public function saveTeam(Request $request)
    {


        $obj =  new Team;
        $obj->name  =  $request->name;
        $obj->grade_name =  !empty($request->grade_name) ? $request->grade_name : 0;
        $obj->team_category  =  !empty($request->team_category) ? $request->team_category : 0;
        $obj->type =  !empty($request->team_type) ? $request->team_type : 0;
        $obj->club  =  $this->userDetail->id;
        $obj->sponsor_link  =  !empty($request->sponsor_link) ? $request->sponsor_link : '';
        if (!empty($request->image) && $request->image != "undefined" && $request->image != "null") {
            $extension             =    $request->image->getClientOriginalExtension();
            $newFolder             =     strtoupper(date('M') . date('Y')) . '/';
            $folderPath            =     base_path() . "/public/uploads/team_sponsor/" . $newFolder;
            if (!File::exists($folderPath)) {
                File::makeDirectory($folderPath, $mode = 0777, true);
            }
            $userImageName = time() . '-team-sponsor.' . $extension;
            $image = $newFolder . $userImageName;
            if ($request->image->move($folderPath, $userImageName)) {
                $obj->image        =    $image;
            }
        }
        $obj->is_active =  1;
        $obj->save();



        $teamId = $obj->id;
        Team::where('id', '=', $teamId)->update(array('sn' => 'T' . sprintf("%05d", ($teamId))));
        $data['success'] = true;
        $data['status'] = 200;
        $data['data'] = $obj;
        $data['message'] = "Team has been added successfully.";
        return response()->json($data);
    }
    public function deleteTeam(Request $request)
    {
        Team::where('id', $request->id)->delete();
        $result =     Team::leftJoin('users', 'teams.club', '=', 'users.id')
            ->leftJoin('dropdown_managers', 'teams.team_category', '=', 'dropdown_managers.id')
            ->select('teams.*', 'teams.name as team_name', 'users.club_name', 'dropdown_managers.name')
            ->where('teams.club', $this->userDetail->id)
            ->orderBy("teams." . "created_at", "desc")
            ->paginate(10);
        $data['status'] = 200;
        $data['data'] = $result;
        $data['message'] = "Team has been deleted successfully.";
        return response()->json($data);
    }

    public function teamDetail(Request $request)
    {
        $teamDetails =    Team::findOrFail($request->id);
        $data['status'] = 200;
        $data['data'] = $teamDetails;
        return response()->json($data);
    }
    public function editTeam(Request $request)
    {

        $obj =  Team::find($request->teamId);
        $obj->name  =  $request->name;
        $obj->grade_name =  !empty($request->grade_name) ? $request->grade_name : 0;
        $obj->team_category  =  !empty($request->team_category) ? $request->team_category : 0;
        $obj->type =  !empty($request->team_type) ? $request->team_type : 0;
        $obj->sponsor_link  =  !empty($request->sponsor_link) ? $request->sponsor_link : '';
        if (!empty($request->image) && $request->image != "undefined" && $request->image != "null") {
            $extension             =    $request->image->getClientOriginalExtension();
            $newFolder             =     strtoupper(date('M') . date('Y')) . '/';
            @unlink(base_path() . "/public/uploads/team_sponsor/" . $obj->image);
            $folderPath            =     base_path() . "/public/uploads/team_sponsor/" . $newFolder;
            if (!File::exists($folderPath)) {
                File::makeDirectory($folderPath, $mode = 0777, true);
            }
            $userImageName = time() . '-team-sponsor.' . $extension;
            $image = $newFolder . $userImageName;
            if ($request->image->move($folderPath, $userImageName)) {
                $obj->image        =    $image;
            }
        }
        $obj->save();
        $result =     Team::leftJoin('users', 'teams.club', '=', 'users.id')
            ->leftJoin('dropdown_managers', 'teams.team_category', '=', 'dropdown_managers.id')
            ->select('teams.*', 'teams.name as team_name', 'users.club_name', 'dropdown_managers.name')
            ->where('teams.club', $this->userDetail->id)
            ->orderBy("teams." . "created_at", "desc")
            ->paginate(10);
        $data['status'] = 200;
        $data['data'] = $result;
        $data['message'] = "Team has been updated successfully.";
        return response()->json($data);
    }
    public function getTeamListByGrade(Request $request)
    {
        $teamLists = Team::where('club', $this->userDetail->id)->select('name', 'id')->get();
        $drop_down = new DropDown();
        $matchTypeList = $drop_down->get_master_list("matchtype");
        $data['status'] = 200;
        $data['data'] = $teamLists;
        $data['matchTypeList'] = $matchTypeList;
        return response()->json($data);
    }
    function getAddTeamData()
    {

        $gradeList = Grade::where('club', $this->userDetail->id)->select('grade', 'id')->get();
        $drop_down  =    new DropDown();
        $teamCategory =    $drop_down->get_master_list("teamcategory");
        $data['status'] = 200;
        $data['grade_list'] = $gradeList;
        $data['team_category'] = $teamCategory;
        return response()->json($data);
    }
}

<?php

namespace App\Http\Controllers;

use App\Model\Team;
use Illuminate\Http\Request;


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

        try {
            $obj =  new Team;
            $obj->name  =  $request->name;
            $obj->grade_name =  !empty($request->grade_name) ? $request->grade_name : 0;
            $obj->team_category  =  !empty($request->team_category) ? $request->team_category : 0;
            $obj->type =  !empty($request->type) ? $request->type : 0;
            $obj->club  =  !empty($request->club) ? $request->club : 0;
            $obj->sponsor_link  =  !empty($request->sponsor_link) ? $request->sponsor_link : '';
            $obj->is_active =  1;
            $obj->save();

            $teamId = $obj->id;
            Team::where('id', '=', $teamId)->update(array('sn' => 'T' . sprintf("%05d", ($teamId))));

            $data['success'] = true;
            $data['status'] = 200;
            $data['message'] = "Grade has been added successfully.";
            return response()->json($data);
        } catch (\Exception $e) {
            $data['success'] = false;
            $data['status'] = 501;
            $data['message'] = $e->getMessage();
        }
    }
    public function deleteTeam(Request $request)
    {
        try {
            Team::where('id', $request->id)->delete();
            $data['status'] = 200;
            $data['message'] = "Team has been deleted successfully.";
            return response()->json($data);
        } catch (\Exception $e) {
            $data['success'] = false;
            $data['status'] = 501;
            $data['message'] = $e->getMessage();
        }
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
        $obj->name    =  $request->name;
        $obj->save();

        $data['status'] = 200;
        $data['message'] = "Team has been updated successfully.";
        return response()->json($data);
    }
    public function getTeamListByGrade(Request $request)
    {

        $teamLists = Team::where('grade_name', $request->grade_id)->select('name', 'id')->get();
        $data['status'] = 200;
        $data['data'] = $teamLists;
        return response()->json($data);
    }
}

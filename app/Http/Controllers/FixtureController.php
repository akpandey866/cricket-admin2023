<?php

namespace App\Http\Controllers;

use App\Model\Fixture;
use App\Model\Grade;
use App\Model\DropDown;
use App\Model\Team;
use Illuminate\Http\Request;


class FixtureController extends Controller
{
    private $userDetail = null;
    public function __construct()
    {
        $this->userDetail = auth('api')->user();
    }
    public function index(Request $request)
    {

        if ($request->limit != "undefined") {
            $DB                     =     Fixture::leftJoin('users', 'fixtures.opposition_club', '=', 'users.id')
                ->leftJoin('user_teams as user_teams', 'fixtures.team', '=', 'user_teams.id')
                ->leftJoin('users as clubs', 'fixtures.club', '=', 'clubs.id')
                ->leftJoin('grades as grades', 'fixtures.grade', '=', 'grades.id')
                ->leftJoin('dropdown_managers', 'fixtures.team_category', '=', 'dropdown_managers.id')
                ->leftJoin('dropdown_managers as dropdown_managers1', 'fixtures.team_type', '=', 'dropdown_managers1.id')
                ->leftJoin('dropdown_managers as dropdown_managers2', 'fixtures.match_type', '=', 'dropdown_managers2.id')
                ->leftJoin('dropdown_managers as dropdown_managers3', 'fixtures.grade', '=', 'dropdown_managers3.id')
                ->leftJoin('teams', 'fixtures.team', '=', 'teams.id')
                ->where('fixtures.club', $this->userDetail->id)
                ->where('fixtures.status', '!=', 3)
                ->select('fixtures.*', 'clubs.club_name', 'dropdown_managers.name  as team_category', 'dropdown_managers1.name  as team_type', 'dropdown_managers2.name  as match_type', 'dropdown_managers1.name  as grade_name', 'teams.name  as team_name', 'grades.grade', 'user_teams.my_team_name');

            if (!empty($request->team_name)) {
                $DB->where("teams.name", 'like', '%' . $request->team_name . '%');
            }
            if (!empty($request->grade)) {
                $DB->where("grades.grade", 'like', '%' . $request->grade . '%');
            }
            if (!empty($request->match_type)) {
                $DB->where("dropdown_managers2.name", 'like', '%' . $request->match_type . '%');
            }
            if ($request->is_active == 0 || $request->is_active == 1) {
                $DB->where("fixtures.is_active", 'like', '%' . $request->is_active . '%');
            }
            $sortBy = "fixtures.created_at";
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
            $gradeList = Grade::where('club', $this->userDetail->id)
                ->pluck('grade', 'id')
                ->all();
            $teamList = Team::where('club', $this->userDetail->id)
                ->pluck('name', 'id')
                ->all();
            $result = $DB
                ->orderBy($sortBy, $order)
                ->paginate($pageLimit);
        }
        $drop_down = new DropDown();
        $gradeList = $drop_down->get_master_list("gradename");
        $matchTypeList = $drop_down->get_master_list("matchtype");
        $data['success'] = true;
        $data['status'] = 200;
        $data['data'] = $result;
        $data['gradeList'] = $gradeList;
        $data['matchTypeList'] = $matchTypeList;
        $data['message'] = "Data fetched sucessfully.";
        return response()->json($data);
    }
    public function getCompletedFixture(Request $request)
    {

        if ($request->limit != "undefined") {
            $DB = Fixture::leftJoin('users', 'fixtures.opposition_club', '=', 'users.id')
                ->leftJoin('user_teams as user_teams', 'fixtures.team', '=', 'user_teams.id')
                ->leftJoin('users as clubs', 'fixtures.club', '=', 'clubs.id')
                ->leftJoin('grades as grades', 'fixtures.grade', '=', 'grades.id')
                ->leftJoin('dropdown_managers', 'fixtures.team_category', '=', 'dropdown_managers.id')
                ->leftJoin('dropdown_managers as dropdown_managers1', 'fixtures.team_type', '=', 'dropdown_managers1.id')
                ->leftJoin('dropdown_managers as dropdown_managers2', 'fixtures.match_type', '=', 'dropdown_managers2.id')
                ->leftJoin('dropdown_managers as dropdown_managers3', 'fixtures.grade', '=', 'dropdown_managers3.id')
                ->leftJoin('teams', 'fixtures.team', '=', 'teams.id')
                ->where('fixtures.club', $this->userDetail->id)
                ->where('fixtures.status', 3)
                ->select('fixtures.*', 'clubs.club_name', 'dropdown_managers.name  as team_category', 'dropdown_managers1.name  as team_type', 'dropdown_managers2.name  as match_type', 'dropdown_managers1.name  as grade_name', 'teams.name  as team_name', 'grades.grade', 'user_teams.my_team_name');

            if (!empty($request->team_name)) {
                $DB->where("teams.name", 'like', '%' . $request->team_name . '%');
            }
            if (!empty($request->grade)) {
                $DB->where("grades.grade", 'like', '%' . $request->grade . '%');
            }
            if (!empty($request->match_type)) {
                $DB->where("dropdown_managers2.name", 'like', '%' . $request->match_type . '%');
            }
            if ($request->is_active == 0 || $request->is_active == 1) {
                $DB->where("fixtures.is_active", 'like', '%' . $request->is_active . '%');
            }
            $sortBy = "fixtures.created_at";
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
            $gradeList = Grade::where('club', $this->userDetail->id)
                ->pluck('grade', 'id')
                ->all();
            $teamList = Team::where('club', $this->userDetail->id)
                ->pluck('name', 'id')
                ->all();
            $result = $DB
                ->orderBy($sortBy, $order)
                ->paginate($pageLimit);
        }
        $drop_down = new DropDown();
        $gradeList = $drop_down->get_master_list("gradename");
        $matchTypeList = $drop_down->get_master_list("matchtype");
        $data['success'] = true;
        $data['status'] = 200;
        $data['data'] = $result;
        $data['gradeList'] = $gradeList;
        $data['matchTypeList'] = $matchTypeList;
        $data['message'] = "Data fetched sucessfully.";
        return response()->json($data);
    }
    public function saveFixture(Request $request)
    {

        try {
            $obj =  new Fixture;
            $obj->grade    =  $request->grade;
            $obj->team    =  $request->team;
            $obj->match_type    =  $request->match_type;
            $obj->start_date    =  $request->start_date;
            $obj->end_date    =  $request->end_date;
            $obj->start_time    =  $request->start_time;;
            $obj->end_time    =  $request->end_time;;
            $obj->opposition_club    = !empty($request->opposition_club) ? $request->opposition_club : "";
            $obj->vanue    =  !empty($request->vanue) ? $request->vanue : "";
            $obj->club    =  $this->userDetail->id;
            $obj->save();

            $data['success'] = true;
            $data['status'] = 200;
            $data['message'] = "Fixture has been added successfully.";
            return response()->json($data);
        } catch (\Exception $e) {
            $data['success'] = false;
            $data['status'] = 501;
            $data['message'] = $e->getMessage();
        }
    }
    public function editFixture(Request $request)
    {
        $obj =  Fixture::findOrFail($request->fixtureId);
        $obj->grade    =  $request->grade;
        $obj->team    =  $request->team;
        $obj->match_type    =  $request->match_type;
        $obj->start_date    =  $request->start_date;
        $obj->end_date    =  $request->end_date;
        $obj->start_time    =  $request->start_time;;
        $obj->end_time    =  $request->end_time;;
        $obj->opposition_club    = !empty($request->opposition_club) ? $request->opposition_club : "";
        $obj->vanue    =  !empty($request->vanue) ? $request->vanue : "";
        $obj->club    =  $this->userDetail->id;
        $obj->save();
        $data['status'] = 200;
        $data['message'] = "Fixture has been updated successfully.";
        return response()->json($data);
    }
    public function deleteFixture(Request $request)
    {
        try {
            Fixture::where('id', $request->id)->delete();
            $data['status'] = 200;
            $data['message'] = "Fixture has been deleted successfully.";
            return response()->json($data);
        } catch (\Exception $e) {
            $data['success'] = false;
            $data['status'] = 501;
            $data['message'] = $e->getMessage();
        }
    }

    public function FixtureDetail(Request $request)
    {
        $fixtureDetail =    Fixture::findOrFail($request->id);
        $data['status'] = 200;
        $data['data'] = $fixtureDetail;
        return response()->json($data);
    }
    public function getMatchListType(Request $request)
    {
        $drop_down = new DropDown();
        $matchTypeList = $drop_down->get_master_list("matchtype");
        $data['status'] = 200;
        $data['data'] = $matchTypeList;
        return response()->json($data);
    }
}

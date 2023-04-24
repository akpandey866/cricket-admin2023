<?php

namespace App\Http\Controllers;

use App\Model\FeedbackCategory;
use App\Model\FeedbackCochAccess;
use App\Model\UserTeams;
use App\Model\FeedbackManagerAccess;
use App\Model\FeedbackManagerAccessTeam;
use App\Model\FeedbackManagerAccessFixture;
use App\Model\FeedbackPointSystem;
use App\Model\Fixture;
use App\Model\DisplayView;

use Illuminate\Http\Request;


class FeedbackFantasyController extends Controller
{
    private $userDetail = null;
    public function __construct()
    {
        $this->userDetail = auth('api')->user();
    }
    public function cateogryIndex(Request $request)
    {
        $DB = FeedbackCategory::query();

        if (!empty($request->title)) {
            $DB->where("title", 'like', '%' . $request->title . '%');
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

        $result =  $DB->where('club_id', $this->userDetail->id)->orderBy($sortBy, $order)->paginate($pageLimit);
        $data['success'] = true;
        $data['status'] = 200;
        $data['data'] = $result;
        $data['message'] = "Data fetched sucessfully.";
        return response()->json($data);
    }
    public function saveCategory(Request $request)
    {
        $obj =  new FeedbackCategory;
        $obj->name     =  $request->title;
        $obj->description = $request->message;
        $obj->club_id =  $this->userDetail->id;
        $obj->save();


        $result =     FeedbackCategory::where('club_id', $this->userDetail->id)
            ->orderBy("created_at", "DESC")
            ->paginate(10);

        $data['success'] = true;
        $data['status'] = 200;
        $data['data'] = $result;
        $data['message'] = "Category has been added successfully.";
        return response()->json($data);
    }
    public function deleteCategory(Request $request)
    {
        FeedbackCategory::where('id', $request->id)->delete();
        $result =     FeedbackCategory::where('club_id', $this->userDetail->id)
            ->orderBy("created_at", "DESC")
            ->paginate(10);
        $data['status'] = 200;
        $data['data'] = $result;
        $data['message'] = "Category has been deleted successfully.";
        return response()->json($data);
    }

    public function categoryDetail(Request $request)
    {

        $result =    FeedbackCategory::findOrFail($request->id);
        $data['status'] = 200;
        $data['data'] = $result;
        return response()->json($data);
    }
    public function editCategory(Request $request)
    {
        $obj =  FeedbackCategory::findOrFail($request->categoryId);
        $obj->name     =  $request->title;
        $obj->description = $request->message;
        $obj->save();

        $result = FeedbackCategory::where('club_id', $this->userDetail->id)->orderBy("created_at", "DESC")->paginate(10);

        $data['status'] = 200;
        $data['data'] = $result;
        $data['message'] = "Category has been updated successfully.";
        return response()->json($data);
    }

    public function coachListing(Request $request)
    {
        $result = FeedbackCochAccess::leftJoin('users', 'users.id', '=', 'feedback_coch_access.user_id')
            ->where('feedback_coch_access.club_id', $this->userDetail->id)
            ->select('feedback_coch_access.*', 'users.full_name as username', 'users.email as email', 'users.my_team_name')
            ->get();

        $selectedIds = FeedbackCochAccess::where('club_id', $this->userDetail->id)->pluck('user_id', 'user_id')->all();

        $userList = UserTeams::leftJoin('users', 'users.id', '=', 'user_teams.user_id')
            ->where('club_id', $this->userDetail->id)
            ->where('user_teams.is_active', 1)
            ->where('users.id', '!=', $this->userDetail->id)
            ->whereNotIn('users.id', $selectedIds)
            ->orderBy('users.full_name', 'ASC')
            ->select('users.full_name', 'user_teams.user_id')
            ->get();

        $data['status'] = 200;
        $data['data'] = $result;
        $data['coach_listing'] = $userList;
        return response()->json($data);
    }
    function saveFeeddbackManager(Request $request)
    {
        $obj = new FeedbackCochAccess();
        $obj->club_id = $this->userDetail->id;
        $obj->user_id = $request->user;
        $obj->save();

        $result = FeedbackCochAccess::leftJoin('users', 'users.id', '=', 'feedback_coch_access.user_id')
            ->where('feedback_coch_access.club_id', $this->userDetail->id)
            ->select('feedback_coch_access.*', 'users.full_name as username', 'users.email as email', 'users.my_team_name')
            ->get();

        $selectedIds = FeedbackCochAccess::where('club_id', $this->userDetail->id)->pluck('user_id', 'user_id')->all();

        $userList = UserTeams::leftJoin('users', 'users.id', '=', 'user_teams.user_id')
            ->where('club_id', $this->userDetail->id)
            ->where('user_teams.is_active', 1)
            ->where('users.id', '!=', $this->userDetail->id)
            ->whereNotIn('users.id', $selectedIds)
            ->orderBy('users.full_name', 'ASC')
            ->select('users.full_name', 'user_teams.user_id')
            ->get();
        $data['status'] = 200;
        $data['data'] = $result;
        $data['coach_listing'] = $userList;
        $data['message'] = "Feedback manager has been added successfully.";
        return response()->json($data);
    }

    public function deleteFeedbackManager(Request $request)
    {
        FeedbackCochAccess::where('id', $request->id)->delete();
        $result = FeedbackCochAccess::leftJoin('users', 'users.id', '=', 'feedback_coch_access.user_id')
            ->where('feedback_coch_access.club_id', $this->userDetail->id)
            ->select('feedback_coch_access.*', 'users.full_name as username', 'users.email as email', 'users.my_team_name')
            ->get();
        $data['status'] = 200;
        $data['data'] = $result;
        $data['message'] = "Feedback Manager has been deleted successfully.";
        return response()->json($data);
    }
    public function manageAccessByTeam(Request $request)
    {
        $teamManager = FeedbackManagerAccess::leftJoin('users', 'users.id', 'feedback_manager_access.user_id')
            ->where(['club_id' => $this->userDetail->id])
            ->where('type', 1)
            ->select('feedback_manager_access.*', 'users.full_name as username')
            ->get();

        $teamLists = \App\Model\Team::where('club', $this->userDetail->id)->select('name', 'id')->get();
        $selectedIds = FeedbackManagerAccess::where('club_id', $this->userDetail->id)->pluck('user_id', 'user_id')->all();
        $userList = FeedbackCochAccess::leftJoin('users', 'users.id', 'feedback_coch_access.user_id')
            ->where('feedback_coch_access.club_id', $this->userDetail->id)
            ->whereNotIn('feedback_coch_access.user_id', $selectedIds)
            ->select('users.full_name', 'feedback_coch_access.user_id')->get();

        $selectedIds = FeedbackManagerAccess::where('club_id', $this->userDetail->id)->pluck('user_id', 'user_id')->all();
        $userList = FeedbackCochAccess::leftJoin('users', 'users.id', 'feedback_coch_access.user_id')
            ->where('feedback_coch_access.club_id', $this->userDetail->id)
            ->whereNotIn('feedback_coch_access.user_id', $selectedIds)
            ->select('users.full_name', 'feedback_coch_access.user_id')->get();



        $data = [];
        $map = $teamLists->map(function ($item) {

            $data['value'] = $item->id;
            $data['text'] = $item->name;
            return $data;
        });
        $data['status'] = 200;
        $data['data'] = $teamManager;
        $data['team_list'] = $map;
        $data['user_list'] = $userList;
        return response()->json($data);
    }
    public function saveManageAcessByTeam(Request $request)
    {
        $obj = new FeedbackManagerAccess;
        $obj->club_id = $this->userDetail->id;
        $obj->user_id = $request->user;
        $obj->type = 1;
        if ($obj->save()) {
            foreach ($request->team as $key => $value) {
                $objTeam = new FeedbackManagerAccessTeam;
                $objTeam->feedback_manager_access_id  = $obj->id;
                $objTeam->team_id  = $value;
                $objTeam->save();
            }
        }

        $selectedIds = FeedbackManagerAccess::where('club_id', $this->userDetail->id)->pluck('user_id', 'user_id')->all();
        $userList = FeedbackCochAccess::leftJoin('users', 'users.id', 'feedback_coch_access.user_id')
            ->where('feedback_coch_access.club_id', $this->userDetail->id)
            ->whereNotIn('feedback_coch_access.user_id', $selectedIds)
            ->select('users.full_name', 'feedback_coch_access.user_id')->get();
        $teamManager = FeedbackManagerAccess::leftJoin('users', 'users.id', 'feedback_manager_access.user_id')
            ->where(['club_id' => $this->userDetail->id])
            ->where('type', 1)
            ->select('feedback_manager_access.*', 'users.full_name as username')
            ->get();
        $data['status'] = 200;
        $data['data'] = $teamManager;
        $data['user_list'] = $userList;
        $data['message'] = "Manage Access by Team has been added successfully.";
        return response()->json($data);
    }
    public function manageAccessByFixture(Request $request)
    {
        $teamManager = FeedbackManagerAccess::leftJoin('users', 'users.id', 'feedback_manager_access.user_id')
            ->where(['club_id' => $this->userDetail->id])
            ->where('type', 2)
            ->select('feedback_manager_access.*', 'users.full_name as username')
            ->get();

        $selectedFixtureId = FeedbackManagerAccessFixture::leftJoin('feedback_manager_access', 'feedback_manager_access.id', '=', 'feedback_manager_access_fixtures.feedback_manager_access_id')
            ->where('feedback_manager_access.club_id', $this->userDetail->id)
            ->pluck('feedback_manager_access_fixtures.fixture_id', 'feedback_manager_access_fixtures.id')->all();

        $fixturData = Fixture::leftJoin('teams', 'teams.id', '=', 'fixtures.team')
            ->where('fixtures.status', '!=', 3)
            ->where('fixtures.start_date', '>', "2022-10-19")
            ->where('fixtures.club', $this->userDetail->id)
            ->whereNotIn('fixtures.id', $selectedFixtureId)
            ->select('fixtures.*', 'teams.name as team_name')
            ->get();

        $selectedIds = FeedbackManagerAccess::where('club_id', $this->userDetail->id)->pluck('user_id', 'user_id')->all();
        $userList = FeedbackCochAccess::leftJoin('users', 'users.id', 'feedback_coch_access.user_id')
            ->where('feedback_coch_access.club_id', $this->userDetail->id)
            ->whereNotIn('feedback_coch_access.user_id', $selectedIds)
            ->select('users.full_name', 'feedback_coch_access.user_id')->get();

        $data['status'] = 200;
        $data['data'] = $teamManager;
        $data['fixture_list'] = $fixturData;
        $data['user_list'] = $userList;
        return response()->json($data);
    }
    public function saveManageAcessByFixture(Request $request)
    {
        $obj = new FeedbackManagerAccess;
        $obj->club_id = $this->userDetail->id;
        $obj->user_id = $request->user;
        $obj->type = 2;
        if ($obj->save()) {
            foreach ($request->team as $key => $value) {
                $objTeam = new FeedbackManagerAccessFixture;
                $objTeam->feedback_manager_access_id  = $obj->id;
                $objTeam->fixture_id  = $value;
                $objTeam->save();
            }
        }

        $selectedIds = FeedbackManagerAccess::where('club_id', $this->userDetail->id)->pluck('user_id', 'user_id')->all();
        $userList = FeedbackCochAccess::leftJoin('users', 'users.id', 'feedback_coch_access.user_id')
            ->where('feedback_coch_access.club_id', $this->userDetail->id)
            ->whereNotIn('feedback_coch_access.user_id', $selectedIds)
            ->select('users.full_name', 'feedback_coch_access.user_id')->get();

        $teamManager = FeedbackManagerAccess::leftJoin('users', 'users.id', 'feedback_manager_access.user_id')
            ->where(['club_id' => $this->userDetail->id])
            ->select('feedback_manager_access.*', 'users.full_name as username')
            ->get();



        $selectedFixtureId = FeedbackManagerAccessFixture::leftJoin('feedback_manager_access', 'feedback_manager_access.id', '=', 'feedback_manager_access_fixtures.feedback_manager_access_id')
            ->where('feedback_manager_access.club_id', $this->userDetail->id)
            ->pluck('feedback_manager_access_fixtures.fixture_id', 'feedback_manager_access_fixtures.id')->all();

        $fixturData = Fixture::leftJoin('teams', 'teams.id', '=', 'fixtures.team')
            ->where('fixtures.status', '!=', 3)
            ->where('fixtures.start_date', '>', "2022-10-19")
            ->where('fixtures.club', $this->userDetail->id)
            ->whereNotIn('fixtures.id', $selectedFixtureId)
            ->select('fixtures.*', 'teams.name as team_name')
            ->get();

        $data['status'] = 200;
        $data['data'] = $teamManager;
        $data['user_list'] = $userList;
        $data['fixture_list'] = $fixturData;
        $data['message'] = "Manage Access by Team has been added successfully.";
        return response()->json($data);
    }

    function showFixtureListing(Request $request)
    {
        $id = $request->id;

        $fixturDataTemp = FeedbackManagerAccessFixture::leftJoin('fixtures', 'fixtures.id', '=', 'feedback_manager_access_fixtures.fixture_id')->leftJoin('teams', 'teams.id', '=', 'fixtures.team')
            ->where('feedback_manager_access_fixtures.feedback_manager_access_id', $id)
            ->select('feedback_manager_access_fixtures.*', 'teams.name as team_name', 'fixtures.start_date', 'fixtures.end_date')
            ->get();

        $data['status'] = 200;
        $data['data'] = $fixturDataTemp;
        return response()->json($data);
    }
    function showTeamListing(Request $request)
    {
        $teamData = FeedbackManagerAccessTeam::leftJoin('teams', 'teams.id', '=', 'feedback_manager_access_team.team_id')
            ->where('feedback_manager_access_team.feedback_manager_access_id', $request->id)
            ->select('feedback_manager_access_team.*', 'teams.name as team_name')
            ->get();

        $data['status'] = 200;
        $data['data'] = $teamData;
        return response()->json($data);
    }
    function deleteManagerFixture(Request $request)
    {
        $managerAccessid = $request->manager_access_id;
        FeedbackManagerAccessFixture::where('id', $request->id)->delete();
        $isManagerAccessIdExist = FeedbackManagerAccessFixture::where('feedback_manager_access_id', $managerAccessid)->value('id');
        if (empty($isManagerAccessIdExist)) {
            FeedbackManagerAccess::where('id', $managerAccessid)->delete();
        }
        $data['status'] = 200;
        $data['message'] = "Fixture deleted successfully.";
        return response()->json($data);
    }

    function deleteManagerTeam(Request $request)
    {
        $managerAccessid = $request->manager_access_id;
        FeedbackManagerAccessTeam::where('id', $request->id)->delete();
        $isManagerAccessIdExist = FeedbackManagerAccessTeam::where('feedback_manager_access_id', $managerAccessid)->value('id');
        if (empty($isManagerAccessIdExist)) {
            FeedbackManagerAccess::where('id', $request->managerAccessid)->delete();
        }
        $data['status'] = 200;
        $data['message'] = "Team deleted successfully.";
        return response()->json($data);
    }
    function deleteManagerAccess(Request $request)
    {
        FeedbackManagerAccess::where('id', $request->id)->delete();
        $data['status'] = 200;
        $data['message'] = "Fixture deleted successfully.";
        return response()->json($data);
    }
    function feedbackPointSystem(Request $request)
    {
        $getFeedbackStars = array(1 => '1 Star', 2 => '2 Star', 3 => '3 Star', 4 => '4 Star', 5 => '5 Star', 6 => '6 Star', 7 => '7 Star', 8 => '8 Star', 9 => '9 Star', 10    => '10 Star');
        $pointDetails = FeedbackPointSystem::where('club_id', $this->userDetail->id)->exists();
        if (!$pointDetails) {
            foreach ($getFeedbackStars as $key => $value) {
                $obj = new FeedbackPointSystem;
                $obj->name = $key;
                $obj->description = "";
                $obj->points = 0.00;
                $obj->club_id = $this->userDetail->id;
                $obj->save();
            }
        }
        $pointDetails = FeedbackPointSystem::where('club_id', $this->userDetail->id)->get();
        $data['status'] = 200;
        $data['data'] = $pointDetails;
        return response()->json($data);
    }
    function saveFeedbackPointSystem(Request $request)
    {
        foreach ($request->data as $key => $value) {
            $obj = FeedbackPointSystem::find($value['id']);
            $obj->name = $value['name'];
            $obj->points = $value['points'];
            $obj->description = $value['description'];
            $obj->save();
        }
        $pointDetails = FeedbackPointSystem::where('club_id', $this->userDetail->id)->get();
        $data['status'] = 200;
        $data['data'] = $pointDetails;
        $data['message'] = "Feedback Point system updated successfully.";
        return response()->json($data);
    }

    function displaySetting()
    {
        $details = DisplayView::where('club_id', $this->userDetail->id)->first();
        $data['status'] = 200;
        $data['data'] = $details;
        return response()->json($data);
    }
    function updateDisplaySetting(Request $request)
    {
        $id = DisplayView::where('club_id', $this->userDetail->id)->orderby('id', 'desc')->value('id');
        if (!empty($id)) {
            DisplayView::where('club_id', $this->userDetail->id)->update(['view' => $request->view]);
        } else {
            $obj =  new DisplayView();
            $obj->view = $request->view;
            $obj->save();
        }
        $data['status'] = 200;
        $data['message'] = "Display setting updated successfully.";
        return response()->json($data);
    }
}

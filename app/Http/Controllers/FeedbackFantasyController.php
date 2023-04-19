<?php

namespace App\Http\Controllers;

use App\Model\FeedbackCategory;
use App\Model\FeedbackCochAccess;
use App\Model\UserTeams;
use App\Model\FeedbackFixtureCoch;
use App\Model\FeedbackManagerAccess;
use App\Model\FeedbackCoachAccessTeam;
use App\Model\FeedbackCoachAccessFixture;
use App\Model\FeedbackManagerAccessTeam;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


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
            ->select('feedback_manager_access.*', 'users.full_name as username')
            ->get();

        $teamLists = \App\Model\Team::where('club', $this->userDetail->id)->select('name', 'id')->get();
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
            ->select('feedback_manager_access.*', 'users.full_name as username')
            ->get();
        $data['status'] = 200;
        $data['data'] = $teamManager;
        $data['user_list'] = $userList;
        $data['message'] = "Manage Access by Team has been added successfully.";
        return response()->json($data);
    }
}

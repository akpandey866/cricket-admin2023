<?php

namespace App\Http\Controllers;

use App\Model\User;
use App\Model\UserTeams;
use App\Model\DropDown;
use App\Model\GamePoint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;



class UserController extends Controller
{
    private $userDetail = null;
    public function __construct()
    {
        $this->userDetail = auth('api')->user();
    }
    public function index(Request $request)
    {

        $DB                     =     User::query();
        if (!empty($request->sn)) {
            $DB->where("users.sn", 'like', '%' . $request->sn . '%');
        }
        if (!empty($request->full_name)) {
            $DB->where("users.full_name", 'like', '%' . $request->full_name . '%');
        }
        if ($request->is_active == 0 || $request->is_active == 1) {
            $DB->where("users.is_active", 'like', '%' . $request->is_active . '%');
        }
        $sortBy = "users.created_at";
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

        $userList = UserTeams::where('club_id', $this->userDetail->id)
            ->where('user_teams.is_active', 1)
            ->pluck('user_id')
            ->all();

        $result = $DB
            ->whereIn('users.id', $userList)
            ->where('is_deleted', '<>', 1)
            ->orWhere('users.senior_club_name', $this->userDetail->id)
            ->orderBy($sortBy, $order)
            ->paginate($pageLimit);

        $data['success'] = true;
        $data['status'] = 200;
        $data['data'] = $result;
        $data['message'] = "Data fetched sucessfully.";
        return response()->json($data);
    }
    public function saveUser(Request $request)
    {

        $autoGenPass = substr(md5(microtime()), 0, 8);
        $first_name = $request->first_name;
        $last_name = $request->last_name;
        $fullName = $first_name . " " . $last_name;
        $obj = new User;
        $validateString = md5(time() . $request->email);
        $obj->validate_string = $validateString;
        $obj->first_name = $first_name;
        $obj->last_name = $last_name;
        $obj->full_name = $fullName;
        $obj->email = $request->email;
        $obj->slug = $this->getSlug($fullName, 'full_name', 'User');
        $obj->password = Hash::make($autoGenPass);
        $obj->senior_club_name = $this->userDetail->id;
        $obj->sport_name = "Cricket";
        $obj->user_role_id = 2;
        $obj->is_verified = 1;
        $obj->is_active = 1;
        $obj->referral_code = substr(base_convert(sha1(uniqid(mt_rand())), 16, 36), 0, 8);
        // $obj->address = !empty(Input::get('address')) ? Input::get('address') : '';
        // $obj->phone = !empty(Input::get('phone')) ? Input::get('phone') : '';
        // $obj->country = !empty(Input::get('country')) ? Input::get('country') : 0;
        // $obj->state = !empty(Input::get('state')) ? Input::get('state') : 0;
        // $obj->city = !empty(Input::get('city')) ? Input::get('city') : 0;
        // $obj->zipcode = !empty(Input::get('zipcode')) ? Input::get('zipcode') : 0;
        $obj->save();

        $userList = UserTeams::where('club_id', $this->userDetail->id)
            ->where('user_teams.is_active', 1)
            ->pluck('user_id')
            ->all();

        $result = User::whereIn('users.id', $userList)
            ->where('is_deleted', '<>', 1)
            ->orWhere('users.senior_club_name', $this->userDetail->id)
            ->orderBy("id", "desc")
            ->paginate(10);
        $data['success'] = true;
        $data['status'] = 200;
        $data['data'] = $result;
        $data['message'] = "User has been added successfully.";
        return response()->json($data);
    }
    public function deleteUser(Request $request)
    {
        User::where('id', $request->id)->delete();
        $userList = UserTeams::where('club_id', $this->userDetail->id)
            ->where('user_teams.is_active', 1)
            ->pluck('user_id')
            ->all();
        $result = User::whereIn('users.id', $userList)
            ->where('is_deleted', '<>', 1)
            ->orWhere('users.senior_club_name', $this->userDetail->id)
            ->orderBy("id", "desc")
            ->paginate(10);
        $data['status'] = 200;
        $data['data'] = $result;
        $data['message'] = "User has been deleted successfully.";
        return response()->json($data);
    }

    public function userDetail(Request $request)
    {
        $userDetails =    User::findOrFail($request->id);
        $data['status'] = 200;
        $data['data'] = $userDetails;
        return response()->json($data);
    }
    public function editUser(Request $request)
    {
        $obj =  User::findOrFail($request->userId);
        $obj->first_name    =  $request->first_name;
        $obj->last_name    =  $request->last_name;
        $obj->full_name = $request->first_name . " " . $request->last_name;
        $obj->email    =  $request->email;
        $obj->save();


        $userList = UserTeams::where('club_id', $this->userDetail->id)
            ->where('user_teams.is_active', 1)
            ->pluck('user_id')
            ->all();

        $result = User::whereIn('users.id', $userList)
            ->where('is_deleted', '<>', 1)
            ->orWhere('users.senior_club_name', $this->userDetail->id)
            ->orderBy("id", "desc")
            ->paginate(10);

        $data['status'] = 200;
        $data['data'] = $result;
        $data['message'] = "User has been updated successfully.";
        return response()->json($data);
    }
}

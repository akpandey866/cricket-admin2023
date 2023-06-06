<?php

namespace App\Http\Controllers;

use App\Model\User;
use App\Model\UserTeams;
use App\Model\EmailAction;
use App\Model\EmailTemplate;
use App\Model\PaidUser;

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
        $obj->phone = !empty($request->phone) ? $request->phone : '';
        $obj->referral_code = substr(base_convert(sha1(uniqid(mt_rand())), 16, 36), 0, 8);
        // $obj->address = !empty(Input::get('address')) ? Input::get('address') : '';
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
    public function sendCredential(Request $request)
    {
        $id = $request->id;
        $obj = User::find($id);
        $settingsEmail = "info@myclubtap.com";
        //$full_name		= 	$obj->full_name;
        $username = $obj->username;
        $email = $obj->email;
        $password = substr(uniqid(rand(10, 1000), false), rand(0, 10), 8);
        $obj->password = Hash::make($password);
        $obj->save();
        $route_url = URL::to('/');
        $click_link = $route_url;
        $emailActions = EmailAction::where('action', '=', 'send_login_credentials')->get()->toArray();
        $emailTemplates = EmailTemplate::where('action', '=', 'send_login_credentials')->get(array('name', 'subject', 'action', 'body'))->toArray();
        $cons = explode(',', $emailActions[0]['options']);
        $constants = array();
        foreach ($cons as $key => $val) {
            $constants[] = '{' . $val . '}';
        }
        $subject = $emailTemplates[0]['subject'];
        $rep_Array = array($username, $username, $password, $click_link, $route_url);
        $messageBody = str_replace($constants, $rep_Array, $emailTemplates[0]['body']);
        $mail = $this->sendMail($email, $username, $subject, $messageBody, $settingsEmail);
        Session::flash('flash_notice', trans("Login credientials send successfully"));
        return Redirect::back();
    }

    public function updatePaidStatus($roleId = 0, $userId = 0, $userStatus = 0)
    {
        if ($userStatus == 0) {
            $statusMessage = trans("Member marked as Unpaid");
        } else {
            $statusMessage = trans("Member marked as Paid");
        }
        User::where('id', $userId)->update(array('is_fund_paid' => $userStatus));
        $paidUser = PaidUser::firstOrNew(['user_id' => $userId, 'club_id' => $this->userDetail->id]);
        $paidUser->user_id = $userId;
        $paidUser->club_id = $this->userDetail->id;
        $paidUser->is_fund_paid = $userStatus;
        $paidUser->request = ($userStatus == 0) ? 0 : 2;
        $paidUser->save();

        $data['status'] = 200;
        $data['message'] = $statusMessage;
        return response()->json($data);
    }
    function paidUserListing(Request $request)
    {
        $paidUser = PaidUser::where(['club_id' => $this->userDetail->id, "request" => 1])->with('userData:id,full_name')->get();
        $data['status'] = 200;
        $data['data'] = $paidUser;
        return response()->json($data);
    }
}

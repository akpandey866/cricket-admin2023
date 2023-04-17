<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Model\User;
use App\Model\Country;
use App\Model\EmailAction;
use App\Model\UserFile;
use App\Model\State;
use App\Model\EmailTemplate;
use mjanssen\BreadcrumbsBundle\Breadcrumbs as Breadcrumb;
use Illuminate\Support\Facades\Input;
use Config, Cache, Cookie, DB, Hash, Mail, mongoDate, Redirect, Response, Session, URL, View, Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;


class ClubController extends Controller
{
    private $userDetail = null;
    public function __construct()
    {
        $this->userDetail = auth('api')->user();
    }
    public function getClubDetails(Request $request)
    {
        $authUser = auth('api')->user();
        $userDetails = User::where('id', $authUser->id)->first();
        $userFile = UserFile::where('user_id', $authUser->id)->first();
        $countryList = Country::orderBy('name', 'ASC')->select('name', 'id')->get();
        $stateList = \App\Model\State::where('country_id', $userDetails->country)->orderBy('name', 'ASC')->select('name', 'id')->get();


        // $state = !empty($userDetails->state) ? $userDetails->state : 0;
        // $city = !empty($userDetails->city) ? $userDetails->city : 0;
        // $country = !empty($userDetails->country) ? $userDetails->country : 0;

        $data['success'] = true;
        $data['status'] = 200;
        $data['user_details'] = $userDetails;
        $data['user_files'] = $userFile;
        $data['country_list'] = $countryList;
        $data['state_list'] = $stateList;
        return response()->json($data);
    }

    public function updateGameAdmin(Request $request)
    {

        $obj = User::find($request->user_id);
        $fullName = ucwords($request->first_name . ' ' . $request->last_name);
        $obj->first_name = $request->first_name;
        $obj->last_name = $request->last_name;
        $obj->full_name = $fullName;
        $obj->email = $request->email;
        $obj->dob = $request->dob;
        $obj->gender = $request->gender;
        // if (!empty($request->password)) {
        //     $obj->password = Hash::make($request->password);
        // }
        $obj->save();
        $data['success'] = true;
        $data['status'] = 200;
        $data['message'] = "Data updated successfully.";
        return response()->json($data);
    }

    public function updateGameSocial(Request $request)
    {

        $obj = User::find($this->userDetail->id);
        $obj->facebook = $request->facebook;
        $obj->twitter =  $request->twitter;
        $obj->instagram = $request->instagram;
        $obj->website = $request->website;
        $obj->save();
        $data['success'] = true;
        $data['status'] = 200;
        $data['message'] = "Data updated successfully.";
        return response()->json($data);
    }
    public function updateGameIntro(Request $request)
    {
        $obj = UserFile::where('user_id', $this->userDetail->id)->first();
        $obj->youtube_video1 = $request->youtube_video1;
        $obj->youtube_video2 =  $request->youtube_video2;
        $obj->youtube_video3 = $request->youtube_video3;
        $obj->youtube_video4 = $request->youtube_video4;

        if (!empty($request->video)  && $request->video != "undefined") {
            $extension = $request->video->getClientOriginalExtension();
            $newFolder = strtoupper(date('M') . date('Y')) . '/';
            $folderPath = base_path() . "/public/uploads/club/" . $newFolder;
            if (!File::exists($folderPath)) {
                File::makeDirectory($folderPath, $mode = 0777, true);
            }
            $uservideoName = time() . '-club.' . $extension;
            $video = $newFolder . $uservideoName;
            if ($request->video->move($folderPath, $uservideoName)) {
                $obj->video = $video;
            }
        }
        if (!empty($request->intro_image)  && $request->intro_image != "undefined") {
            $extension = $request->intro_image->getClientOriginalExtension();
            $newFolder = strtoupper(date('M') . date('Y')) . '/';
            $folderPath = base_path() . "/public/uploads/club/" . $newFolder;
            if (!File::exists($folderPath)) {
                File::makeDirectory($folderPath, $mode = 0777, true);
            }
            $userImageName = time() . '-intro_image.' . $extension;
            $image = $newFolder . $userImageName;
            if ($request->intro_image->move($folderPath, $userImageName)) {
                $obj->intro_image = $image;
            }
        }
        $obj->save();

        $data['success'] = true;
        $data['status'] = 200;
        $data['message'] = "Data updated successfully.";
        return response()->json($data);
    }

    public function updateAboutGame(Request $request)
    {
        $obj = User::find($this->userDetail->id);
        $obj->country = $request->country;
        $obj->state =  $request->state;
        $obj->city = $request->city;
        $obj->timezone = $request->timezone;
        $obj->post_code = $request->post_code;
        $obj->save();
        $data['success'] = true;
        $data['status'] = 200;
        $data['message'] = "Data updated successfully.";
        return response()->json($data);
    }
    public function updateFeeInfo(Request $request)
    {
        $obj = User::find($this->userDetail->id);
        $obj->entry_price = $request->entry_price;
        $obj->entry_fee_info =  $request->entry_fee_info;
        $obj->message = $request->message;

        $obj->save();
        $data['success'] = true;
        $data['status'] = 200;
        $data['message'] = "Data updated successfully.";
        return response()->json($data);
    }

    public function updateBasicSetting(Request $request)
    {
        $obj = User::find($this->userDetail->id);
        $obj->club_name = $request->club_name;
        $obj->game_name =  $request->game_name;
        $obj->username =  $request->username;


        if (!empty($request->image) && $request->image != "undefined" && $request->image != "null") {
            $extension             =    $request->image->getClientOriginalExtension();
            $newFolder             =     strtoupper(date('M') . date('Y')) . '/';
            $folderPath            =     base_path() . "/public/uploads/sponsor/" . $newFolder;
            if (!File::exists($folderPath)) {
                File::makeDirectory($folderPath, $mode = 0777, true);
            }
            $userImageName = time() . '-sponsor-image-main.' . $extension;
            $image = $newFolder . $userImageName;
            if ($request->image->move($folderPath, $userImageName)) {
                $obj->club_logo        =    $image;
            }
        }

        $obj->save();
        $data['success'] = true;
        $data['status'] = 200;
        $data['message'] = "Data updated successfully.";
        return response()->json($data);
    }


    public function getStateByCountryId(Request $request)
    {
        $states = State::where('country_id', $request->countryId)->select('name', 'id')->get();
        $data['success'] = true;
        $data['data'] = $states;
        $data['status'] = 200;
        return response()->json($data);
    }
}

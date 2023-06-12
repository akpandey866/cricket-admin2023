<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Model\Sponsor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class SponsorController extends Controller
{
    private $userDetail = null;
    public function __construct()
    {
        $this->userDetail = auth('api')->user();
    }
    public function index(Request $request)
    {

        if ($request->limit != "undefined") {
            $DB                     =     Sponsor::query();
            if (!empty($request->name)) {
                $DB->where("name", 'like', '%' . $request->name . '%');
            }
            if (!empty($request->grade)) {
                $DB->where("grade", 'like', '%' . $request->grade . '%');
            }
            if ($request->is_active == 0 || $request->is_active == 1) {
                $DB->where("is_active", 'like', '%' . $request->is_active . '%');
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
            $result =     $DB->where('user_id', $this->userDetail->id)
                ->orderBy($sortBy, $order)
                ->paginate($pageLimit);
        }

        $data['success'] = true;
        $data['status'] = 200;
        $data['data'] = $result;
        $data['message'] = "Data fetched sucessfully.";
        return response()->json($data);
    }
    public function saveSponsor(Request $request)
    {

        try {
            $obj =  new Sponsor;
            $obj->name    =  $request->name;
            $obj->website    =  $request->website;
            $obj->facebook    =  $request->facebook;
            $obj->twitter    =  $request->twitter;
            $obj->instagram    =  $request->instagram;
            $obj->about    =  $request->about;
            $obj->user_id    = $this->userDetail->id;
            if (!empty($request->image) && $request->image != "undefined" && $request->image != "null") {
                $extension             =    $request->image->getClientOriginalExtension();
                $newFolder             =     strtoupper(date('M') . date('Y')) . '/';
                $folderPath            =     base_path() . "/public/uploads/sponsor/" . $newFolder;
                if (!File::exists($folderPath)) {
                    File::makeDirectory($folderPath, $mode = 0777, true);
                }
                $userImageName = time() . '-sponsor-main.' . $extension;
                $image = $newFolder . $userImageName;
                if ($request->image->move($folderPath, $userImageName)) {
                    $obj->logo        =    $image;
                }
            }
            $obj->save();

            $result = Sponsor::where('user_id', $this->userDetail->id)->orderBy('created_at', 'DESC')->limit(10)->get();
            $data['success'] = true;
            $data['status'] = 200;
            $data['data'] = $result;
            $data['message'] = "Sponsor has been added successfully.";
            return response()->json($data);
        } catch (\Exception $e) {
            $data['success'] = false;
            $data['status'] = 501;
            $data['message'] = $e->getMessage();
        }
    }
    public function deleteSponsor(Request $request)
    {
        Sponsor::where('id', $request->id)->delete();
        $result = Sponsor::where('user_id', $this->userDetail->id)
            ->orderBy('created_at', 'DESC')
            ->paginate(10);
        $data['status'] = 200;
        $data['data'] = $result;
        $data['message'] = "Sponsor has been deleted successfully.";
        return response()->json($data);
    }

    public function sponsorDetail(Request $request)
    {
        $sponsorDetail =    Sponsor::findOrFail($request->id);
        $data['status'] = 200;
        $data['data'] = $sponsorDetail;
        return response()->json($data);
    }
    public function editSponsor(Request $request)
    {
        $obj =  Sponsor::findOrFail($request->sponsorId);
        $obj->name    =  $request->name;
        $obj->website    =  $request->website;
        $obj->facebook    =  $request->facebook;
        $obj->twitter    =  $request->twitter;
        $obj->instagram    =  $request->instagram;
        $obj->about    =  $request->about;
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
                $obj->logo        =    $image;
            }
        }
        $obj->save();
        $result = Sponsor::where('user_id', $this->userDetail->id)->orderBy('created_at', 'DESC')->paginate(10);
        $data['status'] = 200;
        $data['data'] = $result;
        $data['message'] = "Sponsor has been updated successfully.";
        return response()->json($data);
    }
    function updateFeatured($id = 0, $status = 0)
    {
        $checkFeaturedExists = Sponsor::where(['user_id' => $this->userDetail->id, 'is_featured' => 1])->exists();
        if (($checkFeaturedExists) && ($status == 1)) {
            $data['status'] = 401;
            $data['message'] = "Please remove the existing 'Featured' tag to mark another sponsor as 'Featured'.";
            return response()->json($data);
        }
        Sponsor::where('id', '=', $id)->update(array('is_featured' => $status));
        $msg = "";
        if ($status  == 1) {
            $msg = "Marked as Featured.";
        } else {
            $msg = "Featured mark removed.";
        }

        $data['status'] = 200;
        $data['message'] = $msg;
        return response()->json($data);
    }

    function updateStatus($id = 0, $status = 0)
    {
        Sponsor::where('id', '=', $id)->update(array('is_active' => $status));
        $msg = "";
        if ($status  == 1) {
            $msg = "Sponsor has been activated.";
        } else {
            $msg = "Sponsor has been deactivated.";
        }
        $data['status'] = 200;
        $data['message'] = $msg;
        return response()->json($data);
    }
    function imageUpload(Request $request)
    {
        if (!empty($request->image)) {
            $extension             =    $request->image->getClientOriginalExtension();
            $newFolder             =     strtoupper(date('M') . date('Y')) . '/';
            $folderPath            =     base_path() . "/public/uploads/quill-fileuploader/" . $newFolder;
            if (!File::exists($folderPath)) {
                File::makeDirectory($folderPath, $mode = 0777, true);
            }
            $userImageName = time() . '-quill-image.' . $extension;
            $image = $newFolder . $userImageName;
            $request->image->move($folderPath, $userImageName);
        }
        $data['status'] = 200;
        $data['message'] = "success";
        $data['url'] = env('APP_URL') . "/admin-cricket/public/uploads/quill-fileuploader/" . $image;
        return response()->json($data);
    }
}

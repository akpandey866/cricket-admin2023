<?php

namespace App\Http\Controllers;

use App\Model\GamePrize;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class GamePrizeController extends Controller
{
    private $userDetail = null;
    public function __construct()
    {
        $this->userDetail = auth('api')->user();
    }
    public function index(Request $request)
    {

        if ($request->limit != "undefined") {
            $DB                     =     GamePrize::query();
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
            $result =     $DB->where('club', $this->userDetail->id)
                ->orderBy($sortBy, $order)
                ->paginate($pageLimit);
        }

        $data['success'] = true;
        $data['status'] = 200;
        $data['data'] = $result;
        $data['message'] = "Data fetched sucessfully.";
        return response()->json($data);
    }
    public function savePrize(Request $request)
    {

        $obj =  new GamePrize;
        $obj->title    =  $request->name;
        $obj->description    =  $request->about;
        $obj->club    = $this->userDetail->id;
        if (!empty($request->image) && $request->image != "undefined" && $request->image != "null") {
            $extension             =    $request->image->getClientOriginalExtension();
            $newFolder             =     strtoupper(date('M') . date('Y')) . '/';
            $folderPath            =     base_path() . "/public/uploads/gameprize/" . $newFolder;
            if (!File::exists($folderPath)) {
                File::makeDirectory($folderPath, $mode = 0777, true);
            }
            $userImageName = time() . '-game-prize-main.' . $extension;
            $image = $newFolder . $userImageName;
            if ($request->image->move($folderPath, $userImageName)) {
                $obj->image        =    $image;
            }
        }
        $obj->save();

        $result = GamePrize::where('club', $this->userDetail->id)->orderBy('created_at', 'DESC')->limit(10)->get();
        $data['success'] = true;
        $data['status'] = 200;
        $data['data'] = $result;
        $data['message'] = "Game Prize has been added successfully.";
        return response()->json($data);
    }
    public function deletePrize(Request $request)
    {
        GamePrize::where('id', $request->id)->delete();
        $result = GamePrize::where('club', $this->userDetail->id)
            ->orderBy('created_at', 'DESC')
            ->paginate(10);
        $data['status'] = 200;
        $data['data'] = $result;
        $data['message'] = "Prize has been deleted successfully.";
        return response()->json($data);
    }

    public function prizeDetail(Request $request)
    {
        $prizeDetail =    GamePrize::findOrFail($request->id);
        $data['status'] = 200;
        $data['data'] = $prizeDetail;
        return response()->json($data);
    }
    public function editPrize(Request $request)
    {
        $obj =  GamePrize::findOrFail($request->prizeId);
        $obj->title    =  $request->name;
        $obj->description    =  $request->about;
        if (!empty($request->image) && $request->image != "undefined" && $request->image != "null") {
            $extension             =    $request->image->getClientOriginalExtension();
            $newFolder             =     strtoupper(date('M') . date('Y')) . '/';
            $folderPath            =     base_path() . "/public/uploads/gameprize/" . $newFolder;
            if (!File::exists($folderPath)) {
                File::makeDirectory($folderPath, $mode = 0777, true);
            }
            $userImageName = time() . '-prize-image-main.' . $extension;
            $image = $newFolder . $userImageName;
            if ($request->image->move($folderPath, $userImageName)) {
                $obj->image        =    $image;
            }
        }
        $obj->save();
        $result = GamePrize::where('club', $this->userDetail->id)->orderBy('created_at', 'DESC')->paginate(10);
        $data['status'] = 200;
        $data['data'] = $result;
        $data['message'] = "Prize has been updated successfully.";
        return response()->json($data);
    }
    function updateFeatured($id = 0, $status = 0)
    {
        $checkFeaturedExists = GamePrize::where(['club' => $this->userDetail->id, 'is_featured' => 1])->exists();
        if (($checkFeaturedExists) && ($status == 1)) {
            $data['status'] = 401;
            $data['message'] = "Please remove the existing 'Featured' tag to mark another prize as 'Featured'.";
            return response()->json($data);
        }
        GamePrize::where('id', '=', $id)->update(array('is_featured' => $status));
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
        GamePrize::where('id', '=', $id)->update(array('is_active' => $status));
        $msg = "";
        if ($status  == 1) {
            $msg = "Prize has been activated.";
        } else {
            $msg = "Prize has been deactivated.";
        }
        $data['status'] = 200;
        $data['message'] = $msg;
        return response()->json($data);
    }
}

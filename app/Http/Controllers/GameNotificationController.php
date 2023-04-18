<?php

namespace App\Http\Controllers;

use App\Model\GameNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class GameNotificationController extends Controller
{
    private $userDetail = null;
    public function __construct()
    {
        $this->userDetail = auth('api')->user();
    }
    public function index(Request $request)
    {

        $DB = GameNotification::query();

        if (!empty($request->title)) {
            $DB->where("game_notifications.title", 'like', '%' . $request->title . '%');
        }

        $sortBy = "game_notifications.created_at";
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
    public function saveNotification(Request $request)
    {
        $obj =  new GameNotification;
        $obj->title     =  $request->title;
        $obj->message = $request->message;
        $obj->club_id =  $this->userDetail->id;
        $obj->save();


        $result =     GameNotification::where('club_id', $this->userDetail->id)
            ->orderBy("game_notifications.created_at", "DESC")
            ->paginate(10);

        $data['success'] = true;
        $data['status'] = 200;
        $data['data'] = $result;
        $data['message'] = "Notification has been added successfully.";
        return response()->json($data);
    }
    public function deleteNotification(Request $request)
    {
        GameNotification::where('id', $request->id)->delete();
        $result =     GameNotification::where('club_id', $this->userDetail->id)
            ->orderBy("game_notifications.created_at", "DESC")
            ->paginate(10);
        $data['status'] = 200;
        $data['data'] = $result;
        $data['message'] = "Notification has been deleted successfully.";
        return response()->json($data);
    }

    public function notificationDetail(Request $request)
    {

        $result =    GameNotification::findOrFail($request->id);
        $data['status'] = 200;
        $data['data'] = $result;
        return response()->json($data);
    }
    public function editNotification(Request $request)
    {
        $obj =  GameNotification::findOrFail($request->notificationId);
        $obj->title     =  $request->title;
        $obj->message = $request->message;
        $obj->save();

        $result =     GameNotification::where('club_id', $this->userDetail->id)
            ->orderBy("game_notifications.created_at", "DESC")
            ->paginate(10);

        $data['status'] = 200;
        $data['data'] = $result;
        $data['message'] = "Notification has been updated successfully.";
        return response()->json($data);
    }
}

<?php

namespace App\Http\Controllers;

use App\Model\FeedbackCategory;
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
}

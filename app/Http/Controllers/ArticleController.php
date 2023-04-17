<?php

namespace App\Http\Controllers;

use App\Model\Article;
use App\Model\DropDown;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;



class ArticleController extends Controller
{
    private $userDetail = null;
    public function __construct()
    {
        $this->userDetail = auth('api')->user();
    }
    public function index(Request $request)
    {

        if ($request->limit != "undefined") {
            $DB                     =     Article::query();
            if (!empty($request->title)) {
                $DB->where("articles.title", 'like', '%' . $request->title . '%');
            }
            if (!empty($request->link)) {
                $DB->where("articles.link", 'like', '%' . $request->link . '%');
            }
            if ($request->is_active == 0 || $request->is_active == 1) {
                $DB->where("articles.is_active", 'like', '%' . $request->is_active . '%');
            }
            $sortBy = "articles.created_at";
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
            $result =     $DB->where('club_id', $this->userDetail->id)
                ->orderBy($sortBy, $order)
                ->paginate($pageLimit);
        }

        $data['success'] = true;
        $data['status'] = 200;
        $data['data'] = $result;
        $data['message'] = "Data fetched sucessfully.";
        return response()->json($data);
    }
    public function saveArticle(Request $request)
    {

        $obj =  new Article;
        $obj->title    =  $request->title;
        $obj->date    =  $request->date;
        $obj->link    =  !empty($request->external_link) ? $request->external_link : '';
        $obj->description    =  !empty($request->description) ? $request->description : '';
        $obj->slug =  $this->getSlug($request->title, 'title', 'Article');;
        $obj->club_id    =  $this->userDetail->id;


        if (!empty($request->image) && $request->image != "undefined" && $request->image != "null") {
            $extension             =    $request->image->getClientOriginalExtension();
            $newFolder             =     strtoupper(date('M') . date('Y')) . '/';
            $folderPath            =     base_path() . "/public/uploads/articles/" . $newFolder;
            if (!File::exists($folderPath)) {
                File::makeDirectory($folderPath, $mode = 0777, true);
            }
            $userImageName = time() . '-articles-main.' . $extension;
            $image = $newFolder . $userImageName;
            if ($request->image->move($folderPath, $userImageName)) {
                $obj->image        =    $image;
            }
        }
        if (!empty($request->thumb_image)  && $request->thumb_image != "undefined" && $request->thumb_image != "null") {
            $extension             =    $request->thumb_image->getClientOriginalExtension();
            $newFolder             =     strtoupper(date('M') . date('Y')) . '/';
            $folderPath            =    base_path() . "/public/uploads/articles/" . $newFolder;
            if (!File::exists($folderPath)) {
                File::makeDirectory($folderPath, $mode = 0777, true);
            }
            $userImageName = time() . '-articles-thumb.' . $extension;
            $image = $newFolder . $userImageName;
            if ($request->thumb_image->move($folderPath, $userImageName)) {
                $obj->thumb_image        =    $image;
            }
        }

        $obj->save();

        $data['success'] = true;
        $data['status'] = 200;
        $data['message'] = "Article has been added successfully.";
        return response()->json($data);
    }
    public function getSlug($title, $fieldName, $modelName, $limit = 100)
    {
        $slug             =      substr(Str::slug($title), 0, $limit);
        $Model            =     "\App\Model\\$modelName";
        $slugCount         =    $Model::where($fieldName, $title)->count();
        if ($slugCount == 0) {
            $slug         =      substr(Str::slug($title), 0, $limit);
        } else {
            $slug         =      $slug . "-" . $slugCount;
        }
        return $slug;
    } //end getSlug()
    public function deleteArticle(Request $request)
    {
        try {
            Article::where('id', $request->id)->delete();
            $data['status'] = 200;
            // $data['data'] = $result;
            $data['message'] = "Article has been deleted successfully.";
            return response()->json($data);
        } catch (\Exception $e) {
            $data['success'] = false;
            $data['status'] = 501;
            $data['message'] = $e->getMessage();
        }
    }

    public function articleDetail(Request $request)
    {
        $articleDetails =    Article::findOrFail($request->id);
        $data['status'] = 200;
        $data['data'] = $articleDetails;
        return response()->json($data);
    }
    public function editArticle(Request $request)
    {
        $obj =  Article::findOrFail($request->articleId);
        $obj->title    =  $request->title;
        $obj->date    =  $request->date;
        $obj->link    =  !empty($request->external_link) ? $request->external_link : '';
        $obj->description    =  !empty($request->description) ? $request->description : '';
        $obj->slug =  $this->getSlug($request->title, 'title', 'Article');

        if (!empty($request->image)  && $request->image != "undefined" && $request->image != "null") {
            $extension             =    $request->image->getClientOriginalExtension();
            $newFolder             =     strtoupper(date('M') . date('Y')) . '/';
            $folderPath            =     base_path() . "/public/uploads/articles/" . $newFolder;
            if (!File::exists($folderPath)) {
                File::makeDirectory($folderPath, $mode = 0777, true);
            }
            $userImageName = time() . '-articles-main.' . $extension;
            $image = $newFolder . $userImageName;
            if ($request->image->move($folderPath, $userImageName)) {
                $obj->image        =    $image;
            }
        }
        if (!empty($request->thumb_image)  && $request->thumb_image != "undefined" && $request->thumb_image != "null") {
            $extension             =    $request->thumb_image->getClientOriginalExtension();
            $newFolder             =     strtoupper(date('M') . date('Y')) . '/';
            $folderPath            =    base_path() . "/public/uploads/articles/" . $newFolder;
            if (!File::exists($folderPath)) {
                File::makeDirectory($folderPath, $mode = 0777, true);
            }
            $userImageName = time() . '-articles-thumb.' . $extension;
            $image = $newFolder . $userImageName;
            if ($request->thumb_image->move($folderPath, $userImageName)) {
                $obj->thumb_image        =    $image;
            }
        }

        $obj->save();
        $data['status'] = 200;
        $data['message'] = "Article has been updated successfully.";
        return response()->json($data);
    }

    public function articleData(Request $request)
    {
        $drop_down = new DropDown();
        $matchTypeList = $drop_down->get_master_list("matchtype");
        $data['status'] = 200;
        //$data['data'] = $gradeName;
        $data['matchTypeList'] = $matchTypeList;
        return response()->json($data);
    }
}

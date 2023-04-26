<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class Controller extends BaseController
{
    //
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
    public function imageDelete($path = null)
    {
        //$image_path = public_path('products/images/' . $path);
        if (File::exists($path)) {
            File::delete($path);
        }
    }
}

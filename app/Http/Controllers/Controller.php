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

    public function sendMail($to, $fullName, $subject, $messageBody, $from = '', $files = false, $path = '', $attachmentName = '')
    {
        $data                =    array();
        $data['to']            =    $to;
        $data['from']        =    (!empty($from) ? $from : Config::get("Site.email"));
        $data['fullName']    =    $fullName;
        $data['subject']    =    $subject;
        $data['filepath']    =    $path;
        $data['attachmentName']    =    $attachmentName;
        if ($files === false) {
            Mail::send('emails.template', array('messageBody' => $messageBody), function ($message) use ($data) {
                $message->to($data['to'], $data['fullName'])->from($data['from'])->subject($data['subject']);
            });
        } else {
            if ($attachmentName != '') {
                Mail::send('emails.template', array('messageBody' => $messageBody), function ($message) use ($data) {
                    $message->to($data['to'], $data['fullName'])->from($data['from'])->subject($data['subject'])->attach($data['filepath'], array('as' => $data['attachmentName']));
                });
            } else {
                Mail::send('emails.template', array('messageBody' => $messageBody), function ($message) use ($data) {
                    $message->to($data['to'], $data['fullName'])->from($data['from'])->subject($data['subject'])->attach($data['filepath']);
                });
            }
        }
        DB::table('email_logs')->insert(
            array(
                'email_to'     => $data['to'],
                'email_from' => $data['from'],
                'subject'     => $data['subject'],
                'message'     =>    $messageBody,
                'created_at' => DB::raw('NOW()')
            )
        );
    }
}

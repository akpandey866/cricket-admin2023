<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Model\User;
use App\Model\EmailAction;
use App\Model\EmailTemplate;
use mjanssen\BreadcrumbsBundle\Breadcrumbs as Breadcrumb;
use Illuminate\Support\Facades\Input;
use Config, Cache, Cookie, DB, File, Hash, Mail, mongoDate, Redirect, Response, Session, URL, View, Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * AdminLogin Controller
 *
 * Add your methods in the class below
 *
 * This file will render views\admin\login
 */
class AdminLoginController extends Controller
{
    /**
     * Function for display admin  login page
     *
     * @param null
     *
     * @return view page.
     */
    public function login(Request $request)
    {

        $this->validate($request, [
            'email' => 'required|string',
            'password' => 'required|string',
        ]);

        $credentials = $request->only(['email', 'password']);
        $credentials['default_club_game'] = 1;
        if (!$token = Auth::attempt($credentials)) {
            return response()->json(['message' => 'username or password incorrect.'], 401);
        }

        return response()->json([
            'message' => 'Login Succesfully.',
            'user' => auth('api')->user(),
            'status' => 200,
            'token' => $token
        ]);
    } // end index()
    /**
     * Function for logout admin users
     *
     * @param null
     *
     * @return rerirect page.
     */
    public function logout()
    {
        Auth::guard('admin')->logout();
        Session::flash('flash_notice', 'You are now logged out!');
        return Redirect::to('/admin')->with('message', 'You are now logged out!');
    } //endLogout()
    /**
     * Function is used to display forget password page
     *
     * @param null
     *
     * @return view page.
     */
    public function forgetPassword()
    {
        return View::make('admin.login.forget_password');
    } // end forgetPassword()
    /**
     * Function is used for reset password
     *
     * @param $validate_string as validator string
     *
     * @return view page.
     */
    public function resetPassword($validate_string = null)
    {
        Input::replace($this->arrayStripTags(Input::all()));
        if ($validate_string != "" && $validate_string != null) {

            $userDetail    =    User::where('is_active', '1')->where('forgot_password_validate_string', $validate_string)->first();

            if (!empty($userDetail)) {
                return View::make('admin.login.reset_password', compact('validate_string'));
            } else {
                return Redirect::to('/')
                    ->with('error', trans('Sorry, you are using wrong link.'));
            }
        } else {
            return Redirect::to('/')->with('error', trans('Sorry, you are using wrong link.'));
        }
    } // end resetPassword()
    /**
     * Function is used to send email for forgot password process
     *
     * @param null
     *
     * @return url.
     */
    public function sendPassword()
    {
        Input::replace($this->arrayStripTags(Input::all()));
        $thisData                =    Input::all();
        Input::replace($this->arrayStripTags($thisData));
        $messages = array(
            'email.required'         => trans('The email field is required.'),
            'email.email'             => trans('The email must be a valid email address.'),
        );
        $validator = Validator::make(
            Input::all(),
            array(
                'email'             => 'required|email',
            ),
            $messages
        );
        if ($validator->fails()) {
            return Redirect::back()
                ->withErrors($validator)->withInput()->with(compact(''));
        } else {
            $email        =    Input::get('email');
            $adminIDs = [SUPER_ADMIN_ROLE_ID, SUBADMIN, CLUBUSER];
            $userDetail    =    User::where('email', $email)->whereIn('user_role_id', $adminIDs)->first();
            if (!empty($userDetail)) {
                if ($userDetail->is_active == 1) {
                    if ($userDetail->is_verified == 1) {
                        $forgot_password_validate_string    =     md5($userDetail->email);
                        User::where('email', $email)->update(array('forgot_password_validate_string' => $forgot_password_validate_string));

                        $settingsEmail         =  Config::get('Site.email');
                        $email                 =  $userDetail->email;
                        $username            =  $userDetail->username;
                        $full_name            =  $userDetail->full_name;
                        $route_url          =  URL::to('admin/reset_password/' . $forgot_password_validate_string);
                        $varify_link           =   $route_url;

                        $emailActions        =    EmailAction::where('action', '=', 'forgot_password')->get()->toArray();
                        $emailTemplates        =    EmailTemplate::where('action', '=', 'forgot_password')->get(array('name', 'subject', 'action', 'body'))->toArray();
                        $cons = explode(',', $emailActions[0]['options']);
                        $constants = array();

                        foreach ($cons as $key => $val) {
                            $constants[] = '{' . $val . '}';
                        }
                        $subject             =  $emailTemplates[0]['subject'];
                        $rep_Array             =  array($username, $varify_link, $route_url);
                        $messageBody        =  str_replace($constants, $rep_Array, $emailTemplates[0]['body']);

                        $this->sendMail($email, $full_name, $subject, $messageBody, $settingsEmail);
                        Session::flash('flash_notice', trans('An email has been sent to your inbox. To reset your password please follow the steps mentioned in the email.'));
                        return Redirect::to('/admin');
                    } else {
                        return Redirect::to('admin/forget_password')->with('error', trans('Your account has not been verified yet.'));
                    }
                } else {
                    return Redirect::to('admin/forget_password')->with('error', trans('Your account has been temporarily disabled. Please contact administrator to unlock.'));
                }
            } else {
                return Redirect::to('/admin')->with('error', trans('Your email is not registered with ' . Config::get("Site.title") . "."));
            }
        }
    } // sendPassword()


    /**
     * Function is used for Logged Out user
     *
     * @param null
     *
     * @return view page.
     */
    public function LoggedOut()
    {
        Auth::logout();
        Session::flash('flash_notice', 'You are now logged out!');
        return Redirect::to('/admin')->with('message', 'You are now logged out!');
    }
    //end LoggedOut()

}// end AdminLoginController

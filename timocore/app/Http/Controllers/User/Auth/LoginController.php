<?php

namespace App\Http\Controllers\User\Auth;

use App\Http\Controllers\Controller;
use App\Lib\Intended;
use App\Models\User;
use App\Models\UserLogin;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Status;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{

    use AuthenticatesUsers;

    public function __construct()
    {
        parent::__construct();
    }

    public function showLoginForm()
    {
        $pageTitle = "Login";
        Intended::identifyRoute();
        return view('Template::user.auth.login', compact('pageTitle'));
    }

    public function login(Request $request)
    {

        $this->validateLogin($request);

        if(!verifyCaptcha()){
            $notify[] = ['error','Invalid captcha provided'];
            return back()->withNotify($notify);
        }

        $user = User::where('email', $request->email)->first();

        if ($user && $user->status == Status::USER_PENDING) {
            $notify[] = ['error', 'Your account is not approved yet. Please wait for approval'];
            return back()->withNotify($notify)->withInput();
        }

        if ($user && $user->status == Status::USER_REJECTED) {
            $notify[] = ['error', 'Your account request has been rejected. Please contact your organization for assistance'];
            return back()->withNotify($notify)->withInput();
        }

        // If the class is using the ThrottlesLogins trait, we can automatically throttle
        // the login attempts for this application. We'll key this by the username and
        // the IP address of the client making these requests into this application.
        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }

        if ($this->attemptLogin($request)) {
            return $this->sendLoginResponse($request);
        }

        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.
        $this->incrementLoginAttempts($request);

        Intended::reAssignSession();

        return $this->sendFailedLoginResponse($request);
    }

    protected function validateLogin($request)
    {

        $validator = Validator::make($request->all(), [
            'email' => 'required|string',
            'password' => 'required|string',
        ]);
        if ($validator->fails()) {
            Intended::reAssignSession();
            $validator->validate();
        }

    }

    public function logout()
    {
        $this->guard()->logout();
        request()->session()->invalidate();

        $notify[] = ['success', 'You have been logged out.'];
        return to_route('user.login')->withNotify($notify);
    }


    public function authenticated(Request $request, $user)
    {
        $user->save();
        $ip = getRealIP();
        $exist = UserLogin::where('user_ip',$ip)->first();
        $userLogin = new UserLogin();
        if ($exist) {
            $userLogin->longitude =  $exist->longitude;
            $userLogin->latitude =  $exist->latitude;
            $userLogin->city =  $exist->city;
            $userLogin->country_code = $exist->country_code;
            $userLogin->country =  $exist->country;
        }else{
            $info = json_decode(json_encode(getIpInfo()), true);
            $userLogin->longitude =  isset($info['long']) ? implode(',',$info['long']) : '';
            $userLogin->latitude =  isset($info['lat']) ? implode(',',$info['lat']) : '';
            $userLogin->city =  isset($info['city']) ? implode(',',$info['city']) : '';
            $userLogin->country_code = isset($info['code']) ? implode(',',$info['code']) : '';
            $userLogin->country =  isset($info['country']) ? implode(',',$info['country']) : '';
        }

        $userAgent = osBrowser();
        $userLogin->user_id = $user->id;
        $userLogin->user_ip =  $ip;

        $userLogin->browser = isset($userAgent['browser']) ? $userAgent['browser'] : '';
        $userLogin->os = isset($userAgent['os_platform']) ? $userAgent['os_platform'] : '';
        $userLogin->save();

        $redirection = Intended::getRedirection();
        return $redirection ? $redirection : to_route('user.home');
    }


}

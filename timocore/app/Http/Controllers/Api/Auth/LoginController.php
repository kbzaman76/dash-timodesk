<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\UserLogin;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Laravel\Sanctum\PersonalAccessToken;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;


    /**
     * Create a new controller instance.
     *
     * @return void
     */


    public function __construct()
    {
        parent::__construct();
    }

    public function login(Request $request)
    {

        $validator = $this->validateLogin($request);

        if ($request->is_web) {
            if (!verifyCaptcha()) {
                $notify[] = 'Invalid captcha provided';
                return responseError('captcha_error', $notify);
            }
        }

        if ($validator->fails()) {
            return responseError('validation_error', $validator->errors());
        }

        $request->merge(['email' => $request->username]);
        $credentials = request(['email', 'password']);
        if (!Auth::attempt($credentials)) {
            $notify[] = 'Unauthorized user';
            return responseError('unauthorized_user', $notify);
        }

        $user = $request->user();

        if($user->status == 0) {
            $notify[] = 'Your account is banned';
            return responseError('banned', $notify);
        }

        $user->tokens()->where('name', 'auth_token')->delete();

        $tokenResult = $user->createToken('auth_token', ['user'])->plainTextToken;
        $this->authenticated($request, $user);
        $this->clearLoginAttempts($request);
        $notify[] = 'Login Successful';

        return responseSuccess('login_success', $notify, [
            'user' => auth()->user(),
            'access_token' => $tokenResult,
            'token_type' => 'Bearer'
        ]);
    }

    protected function validateLogin(Request $request)
    {
        $validationRule = [
            'username' => 'required|string',
            'password' => 'required|string',
        ];

        $validate = Validator::make($request->all(), $validationRule);
        return $validate;
    }

    public function logout()
    {
        auth()->user()->tokens()->delete();

        $notify[] = 'Logout Successful';
        return responseSuccess('logout_success', $notify);
    }

    public function authenticated(Request $request, $user)
    {
        $ip = getRealIP();
        $exist = UserLogin::where('user_ip', $ip)->first();
        $userLogin = new UserLogin();
        if ($exist) {
            $userLogin->longitude =  $exist->longitude;
            $userLogin->latitude =  $exist->latitude;
            $userLogin->city =  $exist->city;
            $userLogin->country_code = $exist->country_code;
            $userLogin->country =  $exist->country;
        } else {
            $info = json_decode(json_encode(getIpInfo()), true);
            $userLogin->longitude =  isset($info['long']) ? implode(',', $info['long']) : '';
            $userLogin->latitude =  isset($info['lat']) ? implode(',', $info['lat']) : '';
            $userLogin->city =  isset($info['city']) ? implode(',', $info['city']) : '';
            $userLogin->country_code = isset($info['code']) ? implode(',', $info['code']) : '';
            $userLogin->country =  isset($info['country']) ? implode(',', $info['country']) : '';
        }

        $userAgent = osBrowser();
        $userLogin->user_id = $user->id;
        $userLogin->user_ip =  $ip;

        $userLogin->browser = isset($userAgent['browser']) ? $userAgent['browser'] : '';
        $userLogin->os = isset($userAgent['os_platform']) ? $userAgent['os_platform'] : '';
        $userLogin->save();
    }

    public function checkToken(Request $request)
    {
        $validationRule = [
            'token' => 'required',
        ];

        $validator = Validator::make($request->all(), $validationRule);

        if ($validator->fails()) {
            return responseError('validation_error', $validator->errors());
        }

        $exists = PersonalAccessToken::where('token', hash('sha256', $request->token))->exists();
        if ($exists) {
            $notify[] = 'Token exists';
            return responseSuccess('token_exists', $notify);
        }
        $notify[] = 'Token doesn\'t exists';
        return responseError('token_not_exists', $notify);
    }
}

<?php

namespace App\Http\Controllers\User\Auth;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Models\MemberInvitation;
use App\Models\Organization;
use App\Models\User;
use App\Models\UserLogin;
use App\Models\UserNotification;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class MemberRegisterController extends Controller {

    use RegistersUsers;

    public function __construct() {
        parent::__construct();
    }

    public function showGeneralJoinForm($invitationCode) {
        $pageTitle    = "Join";
        $organization = Organization::where('invitation_code', $invitationCode)->active()->firstOrFail();
        $type         = "link";

        return view('Template::user.auth.join', compact('pageTitle', 'type', 'organization'));
    }

    public function showEmailJoinForm($invitationCode) {
        $pageTitle = "Join";

        $invitation = MemberInvitation::where('invitation_code', $invitationCode)->with('organization')->where('status', Status::NO)->firstOrFail();

        $type = "email";

        $organization = $invitation->organization;

        return view('Template::user.auth.join', compact('pageTitle', 'invitation', 'type', 'organization'));
    }

    protected function validator(array $data) {

        $passwordValidation = Password::min(6);

        if (gs('secure_password')) {
            $passwordValidation = $passwordValidation->mixedCase()->numbers()->symbols()->uncompromised();
        }

        $agree = 'nullable';

        $rules = [
            'type'     => 'required|in:email,link',
            'fullname' => 'required',
            'email'    => [
                'required',
                'string',
                'email',
                'unique:users,email',
                function ($attribute, $value, $fail) use ($data) {
                    if (($data['type'] ?? null) === 'email') {
                        $exists = \App\Models\MemberInvitation::where('email', $value)->exists();
                        if (!$exists) {
                            $fail('The provided email is not a valid invitation email.');
                        }
                    }
                },
            ],
            'password' => ['required', 'confirmed', $passwordValidation],
            'captcha'  => 'sometimes|required',
            'agree'    => $agree,
        ];

        if (($data['type'] ?? null) === 'email') {
            $rules['invitation_id'] = 'required|exists:member_invitations,id';
        } elseif (($data['type'] ?? null) === 'link') {
            $rules['invitation_code'] = 'required|exists:organizations,invitation_code';
        }

        $messages = [
            'type.required'            => 'Invalid request type.',
            'type.in'                  => 'Invalid request type.',
            'fullname.required'        => 'The first name field is required.',
            'invitation_id.required'   => 'The provided invitation is invalid.',
            'invitation_id.exists'     => 'The provided invitation is invalid.',
            'invitation_code.required' => 'The provided invitation link is invalid.',
            'invitation_code.exists'   => 'The provided invitation link is invalid.',
        ];

        $validate = Validator::make($data, $rules, $messages);

        return $validate;
    }

    public function register(Request $request) {
        $this->validator($request->all())->validate();

        $request->session()->regenerateToken();

        if (!verifyCaptcha()) {
            $notify[] = ['error', 'Invalid captcha provided'];
            return back()->withNotify($notify);
        }

        $role = Status::STAFF;

        if ($request->type == 'email') {
            $invitation = MemberInvitation::with(['organization' => function ($query) {
                $query->where('is_suspend', Status::NO);
            }])->where('status', Status::NO)->where('email', $request->email)->find($request->invitation_id);

            if (!$invitation) {
                $notify[] = ['error', 'Invitation not found'];
                return back()->withNotify($notify);
            }
            $organizationId = $invitation->organization_id;
            $role = $invitation->role;
        } else {
            $organization = Organization::where('invitation_code', $request->invitation_code)->active()->first();
            if (!$organization) {
                $notify[] = ['error', 'Invitation not found'];
                return back()->withNotify($notify);
            }
            $organizationId = $organization->id;
        }


        event(new Registered($user = $this->create($request->all(), $organizationId, $role)));

        // update email invitation
        if ($request->type == 'email') {
            $invitation->delete();
        } else {
            $organization = Organization::where('invitation_code', $request->invitation_code)->first();
            session()->put('organization', $organization);
            $notify[] = ['success', 'Your account has been created successfully.'];
            return to_route('user.join.team.confirm')->withNotify($notify);
        }

        $this->guard()->login($user);

        return $this->registered($request, $user)
        ?: redirect($this->redirectPath());
    }

    protected function create(array $data, $organizationId, $role) {

        //User Create
        $user                   = new User();
        $user->email            = strtolower($data['email']);
        $user->fullname         = $data['fullname'];
        $user->password         = Hash::make($data['password']);
        $user->ev               = gs('ev') ? Status::NO : Status::YES;
        $user->has_organization = Status::NO;
        $user->organization_id  = $organizationId;
        $user->role             = $role;
        if ($data['type'] == 'link') {
            $user->status = Status::USER_PENDING;
        } else {
            $user->ev          = Status::YES;
        }
        $user->tracking_status  =  $user->ev ? Status::YES : Status::NO;

        $user->uid              = getUid();
        $user->save();

        $userNotification            = new UserNotification();
        $userNotification->user_id   = $user->organization->user_id;
        $userNotification->sender_id   = $user->id;
        $userNotification->title     = 'New member joined';
        $userNotification->click_url = urlPath('user.member.details', $user->uid);
        $userNotification->save();

        if ($data['type'] == 'email') {
            //Login Log Create
            $ip        = getRealIP();
            $exist     = UserLogin::where('user_ip', $ip)->first();
            $userLogin = new UserLogin();

            if ($exist) {
                $userLogin->longitude    = $exist->longitude;
                $userLogin->latitude     = $exist->latitude;
                $userLogin->city         = $exist->city;
                $userLogin->country_code = $exist->country_code;
                $userLogin->country      = $exist->country;
            } else {
                $info                    = json_decode(json_encode(getIpInfo()), true);
                $userLogin->longitude    = isset($info['long']) ? implode(',', $info['long']) : '';
                $userLogin->latitude     = isset($info['lat']) ? implode(',', $info['lat']) : '';
                $userLogin->city         = isset($info['city']) ? implode(',', $info['city']) : '';
                $userLogin->country_code = isset($info['code']) ? implode(',', $info['code']) : '';
                $userLogin->country      = isset($info['country']) ? implode(',', $info['country']) : '';
            }

            $userAgent          = osBrowser();
            $userLogin->user_id = $user->id;
            $userLogin->user_ip = $ip;

            $userLogin->browser = isset($userAgent['browser']) ? $userAgent['browser'] : '';
            $userLogin->os      = isset($userAgent['os_platform']) ? $userAgent['os_platform'] : '';
            $userLogin->save();
        }

        return $user;
    }

    public function registered() {
        return to_route('user.home');
    }

    public function joinConfirm() {
        if (session()->has('organization')) {
            $organization = session()->get('organization');
        }else{
            abort(404);
        }

        $pageTitle = "Account Created";
        return  view('Template::user.auth.join_confirm', compact('pageTitle', 'organization'));
    }

}

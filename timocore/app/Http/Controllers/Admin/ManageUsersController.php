<?php
namespace App\Http\Controllers\Admin;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Lib\BillingManager;
use App\Lib\UserNotificationSender;
use App\Models\NotificationLog;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Rules\FileTypeValidate;

class ManageUsersController extends Controller
{

    public function allUsers($organizationId = null)
    {
        $pageTitle = 'All Users';
        $users = $this->userData(organizationId: $organizationId);
        return view('admin.users.list', compact('pageTitle', 'users'));
    }

    public function activeUsers($organizationId = null)
    {
        $pageTitle = 'Active Users';
        $users = $this->userData('active', $organizationId);
        return view('admin.users.list', compact('pageTitle', 'users'));
    }

    public function bannedUsers($organizationId = null)
    {
        $pageTitle = 'Banned Users';
        $users = $this->userData('banned', $organizationId);
        return view('admin.users.list', compact('pageTitle', 'users'));
    }

    public function emailUnverifiedUsers($organizationId = null)
    {
        $pageTitle = 'Email Unverified Users';
        $users = $this->userData('emailUnverified', $organizationId);
        return view('admin.users.list', compact('pageTitle', 'users'));
    }

    public function emailVerifiedUsers($organizationId = null)
    {
        $pageTitle = 'Email Verified Users';
        $users = $this->userData('emailVerified', $organizationId);
        return view('admin.users.list', compact('pageTitle', 'users'));
    }

    public function mobileVerifiedUsers($organizationId = null)
    {
        $pageTitle = 'Mobile Verified Users';
        $users = $this->userData('mobileVerified', $organizationId);
        return view('admin.users.list', compact('pageTitle', 'users'));
    }

    public function billingUsers($organizationId)
    {
        $pageTitle = 'Billing Users';
        $users = $this->userData('billing', $organizationId);
        return view('admin.users.list', compact('pageTitle', 'users'));
    }


    protected function userData($scope = null, $organizationId = null){
        if ($scope) {
            if($scope == 'billing'){
                $organization = Organization::findOrFail($organizationId);
                $userIds = BillingManager::billUserIds($organization);
                $users = User::whereIn('id', $userIds);
            } else {
                $users = User::$scope();
            }
        }else{
            $users = User::query();
        }

        if ($organizationId) {
            $users = $users->where('organization_id', $organizationId);
        }

        return $users->searchable(['fullname','email'])->orderBy('id','desc')->paginate(getPaginate());
    }


    public function detail($id)
    {
        $user = User::findOrFail($id);
        $pageTitle = 'User Detail - '.$user->fullname;

        $countries = json_decode(file_get_contents(resource_path('views/partials/country.json')));
        return view('admin.users.detail', compact('pageTitle', 'user','countries'));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'email' => 'required|email|string|max:40|unique:users,email,' . $user->id,
            'fullname' => 'required|string|max:255',
        ]);


        $user->fullname = $request->fullname;
        $user->email = $request->email;

        $user->ev = $request->ev ? Status::VERIFIED : Status::UNVERIFIED;
        $user->tracking_status = $request->tracking_status ? Status::YES : Status::NO;
        $user->save();

        $notify[] = ['success', 'User details updated successfully'];
        return back()->withNotify($notify);
    }

    public function login($id){
        Auth::loginUsingId($id);
        return to_route('user.home');
    }

    public function status(Request $request,$id)
    {
        $user = User::findOrFail($id);
        if ($user->status == Status::USER_ACTIVE) {
            $request->validate([
                'reason'=>'required|string|max:255'
            ]);
            $user->status = Status::USER_BAN;
            $user->ban_reason = $request->reason;
            $notify[] = ['success','User banned successfully'];
        }else{
            $user->status = Status::USER_ACTIVE;
            $user->ban_reason = null;
            $notify[] = ['success','User unbanned successfully'];
        }
        $user->save();
        return back()->withNotify($notify);

    }


    public function showNotificationSingleForm($id)
    {
        $user = User::findOrFail($id);
        if (!gs('en')) {
            $notify[] = ['warning','Notification options are disabled currently'];
            return to_route('admin.users.detail',$user->id)->withNotify($notify);
        }
        $pageTitle = 'Send Notification to ' . $user->fullname;
        return view('admin.users.notification_single', compact('pageTitle', 'user'));
    }

    public function sendNotificationSingle(Request $request, $id)
    {
        $request->validate([
            'message' => 'required',
            'via'     => 'required|in:email',
            'subject' => 'required_if:via,email',
            'image'   => ['nullable', 'image', new FileTypeValidate(['jpg', 'jpeg', 'png'])],
        ]);

        if (!gs('en')) {
            $notify[] = ['warning', 'Notification options are disabled currently'];
            return to_route('admin.dashboard')->withNotify($notify);
        }

        return (new UserNotificationSender())->notificationToSingle($request, $id);
    }

    public function showNotificationAllForm()
    {
        if (!gs('en')) {
            $notify[] = ['warning', 'Notification options are disabled currently'];
            return to_route('admin.dashboard')->withNotify($notify);
        }

        $notifyToUser = User::notifyToUser();
        $users        = User::active()->count();
        $pageTitle    = 'Notification to Verified Users';

        if (session()->has('SEND_NOTIFICATION') && !request()->email_sent) {
            session()->forget('SEND_NOTIFICATION');
        }

        return view('admin.users.notification_all', compact('pageTitle', 'users', 'notifyToUser'));
    }

    public function sendNotificationAll(Request $request)
    {
        $request->validate([
            'via'                          => 'required|in:email',
            'message'                      => 'required',
            'subject'                      => 'required_if:via,email',
            'start'                        => 'required|integer|gte:1',
            'batch'                        => 'required|integer|gte:1',
            'being_sent_to'                => 'required',
            'cooling_time'                 => 'required|integer|gte:1',
            'number_of_top_deposited_user' => 'required_if:being_sent_to,topDepositedUsers|integer|gte:0',
            'number_of_days'               => 'required_if:being_sent_to,notLoginUsers|integer|gte:0',
            'image'                        => ["nullable", 'image', new FileTypeValidate(['jpg', 'jpeg', 'png'])],
        ], [
            'number_of_days.required_if'               => "Number of days field is required",
            'number_of_top_deposited_user.required_if' => "Number of top deposited user field is required",
        ]);

        if (!gs('en')) {
            $notify[] = ['warning', 'Notification options are disabled currently'];
            return to_route('admin.dashboard')->withNotify($notify);
        }

        return (new UserNotificationSender())->notificationToAll($request);
    }

    public function countBySegment($methodName){
        return User::active()->$methodName()->count();
    }

    public function list()
    {
        $query = User::active();

        if (request()->search) {
            $query->where(function ($q) {
                $q->where('email', 'like', '%' . request()->search . '%')->orWhere('fullname', 'like', '%' . request()->search . '%');
            });
        }
        $users = $query->orderBy('id', 'desc')->paginate(getPaginate());
        return response()->json([
            'success' => true,
            'users'   => $users,
            'more'    => $users->hasMorePages()
        ]);
    }

    public function notificationLog($id = 0, $type = null){
        $user = null;
        $pageTitle = 'Notifications Sent';
        $logQuery = NotificationLog::searchable(['user:email', 'user:fullname'])->dateFilter();
        if($id && $type == 'organization') {
            $organization = Organization::findOrFail($id);
            $pageTitle = 'Notifications Sent to '.$organization->name;

            $logQuery->filter(['user:organization_id']);
        } elseif($id) {
            $user = User::findOrFail($id);
            $pageTitle = 'Notifications Sent to '.$user->fullname;

            $logQuery->where('user_id',$id);
        }
        $logs = $logQuery->with('user')->orderBy('id','desc')->paginate(getPaginate());
        return view('admin.reports.notification_history', compact('pageTitle','logs','user'));
    }

}

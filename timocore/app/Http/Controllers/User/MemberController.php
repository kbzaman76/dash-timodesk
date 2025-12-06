<?php

namespace App\Http\Controllers\User;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Models\MemberInvitation;
use App\Models\Project;
use App\Models\Track;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class MemberController extends Controller
{
    public function memberList()
    {
        $pageTitle = 'Members';
        $members   = User::searchable(['fullname', 'email'])
            ->where('organization_id', organizationId())
            ->when(request('status') != "", function ($query) {
                $query->where('status', request('status'));
            })
            ->when(request('role') != "", function ($query) {
                $query->where('role', request('role'));
            })
            ->when(request('tracking_status') != "", function ($query) {
                $query->where('tracking_status', request('tracking_status'));
            })
            ->when(request('project') != "", function ($query) {
                $project = Project::where('uid', request('project'))->first();
                $query->whereHas('projects', function ($q) use ($project) {
                    $q->where('project_id', $project->id);
                });
            })
            ->orderBy('status','desc')
            ->orderBy('fullname')
            ->paginate(getPaginate());

        $projects            = auth()->user()->organization->projects;
        $organization        = auth()->user()->organization;
        $totalPendingMembers = MemberInvitation::where('organization_id', organizationId())->count();

        return view('Template::user.member.list', compact('pageTitle', 'members', 'organization', 'projects', 'totalPendingMembers'));
    }

    public function pendingMember()
    {
        $pageTitle           = 'Pending Invitations';
        $members             = MemberInvitation::where('organization_id', organizationId())->orderBy('id', 'desc')->paginate(getPaginate());
        $totalPendingMembers = $members->count();

        return view('Template::user.member.pending', compact('pageTitle', 'members', 'totalPendingMembers'));
    }

    public function deleteInvitation($id)
    {
        $member = MemberInvitation::where('organization_id', organizationId())->findOrFail($id);
        $member->delete();

        $notify[] = ['success', 'Invitation has been deleted'];
        return back()->withNotify($notify);
    }

    public function sendInvitation(Request $request)
    {
        $roles = implode(",", [Status::ORGANIZER, Status::MANAGER, Status::STAFF]);
        $request->validate([
            'email'   => 'required|array',
            'email.*' => [
                'required',
                'email',
                function ($attribute, $value, $fail) {
                    $existsInInvitations = MemberInvitation::where('email', $value)->exists();
                    $existsInUsers       = User::where('email', $value)->exists();

                    if ($existsInInvitations) {
                        $fail('This email address has already been invited.');
                    } elseif ($existsInUsers) {
                        $fail('This email address is already registered as a user.');
                    }
                },
            ],
            'role'    => 'required|array',
            'role.*'  => 'required|in:' . $roles,
        ], [
            'email.*.unique' => 'This email address is already registered.',
        ]);

        $organization = auth()->user()->organization;
        foreach ($request->email as $key => $email) {
            $invitation                  = new MemberInvitation();
            $invitation->email           = $email;
            $invitation->organization_id = $organization->id;
            $invitation->role            = $request->role[$key];
            $invitation->invitation_code = rand(100, 999) . strtolower(getTrx(40)) . rand(100, 999);
            $invitation->save();

            notify($invitation, 'REGISTRATION_INVITATION', [
                'organization_name' => $organization->name,
                'invitation_link'   => route('user.invitation.member.join', $invitation->invitation_code),
            ], ['email']);
        }

        $notify[] = ['success', 'Invitation sent successfully'];
        return to_route('user.member.pending')->withNotify($notify);
    }

    public function memberRegistration(Request $request)
    {
        $roles = implode(",", [Status::ORGANIZER, Status::MANAGER, Status::STAFF]);
        $request->validate([
            'fullname' => 'required|string',
            'email'    => 'required|string|email|unique:users',
            'role'     => 'required|in:' . $roles,
            'password' => ['required'],
        ], [], [
            'fullname' => 'name',
        ]);

        $organization = auth()->user()->organization;

        //User Create
        $user                   = new User();
        $user->email            = strtolower($request->email);
        $user->fullname         = $request->fullname;
        $user->password         = Hash::make($request->password);
        $user->ev               = gs('ev') ? Status::NO : Status::YES;
        $user->has_organization = Status::NO;
        $user->role             = $request->role;
        $user->organization_id  = $organization->id;
        $user->status           = Status::USER_ACTIVE;
        $user->tracking_status  = gs('ev') ? Status::NO : Status::YES;
        $user->uid              = getUid();
        $user->save();

        // send a verification linke
        $user->sendEmailVerificationLink();

        notify($user, 'MEMBER_REGISTRATION', [
            'organization_name' => $organization->name,
            'user_email'        => $user->email,
            'user_password'     => $request->password,
            'login_link'        => route('user.login'),
        ], ['email']);

        $notify[] = ['success', 'Member added successfully'];
        return back()->withNotify($notify);
    }

    public function generateInvitationLink()
    {
        $organization                  = auth()->user()->organization;
        $organization->invitation_code = strtolower(getTrx(35));
        $organization->save();

        return response()->json(['invitationLink' => route('user.invitation.join', $organization->invitation_code)]);
    }

    public function changeTrackingStatus(Request $request, $memberId)
    {
        $user = User::where('organization_id', organizationId())->where('uid',$memberId)->firstOrFail();

        if ($user->status == Status::USER_BAN) {
            return $this->respondTrackingStatus($request, false, "This user is banned and cannot perform this action.", $user);
        }
        if ($request->filled('tracking_status')) {
            $statusValue = (int) $request->input('tracking_status');

            if (!in_array($statusValue, [Status::YES, Status::NO], true)) {
                return $this->respondTrackingStatus($request, false, 'Invalid tracking status selected', $user);
            }

            if ($statusValue === Status::YES && ($user->status == Status::USER_PENDING || $user->ev == Status::NO)) {
                return $this->respondTrackingStatus($request, false, 'Email is not verified', $user);
            }

            $message = $statusValue === Status::YES ? 'Tracking enabled successfully' : 'Tracking disabled successfully';

            if ($user->tracking_status != $statusValue) {
                $user->tracking_status = $statusValue;
                $user->save();
            }

            return $this->respondTrackingStatus($request, true, $message, $user);
        }

        if ($user->tracking_status == Status::YES) {
            $user->tracking_status = Status::NO;
            $message               = 'Tracking disabled successfully';
        } else {
            if ($user->status == Status::USER_PENDING || $user->ev == Status::NO) {
                return $this->respondTrackingStatus($request, false, 'Email is not verified', $user);
            }
            $user->tracking_status = Status::YES;
            $message               = 'Tracking enabled successfully';
        }
        $user->save();

        return $this->respondTrackingStatus($request, true, $message, $user);
    }

    public function changeStatus(Request $request, $memberId)
    {
        $loggedInMember = auth()->user();
        if ($memberId == $loggedInMember->uid) {
            $message = 'You cannot disable yourself';
            return $this->statusChangeMessage($request, false, $message, $loggedInMember);
        }

        $user = User::where('organization_id', organizationId())->where('uid',$memberId)->firstOrFail();
        if (isEditDisabled($user)) {
            $message = 'You are not allowed to disable the member';
            return $this->statusChangeMessage($request, false, $message, $user);
        }

        $user->status = ($user->status == Status::USER_ACTIVE) ? Status::USER_BAN : Status::USER_ACTIVE;
        if ($user->status == Status::USER_BAN) {
            $user->tracking_status = Status::NO;
        }
        $user->save();

        $message = ($user->status == Status::USER_ACTIVE) ? "Member enabled successfully" : "Member disabled successfully";

        return $this->statusChangeMessage($request, true, $message, $user);
    }

    public function statusChangeMessage($request, $success, $message, $user)
    {
        if ($request->ajax()) {
            return response()->json([
                'success'       => $success,
                'member_status' => $user->status,
                'message'       => $message,
            ], $success ? 200 : 422);
        }

        $notify[] = [$success ? 'success' : 'error', $message];
        return back()->withNotify($notify);
    }

    public function approve($memberId)
    {
        $user                  = User::where('organization_id', organizationId())->where('uid',$memberId)->firstOrFail();
        $user->status          = Status::USER_ACTIVE;
        $user->tracking_status = Status::YES;
        $user->save();

        notify($user, 'MEMBER_APPROVE', [
            'member_name'       => toTitle($user->fullname),
            'organization_name' => $user->organization->name,
            'login_link'        => route('user.login'),
        ], ['email']);

        $notify[] = ['success', "User approved successfully"];
        return back()->withNotify($notify);
    }

    public function reject($memberId)
    {
        $user         = User::where('organization_id', organizationId())->where('status',Status::USER_PENDING)->where('uid',$memberId)->firstOrFail();
        $user->status = Status::USER_REJECTED;
        $user->email  = 'deleted-'.$user->email;
        $user->save();

        $user->delete();

        $notify[] = ['success', "User rejected successfully"];
        return back()->withNotify($notify);
    }

    public function details($uid)
    {
        $user      = User::withCount('projects', 'tasks')->where('organization_id', organizationId())->where('uid', $uid)->firstOrFail();
        $pageTitle = 'Information of ' . toTitle($user->fullname);
        $projects  = auth()->user()->organization->projects;
        $tracks    = Track::where('user_id', $user->id);

        $totalTracks = $tracks->clone()->sum('time_in_seconds');
        $tracksData  = $tracks->clone()->select(
            DB::raw('SUM(time_in_seconds) as total_seconds'),
            DB::raw('COUNT(DISTINCT DATE(started_at)) as total_days')
        )->first();

        $avgDailySeconds = $tracksData->total_days > 0 ? $tracksData->total_seconds / $tracksData->total_days : 0;

        return view('Template::user.member.details', compact('pageTitle', 'user', 'totalTracks', 'avgDailySeconds', 'projects'));
    }

    public function addProject(Request $request, $uid)
    {
        $user = User::where('organization_id', organizationId())->where('uid', $uid)->firstOrFail();

        $request->validate([
            'projects'   => 'required|array',
            'projects.*' => 'string',
        ]);

        $orgProjectIds = auth()->user()->organization->projects->pluck('uid')->toArray();
        $selectedIds   = array_values(array_intersect($orgProjectIds, $request->projects));
        $selectedIds   = array_map(function ($id) {
            return Project::where('uid', $id)->first()->id;
        }, $selectedIds);
      
        $user->projects()->syncWithoutDetaching($selectedIds);

        $notify[] = ['success', 'Project assigned successfully'];
        return back()->withNotify($notify);
    }

    //removeProject
    public function removeProject($uid, $projectUid)
    {
        $user = User::where('organization_id', organizationId())->where('uid', $uid)->firstOrFail();
        $project = Project::where('organization_id', organizationId())->where('uid', $projectUid)->firstOrFail();
        $user->projects()->detach($project->id);
        $notify[] = ['success', 'Project removed successfully'];
        return back()->withNotify($notify);
    }

    //updatePhone
    public function updatePhone(Request $request, $uid)
    {
        $request->validate([
            'phone' => 'required|string|max:40',
        ]);

        $user         = User::where('organization_id', organizationId())->where('uid', $uid)->firstOrFail();
        $user->mobile = $request->phone;
        $user->save();

        $notify[] = ['success', 'Phone number updated successfully'];
        return back()->withNotify($notify);
    }

    public function updateRole(Request $request, $uid)
    {
        if (auth()->user()->role == Status::ORGANIZER) {
            $rolesArray = [Status::ORGANIZER, Status::MANAGER, Status::STAFF];
        } else {
            $rolesArray = [Status::MANAGER, Status::STAFF];
        }

        $roles = implode(",", $rolesArray);

        $request->validate([
            'role' => 'required|in:' . $roles,
        ]);

        $user       = User::where('organization_id', organizationId())->where('uid', $uid)->firstOrFail();

        if(isEditDisabled($user)){
            $notify[] = ['error', 'You can not update your role'];
            return back()->withNotify($notify);
        }

        
        if($user->role < auth()->user()->role){
            abort(404);
        }

        $user->role = $request->role;
        $user->save();

        $notify[] = ['success', 'Phone number updated successfully'];
        return back()->withNotify($notify);
    }

    public function checkUser(Request $request)
    {
        $exist['data'] = false;
        $exist['type'] = null;
        if ($request->email) {
            $exist['data'] = User::where('email', $request->email)->exists();
            if (!$exist['data']) {
                $exist['data'] = MemberInvitation::where('email', $request->email)->exists();
            }
            $exist['type']  = 'email';
            $exist['field'] = 'Email';
        }
        return response($exist);
    }

    private function respondTrackingStatus(Request $request, bool $success, string $message, User $user)
    {
        if ($request->ajax()) {
            return response()->json([
                'success'         => $success,
                'tracking_status' => $user->tracking_status,
                'message'         => $message,
            ], $success ? 200 : 422);
        }

        $notify[] = [$success ? 'success' : 'error', $message];
        return goBack($notify);
    }
}

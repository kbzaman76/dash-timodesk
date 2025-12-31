<?php

namespace App\Http\Controllers\User;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Models\App;
use App\Models\Deposit;
use App\Models\Invoice;
use App\Models\OrganizationDiscount;
use App\Models\Project;
use App\Models\Track;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UserNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function home(Request $request)
    {
        $pageTitle = 'Dashboard';

        $authUser        = auth()->user();
        $organization = $authUser->organization;
        $unpaidInvoiceCount = $trialActive = $trialDaysLeft = $trialPercentLeft = NULL;
        $trialTotalDays = $billableStaffCount = $organizationDiscount = $totalBill = $discount = $afterDiscount = NULL;

        if(!$authUser->isStaff()) {
            // Unpaid invoice count for alert
            $unpaidInvoiceCount = Invoice::unpaid()
                ->where('organization_id', $organization->id)
                ->count();

            // Trial/billing info for Bill Details card
            $trialTotalDays   = Status::FREE_TRIAL_DURATION;
            $trialEnd         = $organization->trial_end_at ? Carbon::parse($organization->trial_end_at) : null;
            $trialActive      = $trialEnd && now()->lt($trialEnd);
            $trialDaysLeft    = 0;
            $trialPercentLeft = 0;
            if ($trialActive) {
                $trialDaysLeft    = max(0, number_format((float) now()->diffInDays($trialEnd)+1, 0));
                $trialPercentLeft = $trialTotalDays > 0 ? round(($trialDaysLeft / $trialTotalDays) * 100) : 0;
            }

            // Billable staff count (members with tracked activity in current billing window)
            $billingEnd         = $organization->next_invoice_date ? Carbon::parse($organization->next_invoice_date) : now();
            $billingStart       = (clone $billingEnd)->copy()->subMonth();
            $billableStaffCount = User::where('organization_id', $organization->id)
                ->whereHas('tracks', function ($q) use ($billingStart, $billingEnd) {
                    $q->whereBetween('ended_at', [$billingStart, $billingEnd]);
                })->count();

            $organizationDiscount = OrganizationDiscount::active()->where('organization_id', $organization->id)->latest()->first();

            $discount  = 0;
            $totalBill = gs('price_per_user') * $billableStaffCount;
            if ($organizationDiscount) {
                $discount = ($totalBill / $organizationDiscount->discount_percent);
            }
            $afterDiscount = $totalBill - $discount;
        }

        // Summary stats default to Today
        $summaryStart = orgNow()->startOfDay();
        $summaryEnd   = orgNow()->endOfDay();
        $summaryRange = $summaryStart->format('F d, Y') . ' - ' . $summaryEnd->format('F d, Y');
        $summaryLabel = 'Today';

        $startDate = orgNow()->subDays(30)->startOfDay();
        $endDate   = orgNow()->endOfDay();

        $defaultDateRange = $startDate->format('F d, Y') . ' - ' . $endDate->format('F d, Y');
        $defaultLabel     = 'Last 30 Days';

        $performerDateRange = $startDate->format('F d, Y') . ' - ' . $endDate->format('F d, Y');
        $performerLabel     = 'Last 30 Days';

        $onboardingSteps = [];

        $showOnboarding  = false;

        if(!session()->has('skip_onboarding')) {
            if ($authUser->isOrganizer() || $authUser->isManager()) {
                $onboardingSteps = $this->buildOrganizerOnboardingSteps($organization);
                $showOnboarding  = collect($onboardingSteps)->contains(fn ($step) => empty($step['completed']));
            } elseif ($authUser->isStaff()) {
                $onboardingSteps = $this->buildStaffOnboardingSteps($authUser);
                $showOnboarding  = collect($onboardingSteps)->contains(fn ($step) => empty($step['completed']));
            }
        }

        return view('Template::user.dashboard', compact(
            'pageTitle',
            'organization',
            'summaryRange',
            'summaryLabel',
            'defaultDateRange',
            'defaultLabel',
            'performerDateRange',
            'performerLabel',
            'unpaidInvoiceCount',
            'trialActive',
            'trialDaysLeft',
            'trialPercentLeft',
            'trialTotalDays',
            'billableStaffCount',
            'organizationDiscount',
            'totalBill',
            'discount',
            'afterDiscount',
            'showOnboarding',
            'onboardingSteps'
        ));
    }

    public function skipOnboarding()
    {
        session()->put('skip_onboarding', true);
        return to_route('user.home');
    }

    public function summaryStatistics(Request $request)
    {
        $organization          = auth()->user()->organization;
        [$startDate, $endDate] = $this->parseRangeOrMonth($request->input('date'));
        $stats                 = $this->buildSummaryStats($organization, $startDate, $endDate);
        return view('Template::user.partials.summary_statistics', ['widget' => $stats]);
    }

    public function projectTimings(Request $request)
    {
        $organization = auth()->user()->organization;
        [$startDate, $endDate] = $this->parseRangeOrMonth($request->input('date'));
        $projectTimings = $this->getProjectTimings($organization, $startDate, $endDate);

        return view('Template::user.partials.project_timing_list', compact('projectTimings'));
    }

    public function appUses(Request $request)
    {
        $range = $request->input('date');
        [$startDate, $endDate] = $this->parseRangeOrMonth($request->input('date'));

        $appUsages = App::where('org_id', organizationId())
            ->mine()
            ->whereBetweenOrg('started_at', $startDate, $endDate)
            ->selectRaw('app_name, sum(session_time) as totalSeconds')
            ->groupBy('app_name')
            ->orderBy('totalSeconds', 'DESC')
            ->get();

        return view('Template::user.partials.app_uses_list', compact('appUsages'));
    }

    private function buildOrganizerOnboardingSteps($organization): array
    {
        $user                   = auth()->user();
        $emailVerified          = (bool) $user->ev;
        $hasProjects            = Project::where('organization_id', $organization->id)->exists();
        $memberCount            = User::where('organization_id', $organization->id)->count();
        $hasInvitedMember       = $memberCount > 1;
        $hasAssignedProduct     = Project::where('organization_id', $organization->id)
            ->whereHas('users', function ($query) use ($organization) {
                $query->where('users.organization_id', $organization->id);
            })
            ->exists();
        $hasTrackingEnabledUser = User::where('organization_id', $organization->id)
            ->where('tracking_status', Status::YES)
            ->exists();

        return [
            [
                'key'         => 'verify-email',
                'title'       => __('Verify your email'),
                'description' => __('Secure your workspace and enable member invitations by confirming your email.'),
                'action_label'=> __('Send Verification Link'),
                'action_url'  => $emailVerified ? null : route('user.send.email.ver.link'),
                'completed'   => $emailVerified,
            ],
            [
                'key'         => 'add-project',
                'title'       => __('Create your first project'),
                'description' => __('Projects help you organize tasks and manage time tracking for your team.'),
                'action_label'=> __('Go to Projects'),
                'action_url'  => route('user.project.list'),
                'completed'   => $hasProjects,
            ],
            [
                'key'         => 'invite-member',
                'title'       => __('Invite a team member'),
                'description' => __('Add at least one teammate so you can start tracking time together.'),
                'action_label'=> __('Invite Members'),
                'action_url'  => route('user.member.list'),
                'completed'   => $hasInvitedMember,
            ],
            [
                'key'         => 'enable-tracking',
                'title'       => __('Enable tracking for a member'),
                'description' => __('Turn on tracking for at least one member so screenshots and activity can be captured for your team.'),
                'action_label'=> __('Manage Members'),
                'action_url'  => route('user.member.list'),
                'completed'   => $hasTrackingEnabledUser,
            ],
            [
                'key'         => 'assign-product',
                'title'       => __('Assign a project to a member'),
                'description' => __('Connect members to a project so they can start logging time.'),
                'action_label'=> __('Assign Project'),
                'action_url'  => route('user.project.list'),
                'completed'   => $hasAssignedProduct,
            ],
        ];
    }

    private function buildStaffOnboardingSteps(User $user): array
    {
        $emailVerified     = (bool) $user->ev;

        $hasProjectAccess  = $user->projects()->exists();
        $hasTrackedTime    = Track::where('user_id', $user->id)->exists();

        return [
            [
                'key'         => 'verify-email',
                'title'       => __('Verify your email'),
                'description' => __('Kindly verify your email address before beginning your tracking activities.'),
                'action_label'=> __('Send Verification Link'),
                'action_url'  => $emailVerified ? null : route('user.send.email.ver.link'),
                'completed'   => $emailVerified,
            ],
            [
                'key'         => 'join-project',
                'title'       => __('Join a project'),
                'description' => __('Please ensure you are assigned to at least one project. If not, kindly contact your organizer or manager for assistance.'),
                'action_label'=> __('View Projects'),
                'action_url'  => route('user.project.list'),
                'completed'   => $hasProjectAccess,
            ],
            [
                'key'         => 'start-tracking',
                'title'       => __('Start tracking time'),
                'description' => __('Download and install the application to start tracking your time and activities.'),
                'action_label'=> __('Download Desktop APP'),
                'action_url'  => 'https://timodesk.com/download',
                'new_tab'     => true,
                'completed'   => $hasTrackedTime,
            ],
        ];
    }

    public function timeTrackingData(Request $request)
    {
        $organization = auth()->user()->organization;
        [$startDate, $endDate] = $this->parseRangeOrMonth($request->input('date'));

        [$labels, $values] = $this->getTimeTrackingSeries($organization, $startDate, $endDate);

        return response()->json([
            'labels' => $labels,
            'values' => $values,
        ]);
    }

    public function activitySeriesData(Request $request)
    {
        $organization = auth()->user()->organization;
        [$startDate, $endDate] = $this->parseRangeOrMonth($request->input('date'));

        [$labels, $values] = $this->getActivitySeries($organization, $startDate, $endDate);

        return response()->json([
            'labels' => $labels,
            'values' => $values,
        ]);
    }

    public function topActivityStaff(Request $request)
    {
        $organization          = auth()->user()->organization;
        [$startDate, $endDate] = $this->parseRangeOrMonth($request->input('date'));
        $items                 = $this->getTopActivityStaffItems($organization, $startDate, $endDate, 5);

        $html = view('Template::user.partials.top_activity_staff', [
            'staffs' => $items,
        ])->render();

        return response()->json(['html' => $html]);
    }

    private function getProjectTimings($organization, $startDate, $endDate)
    {
        return $organization->tracks()
            ->mine()
            ->whereBetweenOrg('started_at', $startDate, $endDate)
            ->select(
                'project_id',
                DB::raw('SUM(tracks.time_in_seconds) as total_seconds'),
            )
            ->groupBy('project_id')
            ->with('project:id,title')
            ->orderBy('total_seconds', 'DESC')
            ->get();
    }

    private function getTimeTrackingSeries($organization, $startDate, $endDate)
    {
        $rows = $organization->tracks()
            ->mine()
            ->whereBetweenOrg('started_at', $startDate, $endDate)
            ->selectDateTz(alias:"day")
            ->selectRaw('SUM(time_in_seconds) as total_seconds')
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->keyBy('day');

        $labels = [];
        $values = [];
        $cursor = (clone $startDate)->startOfDay();
        $end    = (clone $endDate)->startOfDay();
        while ($cursor->lte($end)) {
            $key      = $cursor->toDateString();
            $labels[] = $cursor->format('M d');
            $seconds  = isset($rows[$key]) ? (int) $rows[$key]->total_seconds : 0;
            $values[] = (int) $seconds;
            $cursor->addDay();
        }

        return [$labels, $values];
    }

    private function getActivitySeries($organization, $startDate, $endDate)
    {
        $rows = $organization->tracks()
            ->whereBetweenOrg('started_at', $startDate, $endDate)
            ->selectDateTz(alias:'day')
            ->selectRaw('(SUM(overall_activity) / NULLIF(SUM(time_in_seconds), 0)) as avg_activity')
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->keyBy('day');

        $labels = [];
        $values = [];
        $cursor = (clone $startDate)->startOfDay();
        $end    = (clone $endDate)->startOfDay();
        while ($cursor->lte($end)) {
            $key      = $cursor->toDateString();
            $labels[] = $cursor->format('M d');
            $values[] = isset($rows[$key]) ? round((float) $rows[$key]->avg_activity, 2) : 0;
            $cursor->addDay();
        }
        return [$labels, $values];
    }

    private function getTopActivityStaffItems($organization, $startDate, $endDate, $limit = 5)
    {
        $rows = $organization->tracks()
            ->whereBetweenOrg('started_at', $startDate, $endDate)
            ->selectRaw('user_id')
            ->selectRaw('(SUM(overall_activity) / NULLIF(SUM(time_in_seconds), 0)) as avg_activity')
            ->with('user:id,image,fullname')
            ->groupBy('user_id')
            ->orderByDesc('avg_activity')
            ->limit($limit)
            ->get();

        return $rows->map(function ($row) {
            $user = $row->user;
            return (object) [
                'image_url' => $user->image_url,
                'name'      => toTitle($user->fullname),
                'avg'       => round((float) $row->avg_activity, 2),
                'image'     => $user->image,
            ];
        });
    }

    private function getPerformerRanks($organization, $startDate, $endDate, $limit = 5)
    {
        $rows = $organization->tracks()
            ->whereBetweenOrg('started_at', $startDate, $endDate)
            ->selectRaw('user_id')
            ->selectRaw('SUM(COALESCE(time_in_seconds, 0)) as totalSeconds')
            ->with('user:id,fullname,image')
            ->groupBy('user_id')
            ->get();

        $sortedDesc = $rows->sortByDesc('totalSeconds')->values();
        $sortedAsc  = $rows->sortBy('totalSeconds')->values();

        $mapRow = function ($row) {
            $user = $row->user;
            $name = toTitle($user->fullname);
            return (object) [
                'user_id' => $row->user_id,
                'name'    => $name,
                'seconds' => (int) $row->totalSeconds,
                'image'   => getImage($user->image),
            ];
        };

        return [
            $sortedDesc->take($limit)->map($mapRow),
            $sortedAsc->take($limit)->map($mapRow),
        ];
    }

    public function topPerformers(Request $request)
    {
        $organization          = auth()->user()->organization;
        [$startDate, $endDate] = $this->parseRangeOrMonth($request->input('date'));
        [$top]                 = $this->getPerformerRanks($organization, $startDate, $endDate, 5);
        return view('Template::user.partials.performer_list', [
            'performers' => $top,
            'title'      => __('Top Performers'),
        ]);
    }

    public function lowPerformers(Request $request)
    {
        $organization          = auth()->user()->organization;
        [$startDate, $endDate] = $this->parseRangeOrMonth($request->input('date'));
        [, $low]               = $this->getPerformerRanks($organization, $startDate, $endDate, 5);
        return view('Template::user.partials.performer_list', [
            'performers' => $low,
            'title'      => __('Low Performers'),
        ]);
    }

    private function parseRangeOrMonth($range)
    {
        if ($range && str_contains($range, '-')) {
            try {
                [$start, $end] = array_map('trim', explode('-', $range, 2));
                return [Carbon::parse($start)->setTimeZone(orgTimezone())->startOfDay(), Carbon::parse($end)->setTimeZone(orgTimezone())->endOfDay()];
            } catch (\Throwable $e) {
            }
        }
        return [nowOrg()->startOfMonth(), nowOrg()->endOfMonth()];
    }

    private function buildSummaryStats($organization, $startDate, $endDate)
    {
        $tracks = $organization->tracks()->mine()->whereBetweenOrg('started_at', $startDate, $endDate);
        $totalSeconds = (int) $tracks->clone()->sum('time_in_seconds');
        $avgActivity  = getActivity($tracks->clone());
        $projectCount = (int) $tracks->clone()->distinct('project_id')->count('project_id');

        $periodDays = (int) max(1, round($startDate->diffInDays($endDate)));
        $prevStart  = (clone $startDate)->subDays($periodDays);
        $prevEnd    = (clone $startDate)->subDay()->endOfDay();
        $prevTracks = $organization->tracks()->mine()->whereBetweenOrg('started_at', $prevStart, $prevEnd);

        $prevTotalSeconds = (int) $prevTracks->clone()->sum('time_in_seconds');
        $prevAvgActivity  = getActivity($prevTracks->clone(), true);

        $pct = function ($current, $previous) {
            if ($previous == 0) {
                return $current > 0 ? 100.0 : 0.0;
            }
            return (($current - $previous) / $previous) * 100.0;
        };

        return [
            'total_times'       => $totalSeconds,
            'average_activity'  => number_format($avgActivity ?: 0, 2),
            'total_projects'    => $projectCount,
            'delta_total_times' => round($pct($totalSeconds, $prevTotalSeconds), 2),
            'delta_activity'    => round($avgActivity - $prevAvgActivity, 2),
        ];
    }

    public function depositHistory(Request $request)
    {
        $pageTitle = 'Deposit History';
        $deposits  = auth()->user()->deposits()->searchable(['trx'])->with(['gateway'])->orderBy('id', 'desc')->paginate(getPaginate());
        return view('Template::user.deposit_history', compact('pageTitle', 'deposits'));
    }

    public function transactions()
    {
        $pageTitle = 'Transactions';
        $remarks   = Transaction::distinct('remark')->orderBy('remark')->get('remark');

        $transactions = Transaction::where('organization_id', organizationId())->searchable(['trx'])->orderBy('id', 'desc')->paginate(getPaginate());

        return view('Template::user.transactions', compact('pageTitle', 'transactions', 'remarks'));
    }

    public function downloadAttachment($fileHash)
    {
        $filePath  = decrypt($fileHash);
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        $title     = slug(gs('site_name')) . '- attachments.' . $extension;
        try {
            $mimetype = mime_content_type($filePath);
        } catch (\Exception $e) {
            $notify[] = ['error', 'File does not exists'];
            return back()->withNotify($notify);
        }
        header('Content-Disposition: attachment; filename="' . $title);
        header("Content-Type: " . $mimetype);
        return readfile($filePath);
    }

    public function placeholderImage($size = null)
    {
        $imgWidth  = explode('x', $size)[0];
        $imgHeight = explode('x', $size)[1];
        $text      = $imgWidth . '×' . $imgHeight;
        $fontFile  = realpath('assets/font/solaimanLipi_bold.ttf');
        $fontSize  = round(($imgWidth - 50) / 8);
        if ($fontSize <= 9) {
            $fontSize = 9;
        }
        if ($imgHeight < 100 && $fontSize > 30) {
            $fontSize = 30;
        }

        $image     = imagecreatetruecolor($imgWidth, $imgHeight);
        $colorFill = imagecolorallocate($image, 100, 100, 100);
        $bgFill    = imagecolorallocate($image, 255, 255, 255);
        imagefill($image, 0, 0, $bgFill);
        $textBox    = imagettfbbox($fontSize, 0, $fontFile, $text);
        $textWidth  = abs($textBox[4] - $textBox[0]);
        $textHeight = abs($textBox[5] - $textBox[1]);
        $textX      = ($imgWidth - $textWidth) / 2;
        $textY      = ($imgHeight + $textHeight) / 2;
        header('Content-Type: image/jpeg');
        imagettftext($image, $fontSize, 0, $textX, $textY, $colorFill, $fontFile, $text);
        imagejpeg($image);
        imagedestroy($image);
    }

    public function notifications()
    {
        $notifications   = UserNotification::where('user_id', auth()->id())->orderBy('id', 'desc')->with('sender')->paginate(getPaginate());
        $hasUnread       = UserNotification::where('user_id', auth()->id())->where('is_read', Status::NO)->exists();
        $hasNotification = UserNotification::where('user_id', auth()->id())->exists();
        $pageTitle       = 'Notifications';
        return view('Template::user.notifications', compact('pageTitle', 'notifications', 'hasUnread', 'hasNotification'));
    }

    public function notificationRead($id)
    {
        $notification          = UserNotification::where('user_id', auth()->id())->findOrFail($id);
        $notification->is_read = Status::YES;
        $notification->save();
        $url = $notification->click_url;
        if ($url == '#') {
            $url = url()->previous();
        }
        return redirect($url);
    }

    public function readAllNotification()
    {
        UserNotification::where('user_id', auth()->id())->where('is_read', Status::NO)->update([
            'is_read' => Status::YES,
        ]);

        $notify[] = ['success', 'Notifications read successfully'];
        return back()->withNotify($notify);
    }

    public function deleteAllNotification()
    {
        UserNotification::where('user_id', auth()->id())->delete();

        $notify[] = ['success', 'All notifications deleted successfully'];
        return back()->withNotify($notify);
    }

    public function deleteSingleNotification($id)
    {
        UserNotification::where('user_id', auth()->id())->where('id', $id)->delete();

        $notify[] = ['success', 'Notification deleted successfully'];
        return back()->withNotify($notify);
    }

    public function billingOverview()
    {
        $pageTitle   = "Billing Overview";
        $totalMember = User::where('organization_id', organizationId())->count();

        $user         = auth()->user();
        $organization = $user->organization;
        $freeTrial    = now()->parse($organization->trial_end_at);

        if ($freeTrial->gt(now())) {
            $startDate = $freeTrial->subDays(Status::FREE_TRIAL_DURATION);
        } else {
            $startDate = now()->parse($organization->next_invoice_date)->subDays(30);
        }

        $endDate = now();

        $trackingMember = Track::where('organization_id', $organization->id)->whereBetween('started_at', [$startDate, $endDate])->distinct('user_id')->count();

        $organizationDiscount = OrganizationDiscount::active()->where('organization_id', $organization->id)->latest()->first();
        $organizationDiscount = OrganizationDiscount::active()->latest()->first();

        $widget['unpaid_invoices']   = Invoice::unpaid()->where('organization_id', $organization->id)->count();
        $widget['total_invoices']    = Invoice::where('organization_id', $organization->id)->count();
        $widget['total_deposit']     = Deposit::successful()->where('user_id', $user->id)->sum('amount');
        $widget['transaction_count'] = Transaction::where('organization_id', $organization->id)->count();

        return view('Template::user.billing.overview', compact('pageTitle', 'organization', 'totalMember', 'trackingMember', 'organizationDiscount', 'widget'));

    }

    public function token() {
        $user = auth()->user();

        abort_unless($user->isOrganizer() || $user->isManager(), 403);

        $token = cache()->remember(
            "socket_admin_token_{$user->id}",
            now()->addMinutes((int) config('socket.token_session_minutes')),
            function () use ($user) {
                $user->tokens()->where('name', 'socket-admin')->delete();

                return $user
                    ->createToken('socket-admin', ['socket'])
                    ->plainTextToken;
            }
        );

        return response()->json([
            'token'  => $token,
            'org_id' => $user->organization_id,
        ]);
    }
}

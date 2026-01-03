<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\App;
use App\Models\Track;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function monthlyTimeSheet()
    {
        $pageTitle = 'Monthly Timesheet';

        $monthArray = [];

        $firstTrack = Track::where('organization_id', organizationId())->first();

        if ($firstTrack) {
            $startDate = now()->parse($firstTrack->started_at)->startOfMonth();
            $endDate   = now()->startOfMonth();

            while ($startDate <= $endDate) {
                $monthArray[] = $startDate->format('Y-m');
                $startDate->addMonth();
            }
        }

        $monthArray = array_reverse($monthArray);

        return view('Template::user.report.monthly_time_sheet', compact('pageTitle', 'monthArray'));
    }

    public function loadMonthlyTimeSheet(Request $request)
    {
        $monthInput = $request->input('month', now()->format('Y-m'));
        $aboveTime  = max(0, (int) $request->input('above_time', 0));
        $belowTime  = max(0, (int) $request->input('below_time', 0));

        try {
            $selectedMonth = Carbon::createFromFormat('Y-m', $monthInput)->startOfMonth();
        } catch (\Exception $e) {
            $selectedMonth = now()->startOfMonth();
        }

        $startOfMonth = $selectedMonth->copy()->startOfMonth();
        $endOfMonth   = $selectedMonth->copy()->endOfMonth();

        $organization = auth()->user()->organization;

        $tracks = Track::whereBetweenOrg('started_at', $startOfMonth, $endOfMonth)
            ->where('organization_id', $organization->id)
            ->selectRaw('SUM(time_in_seconds) AS totalSeconds')
            ->selectRaw('SUM(overall_activity) AS totalActivity')
            ->selectTzFormat(format: '%d', alias: 'created_on')
            ->addSelect('user_id')
            ->groupBy('user_id')
            ->groupBy('created_on')
            ->get();

        $totalSeconds    = $tracks->sum('totalSeconds');
        $totalActivity   = $tracks->sum('totalActivity');
        $activityPercent = $totalSeconds > 0 ? showAmount($totalActivity / $totalSeconds, currencyFormat: false) : 0;
        $totalWorks      = $tracks->count();
        $averageTime     = $totalSeconds / ($totalWorks > 0 ? $totalWorks : 1);

        $groupedTracks = [];
        $userStats     = [];
        $officeDays    = [];

        foreach ($tracks as $track) {
            $userId                       = (int) $track->user_id;
            $day                          = intval($track->created_on);
            $seconds                      = (int) $track->totalSeconds;
            $groupedTracks[$userId][$day] = $seconds;

            $userStats[$userId]['total'] = ($userStats[$userId]['total'] ?? 0) + $seconds;
            $userStats[$userId]['days']  = ($userStats[$userId]['days'] ?? 0) + 1;
        }

        $userIds = $tracks->pluck('user_id')->unique()->values()->all();
        $users   = User::whereIn('id', $userIds)->get();
        $unTracked   = User::where('organization_id',myOrganization()->id)->whereNotIn('id', $userIds)->where('status',1)->get();
        $users = $users->merge($unTracked)->sortBy('fullname');
        $daysInMonth = Carbon::parse($startOfMonth)->daysInMonth;

        if (request()->pdf) {
            $monthTitle = Carbon::createFromFormat('Y-m', $monthInput)->format('F Y');

            $pdf = Pdf::loadView(
                'Template::user.report.monthly_time_sheet_pdf',
                compact('daysInMonth', 'users', 'organization', 'officeDays', 'groupedTracks', 'userStats', 'monthTitle', 'organization', 'aboveTime', 'belowTime')
            );

            $pdf->setPaper('A4', 'landscape');
            $pdf->setOptions([
                'dpi'             => 300,
                'isRemoteEnabled' => true,
                'chroot'          => realpath(base_path('..')),
                'fontDir'         => storage_path('fonts'),
                'fontCache'       => storage_path('fonts'),
            ]);

            return $pdf->download("time-analytics-{$monthInput}.pdf");
        }

        return response()->json([
            'view'             => view('Template::user.report.monthly_time_sheet_content', compact('daysInMonth', 'users', 'organization', 'officeDays', 'groupedTracks', 'userStats'))->render(),
            'days_in_month'    => $daysInMonth,
            'total_time'       => formatSecondsToHoursMinutes($totalSeconds),
            'average_time'     => formatSecondsToHoursMinutes($averageTime),
            'activity_percent' => $activityPercent . '%',
        ]);
    }

    public function timeAnalytics()
    {
        $pageTitle = 'Time Analytics';

        $date = request()->date;

        if ($date) {
            try {
                [$start, $end] = array_map('trim', explode('-', $date));
                $startDate     = Carbon::createFromFormat('m/d/Y', $start)->startOfDay();
                $endDate       = Carbon::createFromFormat('m/d/Y', $end)->endOfDay();
            } catch (\Throwable $th) {
                $startDate = now()->startOfMonth();
                $endDate   = now()->endOfMonth();
            }
            $dateFrom  = $startDate->format('m/d/Y');
            $dateTo    = $endDate->format('m/d/Y');
            $dateRange = $dateFrom . ' - ' . $dateTo;

        } else {
            $date =  last30Days('m/d/Y');
            $dateRange = $date['defaultDateRange'];
        }

        $members = User::where('organization_id', organizationId())->me()->orderBy('fullname')->get();

        return view('Template::user.report.time_analytics', compact('pageTitle', 'dateRange', 'members'));
    }

    public function loadTimeAnalytics(Request $request)
    {
        $date = $request->date;
        $user = User::where('organization_id', organizationId())->where('uid', $request->user)->first();

        if ($date) {
            try {
                [$start, $end] = array_map('trim', explode('-', $date));
                $startDate     = Carbon::createFromFormat('m/d/Y', $start)->startOfDay();
                $endDate       = Carbon::createFromFormat('m/d/Y', $end)->endOfDay();
            } catch (\Throwable $th) {
                $startDate = now()->startOfMonth();
                $endDate   = now()->endOfMonth();
            }
        }

        $tracks = Track::whereBetweenOrg('started_at', $startDate, $endDate)
            ->mine()
            ->where('organization_id', organizationId())
            ->when($user, function ($query) use ($user) {
                return $query->where('user_id', $user->id);
            })
            ->selectRaw('SUM(time_in_seconds) AS totalSeconds')
            ->selectTzFormat(format: '%d-%m-%Y', alias: 'created_on')
            ->groupBy('created_on')
            ->get();

        $indexed = $tracks->keyBy('created_on');

        $dateList = [];
        $timeList = [];

        $cursor = $startDate->copy();
        while ($cursor->lte($endDate)) {
            $key        = $cursor->format('d-m-Y');
            $dateList[] = $key;

            $timeList[] = $indexed[$key]->totalSeconds ?? 0;

            $cursor->addDay();
        }

        return response()->json([
            'dateList' => $dateList,
            'timeList' => $timeList,
        ]);
    }



    public function appAnalytics()
    {
        $pageTitle = 'App Analytics';

        $members = User::where('organization_id', organizationId())->me()->orderBy('fullname')->get();

        $date =  last30Days('m/d/Y');
        $dateRange = $date['defaultDateRange'];

        return view('Template::user.report.app_analytics', compact('pageTitle', 'members', 'dateRange'));
    }

    public function loadAppAnalytics(Request $request)
    {
        if ($request->date) {
            try {
                [$start, $end] = array_map('trim', explode('-', $request->date));
                $startDate     = Carbon::createFromFormat('m/d/Y', $start)->startOfDay();
                $endDate       = Carbon::createFromFormat('m/d/Y', $end)->endOfDay();
            } catch (\Exception $e) {
                //
            }
        }

        if (!isset($startDate)) {
            $startDate = now()->startOfMonth();
            $endDate   = now()->endOfMonth();
        }

        $sortBy        = $request->input('usage_sort', 'top');
        $sortDirection = $sortBy === 'least' ? 'asc' : 'desc';

        $user = User::where('organization_id', organizationId())->where('uid', $request->user)->first();

        $apps = App::mine()
            ->where('org_id', organizationId())
            ->whereBetweenOrg('started_at', $startDate, $endDate)
            ->when($request->filled('user') && $user, function ($query) use ($user) {
                return $query->where('user_id', $user->id);
            })
            ->selectRaw('app_name, SUM(session_time) AS totalSeconds')
            ->groupBy('app_name')
            ->havingRaw('SUM(session_time) >= 60')
            ->orderBy('totalSeconds', $sortDirection)
            ->limit(20)
            ->get();

        $labels = $apps->pluck('app_name');
        $values = $apps->map(function ($app) {
            $seconds = $app->totalSeconds ?? 0;

            return round($seconds, 2);
        });

        return response()->json([
            'labels' => $labels,
            'values' => $values,
        ]);
    }

    public function projectTiming()
    {
        $pageTitle = 'Project Timing';

        $members = User::where('organization_id', organizationId())->me()->orderBy('fullname')->get();

        $date =  last30Days('m/d/Y');
        $dateRange = $date['defaultDateRange'];

        return view('Template::user.report.project_timing', compact('pageTitle', 'members', 'dateRange'));
    }

    public function loadProjectTiming(Request $request)
    {
        if ($request->date) {
            try {
                [$start, $end] = array_map('trim', explode('-', $request->date));
                $startDate     = Carbon::createFromFormat('m/d/Y', $start)->startOfDay();
                $endDate       = Carbon::createFromFormat('m/d/Y', $end)->endOfDay();
            } catch (\Exception $e) {
            }
        }

        if (!isset($startDate)) {
            $startDate = now()->startOfMonth();
            $endDate   = now()->endOfMonth();
        }

        $uid = $request->user;
        $user = User::where('organization_id', organizationId())->where('uid', $uid)->first();
        $userId = $user?->id ?? 0;

        $tracks = Track::with('user:id,fullname,image', 'project:id,title,uid,icon,color')
            ->mine()
            ->whereBetweenOrg('started_at', $startDate, $endDate)
            ->when($userId > 0, function ($query) use ($userId) {
                return $query->where('user_id', $userId);
            })
            ->where('organization_id', organizationId())
            ->selectRaw('SUM(time_in_seconds) AS totalSeconds')
            ->selectTzFormat(format: '%d-%m-%Y', alias: 'created_on')
            ->addSelect('user_id', 'project_id')
            ->groupBy('created_on')
            ->groupBy('user_id')
            ->groupBy('project_id')
            ->orderBy('created_on', 'DESC')
            ->get();

        $view = $request->group_by == 'date' ? 'project_timing_date_group' : 'project_timing_member_group';

        return response()->json([
            'view' => view("Template::user.report." . $view, [
                'tracks' => $tracks,
            ])->render(),
        ]);
    }
}

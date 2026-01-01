<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Track;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TimeSheetController extends Controller
{

    public function timeCalender($uid = null)
    {
        $pageTitle = "Time Calender";

        $members = User::where('organization_id', organizationId())->me()->orderBy('fullname')->get();

        $year  = orgNow()->year;
        $month = orgNow()->month;

        $memberId = null;
        if ($uid) {
            $filteredMember = User::where('organization_id', auth()->user()->organization_id)->me()->where('uid',$uid)->first();
            if ($filteredMember) {
                $memberId = $filteredMember->id;
            }
        }

        $calendarData = $this->getCalendarData($month, $year, $memberId);
        extract($calendarData);

        return view('Template::user.time_analytics.time_calender', compact(
            'pageTitle',
            'members',
            'weekdays',
            'cells',
            'monthLabel',
            'year',
            'month',
            'prev',
            'next',
            'projectsByDay',
            'todayYmd',
            'memberId'
        ));
    }


    public function loadCalender(Request $request)
    {
        $year  = (int) ($request->query('y', orgNow()->year));
        $month = (int) ($request->query('m', orgNow()->month));

        $user = User::where('organization_id', organizationId())->where('uid', $request->member)->first();
        $memberId = $user?->id ?? null;

        $calendarData = $this->getCalendarData($month, $year, $memberId);
        $html = view('Template::user.time_analytics.calender', $calendarData)->render();
        $calenderHeader = view('Template::user.time_analytics.calender_header', $calendarData)->render();

        return response()->json([
            'html'       => $html,
            'calender_header'=>$calenderHeader
        ]);
    }

    private function getCalendarData($month, $year, $memberId = null)
    {
        if ($month < 1 || $month > 12) {
            $month = (int) orgNow()->month;
            $year  = (int) orgNow()->year;
        }

        $firstOfMonth = Carbon::create($year, $month, 1);
        $monthLabel   = $firstOfMonth->format('F Y');
        $todayYmd     = orgNow()->toDateString();

        $prev = (clone $firstOfMonth)->subMonth();
        $next = (clone $firstOfMonth)->addMonth();

        $startGrid  = (clone $firstOfMonth)->modify('last sunday');
        $endGrid    = (clone $startGrid)->addDays(41);
        $daysInGrid = 42;

        $aggregates = Track::query()->mine();
        if ($memberId) {
            $aggregates->where('user_id',$memberId);
        }

        $aggregates = $aggregates->where('organization_id', organizationId())
            ->whereBetweenOrg('started_at',
                (clone $startGrid)->startOfDay(),
                (clone $endGrid)->endOfDay(),
            )
            ->selectDateTz()
            ->selectRaw('SUM(time_in_seconds) as total_seconds')
            ->selectRaw('COUNT(DISTINCT project_id) as project_count')
            ->groupBy('d')
            ->get();

        $dailyStats = [];
        foreach ($aggregates as $row) {
            $seconds  = (int) $row->total_seconds;
            $dailyStats[$row->d] = [
                'total_seconds'   => 19,
                'total_formatted' => formatSecondsToHoursMinutes($seconds),
                'project_count'   => (int) $row->project_count,
            ];
        }

        // Also aggregate per-project per-day for preloading modal data
        $projectAgg = Track::query()->mine();
        if ($memberId) {
            $projectAgg->where('user_id', $memberId);
        }
        $projectAgg = $projectAgg->where('organization_id', organizationId())
            ->whereBetweenOrg('started_at',
                (clone $startGrid)->startOfDay(),
                (clone $endGrid)->endOfDay(),
            )
            ->selectDateTz()
            ->selectRaw('project_id')
            ->selectRaw('SUM(time_in_seconds) as total_seconds')
            ->groupBy('d', 'project_id')
            ->with('project:id,title')
            ->get();

        $projectsByDay = [];
        foreach ($projectAgg as $row) {
            $d = $row->d;
            $seconds = (int) $row->total_seconds;
            $projectsByDay[$d][] = [
                'project' => $row->project?->title ?? __('Unknown Project'),
                'seconds' => $seconds,
                'display' => $seconds > 0 ? formatSecondsToHoursMinutes($seconds) : '0:00',
            ];
        }

        $cells = [];
        for ($i = 0; $i < $daysInGrid; $i++) {
            $day    = (clone $startGrid)->modify("+{$i} day");
            $ymd  = $day->format('Y-m-d');
            $stat = $dailyStats[$ymd] ?? null;
            $cells[] = [
                'ymd'         => $ymd,
                'day'         => (int) $day->format('j'),
                'isThisMonth' => (int) $day->format('n') === $month,
                'isToday'     => $ymd === $todayYmd,
                'total'       => $stat['total_formatted'] ?? '0:00',
                'projects'    => $stat['project_count'] ?? 0,
            ];
        }

        $weekdays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

        $members = User::where('organization_id', organizationId())->orderBy('fullname')->get();

        return compact('weekdays', 'cells', 'monthLabel', 'year', 'month', 'prev', 'next', 'todayYmd', 'projectsByDay', 'members', 'memberId');
    }



    public function timeWeekly(Request $request, $uid = null)
    {
        $pageTitle = "Weekly Work Log";

        $members = User::where('organization_id', organizationId())->me()->get();

        $memberId = null;
        if ($uid) {
            $filteredMember = User::where('organization_id', auth()->user()->organization_id)->me()->where('uid',$uid)->first();
            if ($filteredMember) {
                $memberId = $filteredMember->uid;
                request()->merge(['member'=>$memberId]);
            }
        }

        $data = $this->buildWeeklyData($request);

        return view('Template::user.time_analytics.weekly', array_merge(
            compact('pageTitle', 'members','memberId'),
            $data
        ));
    }

    public function loadWeekly(Request $request)
    {
        $data = $this->buildWeeklyData($request);
        $table = view('Template::user.time_analytics.weekly_table', $data)->render();
        $header = view('Template::user.time_analytics.weekly_calender_header', $data)->render();

        return response()->json([
            'html' => $table,
            'calender_header' => $header,
        ]);
    }

    private function buildWeeklyData(Request $request)
    {
        // Determine week range (Sunday to Saturday) based on query date or today
        $baseDate = $request->query('date');
        $base = $baseDate ? Carbon::parse($baseDate) : orgNow();
        $weekStart = (clone $base)->startOfWeek(Carbon::SUNDAY);
        $weekEnd   = (clone $base)->endOfWeek(Carbon::SATURDAY);

        // Build days array for headers
        $days = [];
        for ($i = 0; $i < 7; $i++) {
            $d = (clone $weekStart)->addDays($i);
            $days[] = [
                'date' => $d->toDateString(),
                'day'  => (int) $d->format('j'),
                'dow'  => $d->format('D'), // Sun, Mon, ...
            ];
        }

        // Aggregate tracked seconds per project per day in the week
        $user = User::where('organization_id', organizationId())->where('uid', $request->member)->first();
        $memberId = $user?->id ?? null;

        $query = Track::query()
            ->mine()
            ->where('organization_id', organizationId())
            ->whereBetweenOrg('started_at',
                (clone $weekStart)->startOfDay(),
                (clone $weekEnd)->endOfDay(),
            );

        if ($memberId) {
            $query->where('user_id', $memberId);
        }

        $rowsRaw = $query
            ->select('project_id')
            ->selectDateTz()
            ->selectRaw('SUM(time_in_seconds) as total_seconds')
            ->groupBy('project_id', 'd')
            ->with('project')
            ->get();

        // Organize by project
        $projectMap = [];
        foreach ($rowsRaw as $r) {
            $pid = (int) $r->project_id;
            if (!isset($projectMap[$pid])) {
                $projectMap[$pid] = [
                    'uid' => $r->project->uid,
                    'id' => $r->project->id,
                    'color' => (object)$r->project->color,
                    'title' => $r->project?->title ?? __('Unknown Project'),
                    'icon_url' => $r->project?->icon_url,
                    'byDay' => [],
                    'total' => 0,
                ];
            }
            $seconds = (int) $r->total_seconds;
            $projectMap[$pid]['byDay'][$r->d] = ($projectMap[$pid]['byDay'][$r->d] ?? 0) + $seconds;
            $projectMap[$pid]['total'] += $seconds;
        }

        // Build rows for the table and compute column totals
        $rows = [];
        $colTotals = array_fill(0, 7, 0);
        $grandTotal = 0;
        foreach ($projectMap as $pid => $info) {
            $cells = [];
            for ($i = 0; $i < 7; $i++) {
                $date = $days[$i]['date'];
                $sec = (int) ($info['byDay'][$date] ?? 0);
                $cells[] = [
                    'seconds' => $sec,
                    'display' => $sec > 0 ? formatSecondsToHoursMinutes($sec) : '0:00',
                ];
                $colTotals[$i] += $sec;
            }
            $grandTotal += $info['total'];
            $rows[] = [
                'project' => $info,
                'cells'   => $cells,
                'total'   => [
                    'seconds' => $info['total'],
                    'display' => $info['total'] > 0 ? formatSecondsToHoursMinutes($info['total']) : '0:00',
                ],
            ];
        }

        // Format footer totals
        $footer = [
            'byDay' => array_map(function ($sec) {
                return [
                    'seconds' => $sec,
                    'display' => $sec > 0 ? formatSecondsToHoursMinutes($sec) : '0:00',
                ];
            }, $colTotals),
            'grand' => [
                'seconds' => $grandTotal,
                'display' => $grandTotal > 0 ? formatSecondsToHoursMinutes($grandTotal) : '0:00',
            ],
        ];

        // Week label e.g., "Nov 2 – 8, 2025" and prev/next anchor dates
        $sameMonth = $weekStart->month === $weekEnd->month;
        if ($sameMonth) {
            $weekLabel = $weekStart->format('M j') . ' – ' . $weekEnd->format('j, Y');
        } else {
            $weekLabel = $weekStart->format('M j') . ' – ' . $weekEnd->format('M j, Y');
        }

        $prevWeek = (clone $weekStart)->subWeek()->toDateString();
        $nextWeek = (clone $weekStart)->addWeek()->toDateString();
        $members = User::where('organization_id', organizationId())->me()->get();

        $members = User::where('organization_id', organizationId())->orderBy('fullname')->get();

        return compact(
            'days', 'rows', 'footer', 'weekLabel', 'weekStart', 'weekEnd', 'prevWeek', 'nextWeek', 'members', 'memberId'
        );
    }
}

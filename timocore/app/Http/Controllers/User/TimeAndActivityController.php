<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Track;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TimeAndActivityController extends Controller
{
     public function timeAndActivity()
    {
        $pageTitle = 'Time and Activity';

        $members = User::where('organization_id', organizationId())->orderBy('fullname')->me()->get();

        $dateFrom  = now()->startOfMonth()->format('m/d/Y');
        $dateTo    = now()->endOfMonth()->format('m/d/Y');
        $dateRange = $dateFrom . ' - ' . $dateTo;

        return view('Template::user.report.time_and_activity', compact('pageTitle', 'members', 'dateRange'));
    }


    public function loadTimeAndActivity(Request $request)
    {
        [$startDate, $endDate] = $this->resolveTimeActivityRange($request->date);

        $user = User::where('organization_id', organizationId())->where('uid', $request->user)->first();
        $userId = $user?->id ?? 0;
        $groupBy = $request->input('group_by', 'date');
        $level   = $request->input('level', 'root');

        if ($request->boolean('is_export') && !$request->ajax()) {

            if($request->data_type == 'expanded' && $request->export_type == 'pdf' && $startDate->diffInDays($endDate) > 31) {
                $notify[] = ['error', 'The expanded PDF can only be exported for up to 1 month.'];
                return back()->withNotify($notify);
            }

            $tracks = $this->timeActivityExportQuery($startDate, $endDate, $userId)->get();

            $totalSeconds    = $tracks->sum('totalSeconds');
            $totalActivity   = $tracks->sum('totalActivity');
            $activityPercent = showAmount($totalActivity / ($totalSeconds > 0 ? $totalSeconds : 1), currencyFormat: false);
            $totalWorkTime   = formatSecondsToHoursMinutes($totalSeconds);
            $dataType        = $request->input('data_type', 'collapsed');
            $exportType      = $request->input('export_type', 'pdf');
            $organization    = myOrganization();

            if ($exportType === 'pdf') {
                $view            = ($groupBy === 'member') ? 'time_and_activity_member_group' : ($groupBy === 'date' ? 'time_and_activity_date_group' : 'time_and_activity_project_group');
                $pdf = Pdf::loadView(
                    "Template::user.report." . $view . '_pdf',
                    [
                        'tracks'          => $tracks,
                        'totalWorkTime'   => $totalWorkTime,
                        'activityPercent' => $activityPercent . '%',
                        'dataType'        => $dataType,
                        'organization'    => $organization,
                        'startDate'       => $startDate,
                        'endDate'         => $endDate,
                    ]
                );

                $pdf->setPaper('A4', 'portrait');
                $pdf->setOptions([
                    'isRemoteEnabled' => true,
                    'chroot'          => realpath(base_path('..')),
                    'fontDir'         => storage_path('fonts'),
                    'fontCache'       => storage_path('fonts'),
                ]);

                return $pdf->download('time-activitiy-report-from-'.$startDate.'-to-'.$endDate.'.pdf');
            }

            $activityLabel = $activityPercent . '%';

            if ($groupBy === 'date') {
                return $this->timeActivityDateGroupDownloadToCsv($tracks, $totalWorkTime, $activityLabel, $dataType, $organization, $startDate, $endDate);
            }

            if ($groupBy === 'project') {
                return $this->timeActivityProjectGroupDownloadToCsv($tracks, $totalWorkTime, $activityLabel, $dataType, $organization, $startDate, $endDate);
            }

            return $this->timeActivityMemberGroupDownloadToCsv($tracks, $totalWorkTime, $activityLabel, $dataType, $organization, $startDate, $endDate);
        }

        if ($groupBy === 'member') {
            $viewContent = $this->renderMemberGrouping($level, $request, $startDate, $endDate, $userId);
        } elseif($groupBy === 'date') {
            $viewContent = $this->renderDateGrouping($level, $request, $startDate, $endDate, $userId);
        } else {
            $viewContent = $this->renderProjectGrouping($level, $request, $startDate, $endDate, $userId);
        }

        $response = [
            'view' => $viewContent,
        ];

        if ($level === 'root') {
            $stats = $this->buildTimeActivityStats($startDate, $endDate, $userId);
            $response = array_merge($response, [
                'total_work_time'      => formatSecondsToHoursMinutes($stats['totalSeconds']),
                'activity_percent'     => $stats['activityPercent'] . '%',
                'active_members'       => $stats['activeMembers'],
                'tracked_days'         => $stats['trackedDays'],
                'avg_hours_per_member' => formatSecondsToHoursMinutes($stats['avgSecondsPerMember']),
            ]);
        }

        return response()->json($response);
    }



    private function resolveTimeActivityRange(?string $dateRange): array
    {
        if ($dateRange) {
            try {
                [$start, $end] = array_map('trim', explode('-', $dateRange));
                $startDate     = Carbon::createFromFormat('m/d/Y', $start)->startOfDay();
                $endDate       = Carbon::createFromFormat('m/d/Y', $end)->endOfDay();
            } catch (\Exception $exception) {
                //
            }
        }

        if (!isset($startDate) || !isset($endDate)) {
            $startDate = now()->startOfMonth();
            $endDate   = now()->endOfMonth();
        }

        return [$startDate, $endDate];
    }

    private function timeActivityExportQuery(Carbon $startDate, Carbon $endDate, int $userId)
    {
        return Track::with('user:id,fullname,image', 'project:id,title')
            ->mine()
            ->whereBetweenOrg('started_at', $startDate, $endDate)
            ->when($userId > 0, function ($query) use ($userId) {
                return $query->where('user_id', $userId);
            })
            ->where('organization_id', organizationId())
            ->selectRaw('SUM(time_in_seconds) AS totalSeconds')
            ->selectRaw('SUM(overall_activity) AS totalActivity')
            ->selectRaw('SUM(mouse_counts) AS totalMouseCounts')
            ->selectRaw('SUM(keyboard_counts) AS totalKeyboardCounts')
            ->selectTzFormat(format:'%d-%m-%Y', alias:'created_on')
            ->addSelect('user_id', 'project_id')
            ->groupBy('created_on')
            ->groupBy('user_id')
            ->groupBy('project_id')
            ->orderBy('created_on', 'DESC');
    }

    private function baseTimeActivityQuery(Carbon $startDate, Carbon $endDate, int $userId = 0, bool $withRelations = false)
    {
        $query = Track::query()
            ->mine()
            ->whereBetweenOrg('started_at', $startDate, $endDate)
            ->when($userId > 0, function ($builder) use ($userId) {
                return $builder->where('user_id', $userId);
            })
            ->where('organization_id', organizationId());

        if ($withRelations) {
            $query->with('user:id,fullname,image', 'project:id,title');
        }

        return $query;
    }

    private function renderDateGrouping(string $level, Request $request, Carbon $startDate, Carbon $endDate, int $userId): string
    {
        if ($level === 'date_users') {
            $dateKey = $request->input('date_key');
            [$dayStart, $dayEnd] = $this->resolveScopedDayRange($dateKey, $startDate, $endDate);

            $users = $this->baseTimeActivityQuery($dayStart, $dayEnd, $userId, true)
                ->select('user_id')
                ->selectRaw('SUM(time_in_seconds) AS totalSeconds')
                ->selectRaw('SUM(overall_activity) AS totalActivity')
                ->selectRaw('COUNT(DISTINCT project_id) AS totalProjects')
                ->groupBy('user_id')
                ->orderByDesc('totalSeconds')
                ->get();

            return view('Template::user.report.partials.time_activity_date_users', compact('users', 'dateKey'))->render();
        }

        if ($level === 'date_user_projects') {
            $dateKey  = $request->input('date_key');
            $memberId = (int) $request->input('member_id', 0);
            [$dayStart, $dayEnd] = $this->resolveScopedDayRange($dateKey, $startDate, $endDate);

            $projects = $this->baseTimeActivityQuery($dayStart, $dayEnd, $userId, true)
                ->where('user_id', $memberId)
                ->select('project_id')
                ->selectRaw('SUM(time_in_seconds) AS totalSeconds')
                ->selectRaw('SUM(overall_activity) AS totalActivity')
                ->groupBy('project_id')
                ->orderByDesc('totalSeconds')
                ->get();

            return view('Template::user.report.partials.time_activity_date_user_projects', compact('projects'))->render();
        }

        $dateExpression = $this->orgDateExpression();

        $dates = $this->baseTimeActivityQuery($startDate, $endDate, $userId)
            ->selectRaw("{$dateExpression} AS usage_date")
            ->selectRaw('SUM(time_in_seconds) AS totalSeconds')
            ->selectRaw('SUM(overall_activity) AS totalActivity')
            ->selectRaw('COUNT(DISTINCT user_id) AS totalUsers')
            ->groupBy('usage_date')
            ->orderByDesc('usage_date')
            ->get();

        return view('Template::user.report.time_and_activity_date_group', compact('dates'))->render();
    }

    private function renderMemberGrouping(string $level, Request $request, Carbon $startDate, Carbon $endDate, int $userId): string
    {
        if ($level === 'member_dates') {
            $memberId       = (int) $request->input('member_id', 0);
            $dateExpression = $this->orgDateExpression();

            $dates = $this->baseTimeActivityQuery($startDate, $endDate, $userId)
                ->where('user_id', $memberId)
                ->selectRaw("{$dateExpression} AS usage_date")
                ->selectRaw('SUM(time_in_seconds) AS totalSeconds')
                ->selectRaw('SUM(overall_activity) AS totalActivity')
                ->selectRaw('COUNT(DISTINCT project_id) AS totalProjects')
                ->groupBy('usage_date')
                ->orderByDesc('usage_date')
                ->get();

            return view('Template::user.report.partials.time_activity_member_dates', compact('dates', 'memberId'))->render();
        }

        if ($level === 'member_date_projects') {
            $memberId = (int) $request->input('member_id', 0);
            $dateKey  = $request->input('date_key');

            [$dayStart, $dayEnd] = $this->resolveScopedDayRange($dateKey, $startDate, $endDate);

            $projects = $this->baseTimeActivityQuery($dayStart, $dayEnd, $userId, true)
                ->where('user_id', $memberId)
                ->select('project_id')
                ->selectRaw('SUM(time_in_seconds) AS totalSeconds')
                ->selectRaw('SUM(overall_activity) AS totalActivity')
                ->groupBy('project_id')
                ->orderByDesc('totalSeconds')
                ->get();

            return view('Template::user.report.partials.time_activity_member_date_projects', compact('projects'))->render();
        }

        $dateExpression = $this->orgDateExpression();

        $members = $this->baseTimeActivityQuery($startDate, $endDate, $userId, true)
            ->select('user_id')
            ->selectRaw('SUM(time_in_seconds) AS totalSeconds')
            ->selectRaw('SUM(overall_activity) AS totalActivity')
            ->selectRaw("COUNT(DISTINCT {$dateExpression}) AS totalDates")
            ->groupBy('user_id')
            ->orderByDesc('totalSeconds')
            ->get();

        return view('Template::user.report.time_and_activity_member_group', compact('members'))->render();
    }

    private function renderProjectGrouping(string $level, Request $request, Carbon $startDate, Carbon $endDate, int $userId): string
    {
        if ($level === 'project_dates') {
            $projectId       = (int) $request->input('project_id', 0);
            $dateExpression = $this->orgDateExpression();

            $dates = $this->baseTimeActivityQuery($startDate, $endDate, $userId)
                ->where('project_id', $projectId)
                ->selectRaw("{$dateExpression} AS usage_date")
                ->selectRaw('SUM(time_in_seconds) AS totalSeconds')
                ->selectRaw('SUM(overall_activity) AS totalActivity')
                ->selectRaw('COUNT(DISTINCT project_id) AS totalProjects')
                ->groupBy('usage_date')
                ->orderByDesc('usage_date')
                ->get();

            return view('Template::user.report.partials.time_activity_project_dates', compact('dates', 'projectId'))->render();
        }

        if ($level === 'project_date_members') {
            $projectId = (int) $request->input('project_id', 0);
            $dateKey  = $request->input('date_key');

            [$dayStart, $dayEnd] = $this->resolveScopedDayRange($dateKey, $startDate, $endDate);

            $members = $this->baseTimeActivityQuery($dayStart, $dayEnd, $userId, true)
                ->where('project_id', $projectId)
                ->select('user_id')
                ->selectRaw('SUM(time_in_seconds) AS totalSeconds')
                ->selectRaw('SUM(overall_activity) AS totalActivity')
                ->groupBy('user_id')
                ->orderByDesc('totalSeconds')
                ->get();

            return view('Template::user.report.partials.time_activity_project_date_members', compact('members'))->render();
        }

        $dateExpression = $this->orgDateExpression();

        $projects = $this->baseTimeActivityQuery($startDate, $endDate, $userId, true)
            ->select('project_id')
            ->selectRaw('SUM(time_in_seconds) AS totalSeconds')
            ->selectRaw('SUM(overall_activity) AS totalActivity')
            ->selectRaw("COUNT(DISTINCT {$dateExpression}) AS totalDates")
            ->groupBy('project_id')
            ->orderByDesc('totalSeconds')
            ->get();

        return view('Template::user.report.time_and_activity_project_group', compact('projects'))->render();
    }

    private function resolveScopedDayRange(?string $dateKey, Carbon $defaultStart, Carbon $defaultEnd): array
    {
        if (!$dateKey) {
            return [$defaultStart, $defaultEnd];
        }

        try {
            $date = Carbon::createFromFormat('Y-m-d', trim($dateKey), orgTimezone());
        } catch (\Exception $exception) {
            return [$defaultStart, $defaultEnd];
        }

        return [$date->copy()->startOfDay(), $date->copy()->endOfDay()];
    }

    private function orgDateExpression(string $column = 'started_at'): string
    {
        $fromOffset = now(config('app.timezone'))->format('P');
        $toOffset   = now(orgTimezone())->format('P');

        return "DATE_FORMAT(CONVERT_TZ({$column}, '{$fromOffset}', '{$toOffset}'), '%Y-%m-%d')";
    }

    private function buildTimeActivityStats(Carbon $startDate, Carbon $endDate, int $userId): array
    {
        $baseQuery = $this->baseTimeActivityQuery($startDate, $endDate, $userId);

        $totalSeconds  = (clone $baseQuery)->sum('time_in_seconds');
        $totalActivity = (clone $baseQuery)->sum('overall_activity');
        $activeMembers = (clone $baseQuery)->distinct('user_id')->count('user_id');

        $dateExpression = $this->orgDateExpression();
        $trackedDays    = (clone $baseQuery)->selectRaw("COUNT(DISTINCT {$dateExpression}) AS aggregate")->value('aggregate') ?? 0;
        $userDaysExpr   = "CONCAT(user_id, '-', {$dateExpression})";
        $userDays       = (clone $baseQuery)->selectRaw("COUNT(DISTINCT {$userDaysExpr}) AS aggregate")->value('aggregate') ?? 0;

        $avgSecondsPerMember = $userDays > 0 ? (int) floor($totalSeconds / $userDays) : 0;
        $activityPercent     = showAmount($totalActivity / ($totalSeconds > 0 ? $totalSeconds : 1), currencyFormat: false);

        return [
            'totalSeconds'        => $totalSeconds,
            'totalActivity'       => $totalActivity,
            'activeMembers'       => (int) $activeMembers,
            'trackedDays'         => (int) $trackedDays,
            'avgSecondsPerMember' => $avgSecondsPerMember,
            'activityPercent'     => $activityPercent,
        ];
    }

    private function timeActivityDateGroupDownloadToCsv($tracks, $totalWorkTime, $activityPercent, $dataType, $organization, $startDate, $endDate)
    {

        $start = $startDate ? $startDate->format('Y-m-d') : 'start';
        $end   = $endDate ? $endDate->format('Y-m-d') : 'end';

        $filename = "time_activity_{$start}_to_{$end}_" . date('H-i-s') . ".csv";

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');

        // reporty summary
        fputcsv($output, ["Organization:", $organization->name]);
        fputcsv($output, [
            "Report Period:",
            !empty($startDate) && !empty($endDate)
            ? $startDate->format('M d, Y') . " - " . $endDate->format('M d, Y')
            : '',
        ]);
        fputcsv($output, ["Worked Time:", $totalWorkTime, "Average Activity:", $activityPercent]);

        // add gap row
        fputcsv($output, []);

        // table headers based on data type
        if ($dataType === "expanded") {
            $labels = ['Date', 'Member', 'Project', 'Total Time', 'Activity'];
            if (auth()->user()->isStaff()) {
                unset($labels[1]);
            }
        } else {
            $labels = ['Date', 'Total Time', 'Activity'];
        }

        fputcsv($output, $labels);


        // loop date groups
        foreach ($tracks->groupBy('created_on') as $date => $items) {
            // Calculate totals for this date
            $totalSeconds        = $items->sum('totalSeconds');
            $totalActivity       = $items->sum('totalActivity');
            $dateActivityPercent = showAmount(
                $totalActivity / ($totalSeconds > 0 ? $totalSeconds : 1),
                currencyFormat: false
            );

            if ($dataType === "expanded") {
                // date header
                fputcsv($output, [showDateTime($date, 'Y-m-d')]);

                // Group records by user
                $sortedUsers = $items->groupBy('user_id')->sortByDesc(function ($userTracks) {
                    return $userTracks->sum('totalSeconds');
                });
                foreach ($sortedUsers as $userId => $userTracks) {
                    $user                = $userTracks->first()->user ?? null;
                    $userTotalSeconds    = $userTracks->sum('totalSeconds');
                    $userTotalActivity   = $userTracks->sum('totalActivity');
                    $userActivityPercent = showAmount(
                        $userTotalActivity / ($userTotalSeconds > 0 ? $userTotalSeconds : 1),
                        currencyFormat: false
                    );

                    // flag to ensure user's name shows only once per date
                    $firstProject = true;

                    // user's work by project
                    $sortedTracks = $userTracks->groupBy('project_id')->sortByDesc(function ($projectTracks) {
                        return $projectTracks->sum('totalSeconds');
                    });
                    foreach ($sortedTracks as $projectId => $projectTracks) {
                        $project      = $projectTracks->first()->project ?? null;
                        $projSeconds  = $projectTracks->sum('totalSeconds');
                        $projActivity = $projectTracks->sum('totalActivity');
                        $projPercent  = showAmount(
                            $projActivity / ($projSeconds > 0 ? $projSeconds : 1),
                            currencyFormat: false
                        );

                        $row = [
                            '',
                            $firstProject ? (toTitle($user->fullname) ?? 'N/A') : '',
                            $project->title ?? 'N/A',
                            formatSecondsToHoursMinutes($projSeconds),
                            $projPercent . '%',
                        ];

                        if (auth()->user()->isStaff()) {
                            unset($row[1]);
                        }

                        fputcsv($output, $row);

                        $firstProject = false;
                    }

                    if (!auth()->user()->isStaff()) {
                        // user total summary
                        fputcsv($output, [
                            '',
                            '',
                            'Total',
                            formatSecondsToHoursMinutes($userTotalSeconds),
                            $userActivityPercent . '%',
                        ]);
                    }

                    fputcsv($output, []); // Spacing between users
                }

                // date total summary
                fputcsv($output, [
                    '',
                    '',
                    'Date Total',
                    formatSecondsToHoursMinutes($totalSeconds),
                    $dateActivityPercent . '%',
                ]);
            } else {
                // collapsed version
                fputcsv($output, [
                    $date,
                    formatSecondsToHoursMinutes($totalSeconds),
                    $dateActivityPercent . '%',
                ]);
            }

            // separate dates
            fputcsv($output, []);
        }

        fclose($output);
        exit;
    }

    private function timeActivityMemberGroupDownloadToCsv($tracks, $totalWorkTime, $activityPercent, $dataType, $organization, $startDate, $endDate)
    {
        $start = $startDate ? $startDate->format('Y-m-d') : 'start';
        $end   = $endDate ? $endDate->format('Y-m-d') : 'end';

        $filename = "member_group_time_activity_{$start}_to_{$end}_" . date('H-i-s') . ".csv";

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');

        //summary
        fputcsv($output, ["Organization:", $organization->name]);
        fputcsv($output, [
            "Report Period:",
            !empty($startDate) && !empty($endDate)
            ? $startDate->format('M d, Y') . " - " . $endDate->format('M d, Y')
            : '',
        ]);
        fputcsv($output, ["Worked Time:", $totalWorkTime, "Average Activity:", $activityPercent]);
        fputcsv($output, []); // gap

        if ($dataType != 'expanded') {
            fputcsv($output, ['Member', 'Total Time', 'Activity']);

            $sortedUsers = $tracks->groupBy('user_id')->sortByDesc(function ($userTracks) {
                return $userTracks->sum('totalSeconds');
            });
            foreach ($sortedUsers as $userId => $userTracks) {
                $user            = $userTracks->first()->user ?? null;
                $totalSeconds    = $userTracks->sum('totalSeconds');
                $totalActivity   = $userTracks->sum('totalActivity');
                $activityPercent = showAmount(
                    $totalActivity / ($totalSeconds > 0 ? $totalSeconds : 1),
                    currencyFormat: false
                );

                fputcsv($output, [
                    toTitle($user->fullname) ?? 'N/A',
                    formatSecondsToHoursMinutes($totalSeconds),
                    $activityPercent . '%',
                ]);
            }
            fclose($output);
            exit;
        }

        // expanded
        fputcsv($output, ['Member', 'Date', 'Project', 'Total Time', 'Activity']);

        $sortedUsers = $tracks->groupBy('user_id')->sortByDesc(function ($userTracks) {
            return $userTracks->sum('totalSeconds');
        });
        foreach ($sortedUsers as $userId => $userTracks) {
            $user                = $userTracks->first()->user ?? null;
            $userTotalSeconds    = $userTracks->sum('totalSeconds');
            $userTotalActivity   = $userTracks->sum('totalActivity');
            $userActivityPercent = showAmount(
                $userTotalActivity / ($userTotalSeconds > 0 ? $userTotalSeconds : 1),
                currencyFormat: false
            );

            // user row
            fputcsv($output, [(toTitle($user->fullname) ?? 'N/A'), '', '', '', '']);

            // date loop
            foreach ($userTracks->groupBy('created_on') as $date => $dateTracks) {
                $dateTotalSeconds    = $dateTracks->sum('totalSeconds');
                $dateTotalActivity   = $dateTracks->sum('totalActivity');
                $dateActivityPercent = showAmount(
                    $dateTotalActivity / ($dateTotalSeconds > 0 ? $dateTotalSeconds : 1),
                    currencyFormat: false
                );

                // show date heading
                fputcsv($output, [
                    '',
                    showDateTime($date, 'Y-m-d'),
                    '',
                    '',
                    '',
                ]);

                // user date project loop
                $sortedProjects = $dateTracks->groupBy('project_id')->sortByDesc(function ($userTracks) {
                    return $userTracks->sum('totalSeconds');
                });
                foreach ($sortedProjects as $projectId => $projectTracks) {
                    $project             = $projectTracks->first()->project ?? null;
                    $projSeconds         = $projectTracks->sum('totalSeconds');
                    $projActivity        = $projectTracks->sum('totalActivity');
                    $projActivityPercent = showAmount(
                        $projActivity / ($projSeconds > 0 ? $projSeconds : 1),
                        currencyFormat: false
                    );

                    // add project
                    fputcsv($output, [
                        '',
                        '',
                        $project->title ?? 'N/A',
                        formatSecondsToHoursMinutes($projSeconds),
                        $projActivityPercent . '%',
                    ]);
                }

                // add total date time
                fputcsv($output, [
                    '',
                    '',
                    'Total',
                    formatSecondsToHoursMinutes($dateTotalSeconds),
                    $dateActivityPercent . '%',
                ]);

                fputcsv($output, []);
            }

            // User total row
            fputcsv($output, ['', '', 'Total', formatSecondsToHoursMinutes($userTotalSeconds), $userActivityPercent . '%']);
            fputcsv($output, []); //
        }

        fclose($output);
        exit;
    }

    private function timeActivityProjectGroupDownloadToCsv($tracks, $totalWorkTime, $activityPercent, $dataType, $organization, $startDate, $endDate)
    {
        $start = $startDate ? $startDate->format('Y-m-d') : 'start';
        $end   = $endDate ? $endDate->format('Y-m-d') : 'end';

        $filename = "project_group_time_activity_{$start}_to_{$end}_" . date('H-i-s') . ".csv";

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');

        //summary
        fputcsv($output, ["Organization:", $organization->name]);
        fputcsv($output, [
            "Report Period:",
            !empty($startDate) && !empty($endDate)
            ? $startDate->format('M d, Y') . " - " . $endDate->format('M d, Y')
            : '',
        ]);
        fputcsv($output, ["Worked Time:", $totalWorkTime, "Average Activity:", $activityPercent]);
        fputcsv($output, []); // gap

        if ($dataType != 'expanded') {
            fputcsv($output, ['Project', 'Total Time', 'Activity']);

            $sortedProjects = $tracks->groupBy('project_id')->sortByDesc(function ($projectTracks) {
                return $projectTracks->sum('totalSeconds');
            });
            foreach ($sortedProjects as $projectId => $projectTracks) {
                $project         = $projectTracks->first()->project ?? null;
                $totalSeconds    = $projectTracks->sum('totalSeconds');
                $totalActivity   = $projectTracks->sum('totalActivity');
                $activityPercent = showAmount(
                    $totalActivity / ($totalSeconds > 0 ? $totalSeconds : 1),
                    currencyFormat: false
                );

                fputcsv($output, [
                    toTitle($project->title) ?? 'N/A',
                    formatSecondsToHoursMinutes($totalSeconds),
                    $activityPercent . '%',
                ]);
            }
            fclose($output);
            exit;
        }

        // expanded
        $labels = ['Project', 'Date', 'Member', 'Total Time', 'Activity'];
        if (auth()->user()->isStaff()) {
            unset($labels[2]);
        }

        fputcsv($output, $labels);

        $sortedProjects = $tracks->groupBy('project_id')->sortByDesc(function ($projectTracks) {
            return $projectTracks->sum('totalSeconds');
        });
        foreach ($sortedProjects as $projectId => $projectTracks) {
            $project             = $projectTracks->first()->project ?? null;
            $projectTotalSeconds = $projectTracks->sum('totalSeconds');
            $projectTotalActivity   = $projectTracks->sum('totalActivity');
            $projectActivityPercent = showAmount(
                $projectTotalActivity / ($projectTotalSeconds > 0 ? $projectTotalSeconds : 1),
                currencyFormat: false
            );

            // project row
            $projectRow = [(toTitle($project->title) ?? 'N/A'), '', '', '', ''];
            if (auth()->user()->isStaff()) {
                unset($projectRow[1]);
            }
            fputcsv($output, $projectRow);

            // date loop
            foreach ($projectTracks->groupBy('created_on') as $date => $dateTracks) {
                $dateTotalSeconds    = $dateTracks->sum('totalSeconds');
                $dateTotalActivity   = $dateTracks->sum('totalActivity');
                $dateActivityPercent = showAmount(
                    $dateTotalActivity / ($dateTotalSeconds > 0 ? $dateTotalSeconds : 1),
                    currencyFormat: false
                );

                // user date project loop
                $sortedUsers = $dateTracks->groupBy('user_id')->sortByDesc(function ($userTracks) {
                    return $userTracks->sum('totalSeconds');
                });
                foreach ($sortedUsers as $userId => $userTracks) {
                    $user                = $userTracks->first()->user ?? null;
                    $userSeconds         = $userTracks->sum('totalSeconds');
                    $userActivity        = $userTracks->sum('totalActivity');
                    $userActivityPercent = showAmount(
                        $userActivity / ($userSeconds > 0 ? $userSeconds : 1),
                        currencyFormat: false
                    );

                    // add
                    $memberRows = [
                        '',
                        showDateTime($date, 'Y-m-d'),
                        $user->fullname ?? 'N/A',
                        formatSecondsToHoursMinutes($userSeconds),
                        $userActivityPercent . '%',
                    ];
                    if (auth()->user()->isStaff()) {
                        unset($memberRows[2]);
                    }
                    fputcsv($output, $memberRows);
                }

                // add total date time
                $projectTotalRow = [
                    '',
                    '',
                    'Total',
                    formatSecondsToHoursMinutes($dateTotalSeconds),
                    $dateActivityPercent . '%',
                ];
                if (auth()->user()->isStaff()) {
                    unset($projectTotalRow[1]);
                }
                fputcsv($output, $projectTotalRow);
                fputcsv($output, []);
            }

            // User total row
            $projectTotalRow = ['', '', 'Total', formatSecondsToHoursMinutes($projectTotalSeconds), $projectActivityPercent . '%'];
            if (auth()->user()->isStaff()) {
                unset($projectTotalRow[1]);
            }
            fputcsv($output, $projectTotalRow);
            fputcsv($output, []); //
        }

        fclose($output);
        exit;
    }
}

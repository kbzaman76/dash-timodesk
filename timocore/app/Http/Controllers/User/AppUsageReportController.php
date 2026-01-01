<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\App;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AppUsageReportController extends Controller
{
    public function appUsage()
    {
        $pageTitle = 'App Usage';

        $members = User::where('organization_id', organizationId())->me()->orderBy('fullname')->get();

        $date =  last30Days('m/d/Y');
        $dateRange = $date['defaultDateRange'];

        return view('Template::user.report.app_usage', compact('pageTitle', 'members', 'dateRange'));
    }

    public function loadAppUsage(Request $request)
    {
        [$startDate, $endDate] = $this->resolveAppUsageRange($request->date);

        $uid  = $request->input('user', 0);
        $user = User::where('organization_id', organizationId())->where('uid', $uid)->first();
        $userId = $user?->id ?? 0;
        $groupBy = $request->input('group_by', 'date');
        $level   = $request->input('level', 'root');

        if ($request->boolean('is_export') && !$request->ajax()) {
            if($request->data_type == 'expanded' && $request->export_type == 'pdf' && $startDate->diffInDays($endDate) > 31) {
                $notify[] = ['error', 'The expanded PDF can only be exported for up to 1 month.'];
                return back()->withNotify($notify);
            }

            $apps = $this->appUsageExportQuery($startDate, $endDate, $userId)->get();

            $dataType   = $request->input('data_type', 'collapsed');
            $exportType = $request->input('export_type', 'pdf');

            return $this->handleAppUsageExport($apps, $groupBy, $dataType, $exportType, $startDate, $endDate);
        }

        if ($groupBy === 'member') {
            $view = $this->renderMemberGrouping($level, $request, $startDate, $endDate, $userId);
        } elseif ($groupBy === 'app') {
            $view = $this->renderAppGrouping($level, $request, $startDate, $endDate, $userId);
        } else {
            $view = $this->renderDateGrouping($level, $request, $startDate, $endDate, $userId);
        }

        return response()->json([
            'view' => $view,
        ]);
    }

    private function resolveAppUsageRange(?string $dateRange): array
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

    private function appUsageExportQuery(Carbon $startDate, Carbon $endDate, int $userId)
    {
        return App::with('user:id,fullname,image')
            ->whereBetweenOrg('started_at', $startDate, $endDate)
            ->mine()
            ->when($userId > 0, function ($query) use ($userId) {
                return $query->where('user_id', $userId);
            })
            ->where('org_id', organizationId())
            ->selectRaw('SUM(session_time) AS totalSeconds, app_name')
            ->selectTzFormat(format: '%d-%m-%Y', alias: 'created_on')
            ->addSelect('user_id')
            ->groupBy('created_on')
            ->groupBy('user_id')
            ->groupBy('app_name')
            ->orderBy('created_on', 'DESC');
    }

    private function buildAppUsageQuery(Carbon $startDate, Carbon $endDate, int $userId = 0, bool $withUser = false)
    {
        $builder = App::query()
            ->where('org_id', organizationId())
            ->whereBetweenOrg('started_at', $startDate, $endDate)
            ->mine()
            ->when($userId > 0, function ($query) use ($userId) {
                return $query->where('user_id', $userId);
            });

        if ($withUser) {
            $builder->with('user:id,fullname,image');
        }

        return $builder;
    }

    private function renderDateGrouping(string $level, Request $request, Carbon $startDate, Carbon $endDate, int $userId): string
    {
        if ($level === 'date_apps') {
            $dateKey = $request->input('date_key');

            [$dayStart, $dayEnd] = $this->resolveScopedDayRange($dateKey, $startDate, $endDate);

            $apps = $this->buildAppUsageQuery($dayStart, $dayEnd, $userId)
                ->select('app_name')
                ->selectRaw('SUM(session_time) AS totalSeconds')
                ->groupBy('app_name')
                ->orderByDesc('totalSeconds')
                ->get();

            return view('Template::user.report.partials.app_usage_date_apps', compact('apps', 'dateKey'))->render();
        }

        if ($level === 'date_app_members') {
            $dateKey = $request->input('date_key');
            $appName = $request->input('app_name');

            [$dayStart, $dayEnd] = $this->resolveScopedDayRange($dateKey, $startDate, $endDate);

            $members = $this->buildAppUsageQuery($dayStart, $dayEnd, $userId, true)
                ->where('app_name', $appName)
                ->select('user_id')
                ->selectRaw('SUM(session_time) AS totalSeconds')
                ->groupBy('user_id')
                ->orderByDesc('totalSeconds')
                ->get();

            return view('Template::user.report.partials.app_usage_date_members', compact('members'))->render();
        }

        $dates = $this->buildAppUsageQuery($startDate, $endDate, $userId)
            ->selectTzFormat(format: '%Y-%m-%d', alias: 'usage_date')
            ->selectRaw('SUM(session_time) AS totalSeconds')
            ->selectRaw('COUNT(DISTINCT user_id) AS totalUsers')
            ->groupBy('usage_date')
            ->orderByDesc('usage_date')
            ->get();

        return view('Template::user.report.app_usage_date_group', compact('dates'))->render();
    }

    private function renderAppGrouping(string $level, Request $request, Carbon $startDate, Carbon $endDate, int $userId): string
    {
        if ($level === 'app_members') {
            $appName = $request->input('app_name');

            $members = $this->buildAppUsageQuery($startDate, $endDate, $userId, true)
                ->where('app_name', $appName)
                ->select('user_id')
                ->selectRaw('SUM(session_time) AS totalSeconds')
                ->groupBy('user_id')
                ->orderByDesc('totalSeconds')
                ->get();

            return view('Template::user.report.partials.app_usage_app_members', compact('members', 'appName'))->render();
        }

        if ($level === 'app_member_dates') {
            $appName  = $request->input('app_name');
            $memberId = (int) $request->input('member_id');

            $dates = $this->buildAppUsageQuery($startDate, $endDate, $userId)
                ->where('app_name', $appName)
                ->where('user_id', $memberId)
                ->selectTzFormat(format: '%Y-%m-%d', alias: 'usage_date')
                ->selectRaw('SUM(session_time) AS totalSeconds')
                ->groupBy('usage_date')
                ->orderByDesc('usage_date')
                ->get();

            return view('Template::user.report.partials.app_usage_app_member_dates', compact('dates'))->render();
        }

        $apps = $this->buildAppUsageQuery($startDate, $endDate, $userId)
            ->select('app_name')
            ->selectRaw('SUM(session_time) AS totalSeconds')
            ->groupBy('app_name')
            ->orderByDesc('totalSeconds')
            ->get();

        return view('Template::user.report.app_usage_app_group', compact('apps'))->render();
    }

    private function renderMemberGrouping(string $level, Request $request, Carbon $startDate, Carbon $endDate, int $userId): string
    {
        if ($level === 'member_apps') {
            $memberId = (int) $request->input('member_id');

            $apps = $this->buildAppUsageQuery($startDate, $endDate, $userId)
                ->where('user_id', $memberId)
                ->select('app_name')
                ->selectRaw('SUM(session_time) AS totalSeconds')
                ->groupBy('app_name')
                ->orderByDesc('totalSeconds')
                ->get();

            return view('Template::user.report.partials.app_usage_member_apps', compact('apps', 'memberId'))->render();
        }

        if ($level === 'member_app_dates') {
            $memberId = (int) $request->input('member_id');
            $appName  = $request->input('app_name');

            $dates = $this->buildAppUsageQuery($startDate, $endDate, $userId)
                ->where('user_id', $memberId)
                ->where('app_name', $appName)
                ->selectTzFormat(format: '%Y-%m-%d', alias: 'usage_date')
                ->selectRaw('SUM(session_time) AS totalSeconds')
                ->groupBy('usage_date')
                ->orderByDesc('usage_date')
                ->get();

            return view('Template::user.report.partials.app_usage_member_app_dates', compact('dates'))->render();
        }

        $members = $this->buildAppUsageQuery($startDate, $endDate, $userId, true)
            ->select('user_id')
            ->selectRaw('SUM(session_time) AS totalSeconds')
            ->groupBy('user_id')
            ->orderByDesc('totalSeconds')
            ->get();

        return view('Template::user.report.app_usage_member_group', compact('members'))->render();
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

    private function handleAppUsageExport($apps, string $groupBy, string $dataType, string $exportType, Carbon $startDate, Carbon $endDate)
    {
        $organization = myOrganization();
        $startLabel   = $startDate ? $startDate->format('Y-m-d') : now()->format('Y-m-d');
        $endLabel     = $endDate ? $endDate->format('Y-m-d') : now()->format('Y-m-d');

        if ($exportType === 'pdf') {
            $view = ($groupBy == 'date') ? 'app_usage_date_group_pdf' : (($groupBy == 'app') ? 'app_usage_app_group_pdf' : 'app_usage_member_group_pdf');

            $pdf = Pdf::loadView('Template::user.report.' . $view, compact('apps', 'groupBy', 'dataType', 'organization', 'startDate', 'endDate') + ['format' => 'pdf']);
            $pdf->setPaper('A4', 'portrait');
            $pdf->setOptions([
                'isRemoteEnabled' => true,
                'chroot'          => realpath(base_path('..')),
                'fontDir'         => storage_path('fonts'),
                'fontCache'       => storage_path('fonts'),
            ]);

            return $pdf->download('app-usages-report-from-'.$startLabel.'-to-'.$endLabel.'.pdf');
        }

        return $this->downloadAppUsageCsv($apps, $groupBy, $dataType, $startDate, $endDate, $organization);
    }

    private function downloadAppUsageCsv($apps, string $groupBy, string $dataType, Carbon $startDate, Carbon $endDate, $organization)
    {
        $filename = "app-usage-{$startDate->format('Ymd')}-{$endDate->format('Ymd')}.csv";

        $response = response()->streamDownload(function () use ($apps, $groupBy, $dataType, $startDate, $endDate, $organization) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Organization', $organization->name ?? '']);
            if (!empty($startDate) && !empty($endDate)) {
                fputcsv($handle, [
                    'Period',
                    $startDate->format('M d, Y') . ' - ' . $endDate->format('M d, Y'),
                ]);
            }
            fputcsv($handle, ['Total Usage', formatSecondsToHoursMinutes($apps->sum('totalSeconds'))]);
            fputcsv($handle, []); // spacer

            if ($groupBy === 'member') {
                $this->writeAppUsageMemberCsv($handle, $apps, $dataType);
            } elseif ($groupBy === 'app') {
                $this->writeAppUsageAppCsv($handle, $apps, $dataType);
            } else {
                $this->writeAppUsageDateCsv($handle, $apps, $dataType);
            }


            fclose($handle);
        }, $filename, [
            'Content-Type'        => 'text/csv',
            'Cache-Control'       => 'no-store, no-cache',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);

        return $response;
    }

    private function writeAppUsageDateCsv($handle, $apps, string $dataType)
    {
        if ($dataType === 'collapsed') {
            $labels = ['Date', 'Members', 'Apps', 'Total Time'];
        } else {
            $labels = ['Date', 'Member', 'App', 'Total Time'];
        }
        if (auth()->user()->isStaff()) {
            unset($labels[1]);
        }
        fputcsv($handle, $labels);

        foreach ($apps->groupBy('created_on') as $date => $items) {
            if ($dataType === 'collapsed') {
                $memberCount  = $items->groupBy('user_id')->count();
                $appCount     = $items->groupBy('app_name')->count();
                $totalSeconds = $items->sum('totalSeconds');
                $row          = [showDateTime($date, 'Y-m-d'), $memberCount, $appCount, formatSecondsToHoursMinutes($totalSeconds)];
                if (auth()->user()->isStaff()) {
                    unset($row[1]);
                }
                fputcsv($handle, $row);
            } else {
                $sortedUsers = $items->groupBy('user_id')->sortByDesc(function ($userTracks) {
                    return $userTracks->sum('totalSeconds');
                });
                foreach ($sortedUsers as $userApps) {
                    $user = optional($userApps->first())->user;
                    foreach ($userApps->groupBy('app_name') as $appName => $appItems) {
                        $row = [
                            showDateTime($date, 'Y-m-d'),
                            toTitle($user->fullname) ?? 'N/A',
                            $appName,
                            formatSecondsToHoursMinutes($appItems->sum('totalSeconds')),
                        ];
                        if (auth()->user()->isStaff()) {
                            unset($row[1]);
                        }
                        fputcsv($handle, $row);
                    }
                }
            }
        }
    }

    private function writeAppUsageMemberCsv($handle, $apps, string $dataType)
    {
        if ($dataType === 'collapsed') {
            fputcsv($handle, ['Member', 'Apps Used', 'Total Time']);
        } else {
            fputcsv($handle, ['Member', 'App', 'Date', 'Total Time']);
        }

        $sortedUsers = $apps->groupBy('user_id')->sortByDesc(function ($userTracks) {
            return $userTracks->sum('totalSeconds');
        });
        foreach ($sortedUsers as $userApps) {
            $user = optional($userApps->first())->user;

            if ($dataType === 'collapsed') {
                fputcsv($handle, [
                    toTitle($user->fullname) ?? 'N/A',
                    $userApps->groupBy('app_name')->count(),
                    formatSecondsToHoursMinutes($userApps->sum('totalSeconds')),
                ]);
            } else {
                $sortedUserApps = $userApps->groupBy('app_name')->sortByDesc(function ($userTracks) {
                    return $userTracks->sum('totalSeconds');
                });
                foreach ($sortedUserApps as $appName => $appEntries) {
                    foreach ($appEntries->groupBy('created_on') as $date => $dateEntries) {
                        fputcsv($handle, [
                            toTitle($user->fullname) ?? 'N/A',
                            $appName,
                            showDateTime($date, 'Y-m-d'),
                            formatSecondsToHoursMinutes($dateEntries->sum('totalSeconds')),
                        ]);
                    }
                }
            }
        }
    }

    private function writeAppUsageAppCsv($handle, $apps, string $dataType)
    {
        if ($dataType === 'collapsed') {
            $labels = ['App', 'Members', 'Total Time'];
        } else {
            $labels = ['App', 'Member', 'Date', 'Total Time'];
        }


        if (auth()->user()->isStaff()) {
            unset($labels[1]);
        }
        fputcsv($handle, $labels);

        $sortedApps = $apps->groupBy('app_name')->sortByDesc(function ($userTracks) {
            return $userTracks->sum('totalSeconds');
        });
        foreach ($sortedApps as $appName => $appEntries) {
            if ($dataType === 'collapsed') {
                $row = [
                    $appName,
                    $appEntries->groupBy('user_id')->count(),
                    formatSecondsToHoursMinutes($appEntries->sum('totalSeconds')),
                ];
                if (auth()->user()->isStaff()) {
                   unset($row[1]);
                }
                fputcsv($handle, $row);
            } else {
                $sortedAppUser = $appEntries->groupBy('user_id')->sortByDesc(function ($userTracks) {
                    return $userTracks->sum('totalSeconds');
                });
                foreach ($sortedAppUser as $userEntries) {
                    $user = optional($userEntries->first())->user;
                    foreach ($userEntries->groupBy('created_on') as $date => $dateEntries) {
                        $row = [
                            $appName,
                            toTitle($user->fullname) ?? 'N/A',
                            showDateTime($date, 'Y-m-d'),
                            formatSecondsToHoursMinutes($dateEntries->sum('totalSeconds')),
                        ];
                        if (auth()->user()->isStaff()) {
                            unset($row[1]);
                        }
                        fputcsv($handle, $row);
                    }
                }
            }
        }
    }
}

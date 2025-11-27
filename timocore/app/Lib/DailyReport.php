<?php

namespace App\Lib;

use App\Models\App as AppUsage;
use App\Models\Track;
use Carbon\Carbon;

class DailyReport
{
    public function generateDailyReport($user,$organization)
    {
        $dailyReport = $this->commonReport($user->id,$organization);
        if ($dailyReport) {
            $totalWorked     = $dailyReport['totalWorked'];
            $activityPercent = $dailyReport['activityPercent'];
            $totalProject    = $dailyReport['totalProject'];
            $totalTasks      = $dailyReport['totalTasks'];
            $topProjects     = $dailyReport['topProjects'];
            $topTasks        = $dailyReport['topTasks'];
            $topApps         = $dailyReport['topApps'];
            $reportDate      = Carbon::parse($organization->last_summary_mail)->subDay();
            
            $view = view('Template::mail.staff_daily_summary', compact('user', 'organization', 'totalWorked', 'activityPercent', 'totalProject', 'totalTasks', 'topProjects', 'topTasks', 'topApps', 'reportDate'));

            $html = $view->render();
            $sendEmailLater = new SendEmailLater();
            $sendEmailLater->notifyWithQueue($user, 'STAFF_DAILY_SUMMARY', [
                'html' => $html,
            ]);
        }
    }

    public function generateDailyReportForOrganization($user, $organization)
    {
        $dailyReport = $this->commonReport(organization: $organization);
        if ($dailyReport) {
            $totalWorked     = $dailyReport['totalWorked'];
            $activityPercent = $dailyReport['activityPercent'];
            $totalProject    = $dailyReport['totalProject'];
            $totalTasks      = $dailyReport['totalTasks'];
            $topProjects     = $dailyReport['topProjects'];
            $topTasks        = $dailyReport['topTasks'];
            $topMembers      = $dailyReport['topMembers'];
            $memberCount      = $dailyReport['memberCount'];
            $topApps         = $dailyReport['topApps'];
            $reportDate      = Carbon::parse($organization->last_summary_mail)->subDay();
            
            $view = view('Template::mail.organization_daily_summary', compact('user', 'totalWorked', 'activityPercent', 'totalProject', 'totalTasks', 'topProjects', 'topTasks', 'topMembers','organization','memberCount', 'topApps', 'reportDate'));

            $html = $view->render();
            
            $sendEmailLater = new SendEmailLater();
            $sendEmailLater->notifyWithQueue($user, 'ORGANIZATION_DAILY_SUMMARY', [
                'html' => $html,
                'organization_name'=>$organization->name
            ]);
        }
    }

    private function commonReport($userId = null, $organization = null)
    {
        $organizationId = $organization->id;
        $currentTime = now($organization->timezone)->subDay();
        $startDate      = $currentTime->clone()->startOfDay()->setTimezone(config('app.timezone'));
        $endDate      =  $currentTime->clone()->endOfDay()->setTimezone(config('app.timezone'));
        $baseQuery      = Track::whereBetween('started_at', [$startDate,$endDate]);
        if ($userId) {
            $baseQuery = $baseQuery->where('user_id', $userId);
        } else {
            $baseQuery = $baseQuery->where('organization_id', $organizationId);
        }

        $tracks = (clone $baseQuery)->selectRaw('project_id, SUM(time_in_seconds) AS totalSeconds, SUM(overall_activity) AS totalActivity, started_at')
            ->groupBy('project_id')
            ->get();
            
        if ($tracks->isEmpty()) {
            return;
        }
        
        $totalWorked     = $tracks->sum('totalSeconds');
        $totalActivity   = $tracks->sum('totalActivity');
        $activityPercent = $totalWorked > 0 ? (int) (($totalActivity / $totalWorked)) : 0;
        $totalProject    = (clone $baseQuery)->distinct('project_id')->count('project_id');
        $totalTasks      = (clone $baseQuery)->where('task_id', '>', 0)->distinct('task_id')->count('task_id');

        $topProjects = (clone $baseQuery)->with('project:id,title')->selectRaw('project_id, SUM(time_in_seconds) as totalSeconds, SUM(overall_activity) as totalActivity')
            ->groupBy('project_id')
            ->orderByDesc('totalSeconds')
            ->limit(5)
            ->get();

        $topTasks = (clone $baseQuery)->with('task:id,title')->selectRaw('task_id, SUM(time_in_seconds) as totalSeconds, SUM(overall_activity) as totalActivity')
            ->groupBy('task_id')
            ->orderByDesc('totalSeconds')
            ->limit(5)
            ->get();

        if ($organizationId) {
            $topMembers = (clone $baseQuery)->with('user:id,fullname')->selectRaw('user_id, SUM(time_in_seconds) as totalSeconds, SUM(overall_activity) as totalActivity')
                ->groupBy('user_id')
                ->orderByDesc('totalSeconds')
                ->limit(5)
                ->get();
        } else {
            $topMembers = null;
        }
        
        $memberCount = (clone $baseQuery)->distinct('user_id')->count('user_id');
        $appsQuery = AppUsage::query()
            ->whereBetween('started_at', [$startDate,$endDate])
            ->when($userId, function ($query) use ($userId) {
                return $query->where('user_id', $userId);
            }, function ($query) use ($organizationId) {
                return $query->where('org_id', $organizationId);
            });

        $topApps = (clone $appsQuery)
            ->select('app_name')
            ->selectRaw('SUM(session_time) AS totalSeconds')
            ->groupBy('app_name')
            ->orderByDesc('totalSeconds')
            ->limit(5)
            ->get();
        
        return [
            'totalWorked'     => $totalWorked,
            'activityPercent' => $activityPercent,
            'totalProject'    => $totalProject,
            'totalTasks'      => $totalTasks,
            'topProjects'     => $topProjects,
            'topTasks'        => $topTasks,
            'topMembers'      => $topMembers,
            'topApps'         => $topApps,
            'memberCount'     => $memberCount
        ];
    }

}

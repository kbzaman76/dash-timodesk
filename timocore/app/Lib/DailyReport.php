<?php

namespace App\Lib;

use App\Models\Track;

class DailyReport
{
    public function generateDailyReport($user,$organization)
    {
        $dailyReport = $this->commonReport($user->id,$organization);

        if ($dailyReport) {
            $totalWorked     = $dailyReport['totalWorked'];
            $activityPercent = $dailyReport['activityPercent'];
            $totalProject    = $dailyReport['totalProject'];
            $topProjects     = $dailyReport['topProjects'];
            $topTasks        = $dailyReport['topTasks'];

            $view = view('Template::mail.staff_daily_summary', compact('user', 'totalWorked', 'activityPercent', 'totalProject', 'topProjects', 'topTasks'));

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
            $topProjects     = $dailyReport['topProjects'];
            $topTasks        = $dailyReport['topTasks'];
            $topMembers      = $dailyReport['topMembers'];

            $view = view('Template::mail.organization_daily_summary', compact('user', 'totalWorked', 'activityPercent', 'totalProject', 'topProjects', 'topTasks', 'topMembers'));

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
        $yesterday      = now()->subDay();
        $baseQuery      = Track::whereDateOrg('started_at', $yesterday, $organization->timezone);

        if ($userId) {
            $baseQuery = $baseQuery->where('user_id', $userId);
        } else {
            $baseQuery = $baseQuery->where('organization_id', $organizationId);
        }

        $tracks = (clone $baseQuery)->selectRaw('project_id, SUM(time_in_seconds) AS totalSeconds, SUM(overall_activity) AS totalActivity')
            ->groupBy('project_id')
            ->get();

        if ($tracks->isEmpty()) {
            return;
        }

        $totalWorked     = $tracks->sum('totalSeconds');
        $totalActivity   = $tracks->sum('totalActivity');
        $activityPercent = $totalWorked > 0 ? (int) (($totalActivity / $totalWorked) * 100) : 0;
        $totalProject    = $tracks->count();

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

        return [
            'totalWorked'     => $totalWorked,
            'activityPercent' => $activityPercent,
            'totalProject'    => $totalProject,
            'topProjects'     => $topProjects,
            'topTasks'        => $topTasks,
            'topMembers'      => $topMembers,
        ];
    }

}

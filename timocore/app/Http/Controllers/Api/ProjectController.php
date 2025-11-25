<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

class ProjectController extends Controller
{
    public function projects()
    {
        $user = auth()->user();

        request()->merge(['today' => true]);

        $todayTimes = $user->tracks()->whereDateOrg('started_at')->sum('time_in_seconds');

        $projects = $user->projects()
            ->with([
                'tasks' => function ($query) {
                $userId = auth()->id();

                $query
                    ->whereHas('users', function ($q) use ($userId) {
                        $q->where('users.id', $userId);
                    })
                    // latest track started_at (optionally also filtered by user)
                    ->withMax([
                        'tracks as last_started_at' => function ($q) use ($userId) {
                            $q->where('user_id', $userId);
                        },
                    ], 'started_at')
                    ->orderByDesc('last_started_at');
            },
            ])
            ->get()
            ->map(function ($project) use ($user) {
                $project->total_work_time = $project->tasks->sum('total_work_time');
                $project->recent_activity_at = $project->tracks()->latest()->first()->started_at ?? null;
                return $project;
            })
            ->sortByDesc('recent_activity_at')
            ->values();


        return responseSuccess('user_projects', [], [
            'projects' => $projects,
            'today_times' => $todayTimes,
        ]);
    }

}

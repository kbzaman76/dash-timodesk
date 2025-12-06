<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\App;
use App\Models\Screenshot;
use App\Models\Track;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ActivityController extends Controller
{

    public function screenshots($uid = null)
    {
        $pageTitle = 'Screenshots';

        $members        = User::where('organization_id', organizationId())->me()->orderBy('fullname')->get();

        $memberId = null;
        if ($uid) {
            $filteredMember = User::where('organization_id', auth()->user()->organization_id)->me()->where('uid',$uid)->first();
            if ($filteredMember) {
                $memberId = $filteredMember->id;
            }
        }

        return view('Template::user.activity.screenshots.index', compact('pageTitle', 'members', 'memberId'));
    }

    public function loadScreenshots(Request $request)
    {
        $uid    = $request->integer('user');

        $startDate = Carbon::parse($request->input('date', now()->toDateString()))->startOfDay();
        $endDate = $startDate->clone()->endOfDay();

        $member = User::where('uid',$uid)->where('organization_id', organizationId())->first();

        $summary = $this->buildScreenshotSummary($startDate, $endDate, $member);

        if ($request->mode == 'all') {
            return $this->getAllScreenshots($startDate, $endDate ,$member, $summary);
        } else {
            return $this->getTenMinuteScreenshots($startDate, $endDate, $member, $summary);
        }
    }

    private function getTenMinuteScreenshots($startDate, $endDate, $member, $summary = [])
    {
        $tracks = Track::where('organization_id', organizationId())
            ->mine()
            ->when($member, function ($q) use ($member) {
                $q->where('user_id', $member->id);
            })
            ->whereBetweenOrg('started_at', $startDate, $endDate)
            ->orderBy('started_at')
            ->get();

        $slices = [];

        if ($tracks->isNotEmpty()) {
            $firstStart     = Carbon::parse($tracks->first()->started_at);
            $lastStart      = Carbon::parse($tracks->sortByDesc('started_at')->first()->started_at);
            $sliceStartBase = $firstStart->copy()->startOfHour();
            $cursor = $sliceStartBase->copy();

            while ($cursor->lt($lastStart)) {
                $sliceStart = $cursor->copy();
                $sliceEnd   = $cursor->copy()->addHour();

                $blocks      = [];
                $blockCursor = $sliceStart->copy();

                while ($blockCursor->lt($sliceEnd)) {
                    $blockStart = $blockCursor->copy();
                    $blockEnd   = $blockCursor->copy()->addMinutes(10)->subSecond();
                    
                    $blockTrack      = $tracks->where('started_at', '>=', $blockStart)->where('started_at', '<', $blockEnd);
                    $screenshotQuery = Screenshot::mine()->whereBetween('taken_at', [$blockStart, $blockEnd]);

                    if ($member) {
                        $screenshotQuery->where('user_id', $member->id);
                    }

                    $screenshotCount = (clone $screenshotQuery)->count();
                    $firstScreenshot = $screenshotQuery->first();
                    $totalSecond     = $blockTrack->sum('time_in_seconds');
                    
                    $blocks[] = [
                        'start'       => $blockStart->format('g:i A'),
                        'end'         => $blockEnd->format('g:i A'),
                        'date'        => $blockStart->format('m/d/Y'),
                        'has_tracks'  => $blockTrack->count() > 0 ? true : false,
                        'total_times' => $totalSecond,
                        'ss_count'    => $screenshotCount,
                        'screenshot'  => $firstScreenshot ?? null,
                        'activity'    => $totalSecond > 0 ? (int) ($blockTrack->sum('overall_activity') / $totalSecond) : 0,
                    ];

                    $blockCursor->addMinutes(10);
                }

                if (collect($blocks)->sum('total_times') > 0) {
                    $slices[] = [
                        'start'       => $sliceStart->format('g A'),
                        'end'         => $sliceEnd->format('g A'),
                        'total_times' => collect($blocks)->sum('total_times'),
                        'blocks'      => $blocks,
                    ];
                }

                $cursor->addHour();
            }
        }
        
        return response()->json([
            'view' => view('Template::user.activity.screenshots._grid', compact('slices', 'member'))->render(),
            'summary' => $summary,
        ]);
    }

    private function getAllScreenshots($startDate, $endDate, $member, $summary = [])
    {
        $tracks = Track::where('organization_id', organizationId())
            ->mine()
            ->when($member, function ($q) use ($member) {
                $q->where('user_id', $member->id);
            })
            ->whereBetweenOrg('started_at', $startDate, $endDate)
            ->orderBy('started_at')
            ->get();

        $slices = [];

        if ($tracks->isNotEmpty()) {
            $firstStart = Carbon::parse($tracks->first()->started_at);
            $lastStart  = Carbon::parse($tracks->last()->started_at);

            $cursor = $firstStart->copy()->startOfHour();
            $now    = Carbon::now();

            while ($cursor->lt($lastStart) && $cursor->lte($now)) {
                $sliceStart = $cursor->copy();
                $sliceEnd   = $cursor->copy()->addHour();

                $hourTracks = $tracks->filter(function ($t) use ($sliceStart, $sliceEnd) {
                    return Carbon::parse($t->started_at)->between($sliceStart, $sliceEnd->clone()->subSecond());
                });

                $totalTimes  = $hourTracks->sum('time_in_seconds');
                $ssCount     = $hourTracks->sum(fn($t) => $t->screenshots()->count());
                $screenshots = $hourTracks->flatMap(function ($t) {
                    return $t->screenshots->load('project');
                })->filter();

                $activity = $totalTimes > 0
                ? (int) $hourTracks->sum('overall_activity') / $totalTimes
                : 0;

                if ($totalTimes > 0) {
                    $slices[] = [
                        'start'       => $sliceStart->format('g A'),
                        'end'         => $sliceEnd->format('g A'),
                        'total_times' => $totalTimes,
                        'ss_count'    => $ssCount,
                        'screenshots' => $screenshots,
                        'activity'    => $activity,
                    ];
                }

                $cursor->addHour();
            }
        }

        return response()->json([
            'view' => view('Template::user.activity.screenshots.all', compact('slices', 'member'))->render(),
            'summary' => $summary,
        ]);
    }

    public function loadSliceScreenshots(Request $request)
    {
        $dateInput = $request->date;
        $timeStart = $request->start;
        if (str_contains($dateInput, '-')) {
            [$start]  = array_map('trim', explode('-', $dateInput));
            $dayStart = Carbon::createFromFormat('m/d/Y', $start)->startOfDay();
        } else {
            $dayStart = Carbon::createFromFormat('m/d/Y', trim($dateInput))->startOfDay();
        }

        $startTime = Carbon::createFromFormat('m/d/Y h:i A', $dayStart->format('m/d/Y') . ' ' . $timeStart);
        $endTime   = $startTime->copy()->addMinutes(10)->subSecond();

        $screenshots = Screenshot::mine()->where('organization_id', organizationId())->whereBetween('taken_at', [$startTime, $endTime]);

        $member = User::where('uid',$request->user)->where('organization_id', organizationId())->first();

        if ($member) {
            $screenshots->where('user_id', $member->id);
        }

        $screenshots = $screenshots->with('project:id,title')->get(['id', 'project_id', 'src', 'taken_at', 'file_storage_id']);

        $screenshots->each->append('url')->each(function($ss){
            $ss->taken_at = showDateTime($ss->taken_at);
            return $ss;
        });

        return response()->json($screenshots);
    }

    private function buildScreenshotSummary(Carbon $startDate, Carbon $endDate, $member): array
    {
        $tracks = Track::where('organization_id', organizationId())
            ->mine()
            ->when($member, function ($q) use ($member) {
                $q->where('user_id', $member->id);
            })
            ->whereBetweenOrg('started_at', $startDate, $endDate)
            ->get();

        $totalSeconds = (int) $tracks->sum('time_in_seconds');
        $avgActivity = $totalSeconds > 0
            ? (int) round($tracks->sum('overall_activity') / $totalSeconds)
            : 0;

        $taskCount = $tracks->pluck('task_id')->filter()->unique()->count();

        $screenshotCount = Screenshot::mine()
            ->where('organization_id', organizationId())
            ->whereBetween('taken_at', [$startDate, $endDate])
            ->when($member, function ($q) use ($member) {
                $q->where('user_id', $member->id);
            })
            ->count();

        $topApps = App::query()
            ->mine()
            ->where('org_id', organizationId())
            ->whereBetweenOrg('started_at', $startDate, $endDate)
            ->when($member, function ($q) use ($member) {
                $q->where('user_id', $member->id);
            })
            ->select('app_name')
            ->selectRaw('SUM(session_time) as totalSeconds')
            ->groupBy('app_name')
            ->orderByDesc('totalSeconds')
            ->limit(4)
            ->get();

        return [
            'work_time'        => $this->formatToHoursMinutes($totalSeconds),
            'work_seconds'     => $totalSeconds,
            'avg_activity'     => $avgActivity,
            'avg_activity_pct' => $avgActivity . '%',
            'screenshot_count' => $screenshotCount,
            'task_count'       => $taskCount,
            'top_apps'         => $topApps->map(function ($app) {
                $seconds = (int) $app->totalSeconds;
                return [
                    'name'          => $app->app_name,
                    'total_seconds' => $seconds,
                    'app_icon'      => asset('assets/images/apps/'.getApps($app->app_name).'.png'),
                    'display'       => $this->formatToHoursMinutes($seconds),
                ];
            })->values(),
        ];
    }

    private function formatToHoursMinutes(int $seconds): string
    {
        $hours = (int) floor($seconds / 3600);
        $minutes = (int) floor(($seconds % 3600) / 60);

        return sprintf('%dh %02dm', $hours, $minutes);
    }

}

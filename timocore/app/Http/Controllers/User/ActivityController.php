<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
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
        $userId    = $request->integer('user');

        $startDate = Carbon::parse($request->input('date'));
        $endDate = $startDate->clone()->endOfDay();

        if ($request->mode == 'all') {
            return $this->getAllScreenshots($startDate, $endDate ,$userId);
        } else {
            return $this->getTenMinuteScreenshots($startDate, $endDate, $userId);
        }
    }

    private function getTenMinuteScreenshots($startDate, $endDate, $userId)
    {
        $member = User::find($userId);

        $tracks = Track::where('organization_id', organizationId())
            ->mine()
            ->when($userId, function ($q) use ($userId) {
                $q->where('user_id', $userId);
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
                    $blockEnd   = $blockCursor->copy()->addMinutes(10);

                    $blockTrack      = $tracks->where('started_at', '>=', $blockStart)->where('started_at', '<', $blockEnd);
                    $screenshotQuery = Screenshot::mine()->whereBetween('taken_at', [$blockStart, $blockEnd]);

                    if ($userId) {
                        $screenshotQuery->where('user_id', $userId);
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
        ]);
    }

    private function getAllScreenshots($startDate, $endDate, $userId)
    {
        $member = User::find($userId);

        $tracks = Track::where('organization_id', organizationId())
            ->mine()
            ->when($userId, function ($q) use ($userId) {
                $q->where('user_id', $userId);
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
                    return Carbon::parse($t->started_at)->between($sliceStart, $sliceEnd);
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
        $endTime   = $startTime->copy()->addMinutes(10);

        $screenshots = Screenshot::mine()->where('organization_id', organizationId())->whereBetween('taken_at', [$startTime, $endTime]);

        if ($request->user) {
            $screenshots->where('user_id', $request->user);
        }

        $screenshots = $screenshots->with('project:id,title')->get(['id', 'project_id', 'src', 'taken_at', 'file_storage_id']);

        $screenshots->each->append('url')->each(function($ss){
            $ss->taken_at = showDateTime($ss->taken_at);
            return $ss;
        });

        return response()->json($screenshots);
    }

}

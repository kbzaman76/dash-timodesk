<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PerformerController extends Controller
{

    public function top()
    {
        $pageTitle = 'Top Performers';

        $performerStart     = now()->startOfMonth();
        $performerEnd       = now()->endOfMonth();
        $performerDateRange = $performerStart->format('m/d/Y') . ' - ' . $performerEnd->format('m/d/Y');
        $performerLabel     = 'This Month';

        return view('Template::user.performer.top', compact(
            'pageTitle',
            'performerDateRange',
            'performerLabel',
        ));
    }

    private function downloadCsv(
        $filename,
        $performers,
        $startDate,
        $endDate
    ) {
        $organization = auth()->user()->organization;

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        return response()->stream(function () use ($performers, $organization, $startDate, $endDate) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Organization',
                $organization->name,
            ]);
            fputcsv($handle, [
                'Period',
                $startDate->format('M d, Y') . ' - ' . $endDate->format('M d, Y'),
            ]);
            fputcsv($handle, []);

            fputcsv($handle, [
                'Rank',
                'Member',
                'Activity Percentage',
                'Average Time',
                'Total Time',
            ]);

            foreach ($performers as $key => $performer) {
                fputcsv($handle, [
                    ++$key,
                    $performer->user->fullname,
                    (int) $performer->avgActivity . '%',
                    formatSeconds($performer->totalSeconds / $performer->totalDates),
                    formatSeconds($performer->totalSeconds ?? 0),
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }

    private function downloadPdf($pageTitle, $performers, $startDate, $endDate)
    {
        $organization = auth()->user()->organization;

        $pdf = Pdf::loadView(
            'Template::user.performer.pdf',
            compact('performers', 'organization', 'pageTitle')
        );

        $pdf->setPaper('A4', 'portrait');
        $pdf->setOptions([
            'dpi'             => 300,
            'isRemoteEnabled' => true,
            'chroot'          => realpath(base_path('..')),
            'fontDir'         => storage_path('fonts'),
            'fontCache'       => storage_path('fonts'),
        ]);

        return $pdf->download('performers-report-from-' . $startDate . '-to-' . $endDate . '.pdf');
    }

    public function loadTop(Request $request)
    {
        $sortBy       = $request->input('sort_by', 'time_activity');
        $organization = auth()->user()->organization;

        [$startDate, $endDate] = $this->parseRangeOrMonth($request->input('date'));
        $performers            = $this->getPerformerRanks($organization, $startDate, $endDate, 'DESC', $sortBy);

        if ($request->boolean('csv')) {
            return $this->downloadCsv(
                'top_performers_' . $startDate->format('Ymd') . '_' . $endDate->format('Ymd') . '.csv',
                $performers,
                $startDate,
                $endDate
            );
        }

        if (request()->pdf) {
            $pageTitle = 'Top Performers - ' . $startDate->format('M d, Y') . ' to ' . $endDate->format('M d, Y');
            return $this->downloadPdf(
                $pageTitle,
                $performers,
                $startDate,
                $endDate
            );
        }

        $table = view('Template::user.performer.table', [
            'performers' => $performers,
        ])->render();

        return response()->json([
            'html' => $table,
        ]);
    }

    private function parseRangeOrMonth($range)
    {
        if ($range && str_contains($range, '-')) {
            try {
                [$start, $end] = array_map('trim', explode('-', $range));
                $startDate     = Carbon::createFromFormat('m/d/Y', $start)->startOfDay();
                $endDate       = Carbon::createFromFormat('m/d/Y', $end)->endOfDay();
                return [$startDate, $endDate];
            } catch (\Throwable $e) {
            }
        }
        return [now()->startOfMonth(), now()->endOfMonth()];
    }

    private function getPerformerRanks($organization, $startDate, $endDate, $sort, $sortBy = 'time_activity')
    {
        if ($sortBy == 'activity') {
            $sortBy = 'avgActivity';
        } elseif ($sortBy === 'time_activity') {
            $sortBy = 'totalOverallActivity';
        } else {
            $sortBy = 'totalSeconds';
        }

        $query = $organization->tracks()
            ->whereBetweenOrg('started_at', $startDate, $endDate)
            ->groupBy('user_id')
            ->with('user:id,fullname,image,uid')
            ->selectRaw('user_id')
            ->selectRaw('COUNT(DISTINCT DATE(started_at)) as totalDates')
            ->selectRaw('SUM(COALESCE(time_in_seconds,0)) as totalSeconds')
            ->selectRaw('SUM(COALESCE(overall_activity,0)) as totalOverallActivity')
            ->selectRaw('CASE WHEN SUM(COALESCE(time_in_seconds,0)) = 0 THEN 0
                     ELSE SUM(COALESCE(overall_activity,0)) / SUM(COALESCE(time_in_seconds,0))
                END as avgActivity')
            ->orderBy($sortBy, $sort);
        if (request()->pdf || request()->boolean('csv')) {
            return $query->get();
        } else {
            return $query->paginate(getPaginate());
        }

    }

    public function low()
    {
        $pageTitle = 'Low Performers';

        // Default to current month
        $performerStart     = now()->startOfMonth();
        $performerEnd       = now()->endOfMonth();
        $performerDateRange = $performerStart->format('m/d/Y') . ' - ' . $performerEnd->format('m/d/Y');
        $performerLabel     = 'This Month';
        return view('Template::user.performer.low', compact(
            'pageTitle',
            'performerDateRange',
            'performerLabel',
        ));
    }

    public function loadLow(Request $request)
    {
        $sortBy                = $request->input('sort_by', 'time');
        $organization          = auth()->user()->organization;
        [$startDate, $endDate] = $this->parseRangeOrMonth($request->input('date'));
        $performers            = $this->getPerformerRanks($organization, $startDate, $endDate, 'ASC', $sortBy);

        if ($request->boolean('csv')) {
            return $this->downloadCsv(
                'low_performers_' . $startDate->format('Ymd') . '_' . $endDate->format('Ymd') . '.csv',
                $performers,
                $startDate,
                $endDate
            );
        }

        if (request()->pdf) {
            $pageTitle = 'Low Perfomers - ' . $startDate->format('M d, Y') . ' to ' . $endDate->format('M d, Y');
            return $this->downloadPdf(
                $pageTitle,
                $performers,
                $startDate,
                $endDate
            );
        }

        $table = view('Template::user.performer.table', [
            'performers' => $performers,
        ])->render();

        return response()->json([
            'html' => $table,
        ]);
    }

    public function leaderboard()
    {
        $pageTitle = 'Leaderboard';

        $user         = auth()->user();
        $organization = $user->organization;

        $firstTrack = $organization->tracks()->orderBy('started_at')->first();
        $firstYear  = $firstTrack ? Carbon::parse($firstTrack->started_at)->year : now()->year;

        $years = array_reverse(range($firstYear, now()->year));

        return view('Template::user.performer.leader', compact('pageTitle', 'years'));
    }

    public function loadLeaderboard(Request $request)
    {
        $user         = auth()->user();
        $organization = $user->organization;

        $year = $request->year ?? now()->year;

        $startDate = Carbon::create($year, 1, 1);
        $endDate   = Carbon::create($year, 12, 31);

        $fromOffset = now(config('app.timezone'))->format('P');
        $toOffset   = now(orgTimezone())->format('P');

        $topMembers = $organization->tracks()->with('user:id,fullname,image')
            ->whereBetweenOrg('started_at', $startDate, $endDate)
            ->selectRaw("MONTH(CONVERT_TZ(started_at, '$fromOffset', '$toOffset')) as month")
            ->selectRaw("COUNT(DISTINCT DATE(CONVERT_TZ(started_at, '$fromOffset', '$toOffset'))) as totalWorkingDays")
            ->selectRaw('user_id, SUM(COALESCE(time_in_seconds, 0)) as totalSeconds,
            SUM(overall_activity) as totalActivity')
            ->selectRaw('SUM(overall_activity) as totalActivity')
            ->groupBy('month', 'user_id')
            ->orderBy('month')
            ->orderByDesc('totalSeconds')
            ->get()
            ->groupBy('month')
            ->map(function ($group) {
                return $group->first();
            })->values();

        $topMembers = collect(range(1, 12))->map(function ($month) use ($topMembers, $year) {
            return $topMembers->firstWhere('month', $month) ?? (object) [
                'month'            => $month,
                'user'             => null,
                'totalSeconds'     => 0,
                'totalWorkingDays' => 0,
                'totalActivity'    => 0,
            ];
        });

        $topMembers = $topMembers->map(function ($member) use ($year) {
            $member->year = $member->year ?? $year;
            return $member;
        });

        $yearlyTop = $topMembers
            ->groupBy('user_id')
            ->map(function ($items) {
                $valid = $items->filter(fn($item) => $item->totalSeconds > 0);

                return (object) [
                    'user'      => $items->first()->user,
                    'topMonths' => $valid->count(),
                ];
            })
            ->filter(fn($item) => $item->topMonths > 0)
            ->sortByDesc('topMonths')
            ->values();

        return response()->json([
            'html' => view('Template::user.performer.leader_content', compact('topMembers', 'yearlyTop'))->render(),
        ]);
    }

    public function leaderboardMailSend($month, $year)
    {
        $user         = auth()->user();
        $organization = $user->organization;

        $topMember = $this->getTopMember($organization, $month, $year);

        if (!$topMember) {
            $notify[] = ['error', 'No user found'];
            return back()->withNotify($notify);
        }

        notify($topMember->user, 'MONTHLY_PERFOMER', [
            'year'               => $year,
            'total_work'         => formatSeconds($topMember->totalSeconds),
            'total_working_days' => $topMember->totalWorkingDays,
            'month'              => Carbon::createFromDate(null, $topMember->month, 1)->format('F'),
            'image_url'          => $topMember->user->image_url,
            'fullname'           => $topMember->user->fullname,
            'activity'           => number_format($topMember->totalActivity / $topMember->totalSeconds, 2),
        ]);

        $notify[] = ['success', 'Mail sent successfully'];
        return goBack($notify);
    }

    public function leaderboardDownload($month, $year)
    {
        $pageTitle = 'Leaderboard';
        $user         = auth()->user();
        $organization = $user->organization;
        $topMember = $this->getTopMember($organization, $month, $year);

        $printDate = date("F", mktime(0, 0, 0, $month, 1)).' '.$year;

        if (!$topMember) {
            $notify[] = ['error', 'No user found'];
            return back()->withNotify($notify);
        }
        $pdf = Pdf::loadView('Template::user.performer.leader_pdf', compact('pageTitle', 'organization', 'topMember', 'printDate'));
        $pdf->setPaper('A5', 'portrait');
        $pdf->setOptions([
            'dpi'             => 300,
            'isRemoteEnabled' => true,
            'chroot'          => realpath(base_path('..')),
            'fontDir'         => storage_path('fonts'),
            'fontCache'       => storage_path('fonts'),
        ]);

        return $pdf->download('leaderboard-' . $month . '-' . $year . '.pdf');
    }

    private function getTopMember($organization, $month, $year)
    {

        $fromOffset = now(config('app.timezone'))->format('P');
        $toOffset   = now(orgTimezone())->format('P');

        $topMember = $organization->tracks()
            ->with('user:id,fullname,email,image')
            ->whereYear('started_at', $year)
            ->whereMonth('started_at', $month)
            ->selectRaw("MONTH(CONVERT_TZ(started_at, '$fromOffset', '$toOffset')) as month")
            ->selectRaw("COUNT(DISTINCT DATE(CONVERT_TZ(started_at, '$fromOffset', '$toOffset'))) as totalWorkingDays")
            ->selectRaw('user_id, SUM(COALESCE(time_in_seconds, 0)) as totalSeconds')
            ->selectRaw('SUM(overall_activity) as totalActivity')
            ->groupBy('user_id')
            ->orderByDesc('totalSeconds')
            ->first();

        if (!$topMember) {
            return null;
        }

        $topMember->user->append('image_url');

        return $topMember;
    }

}

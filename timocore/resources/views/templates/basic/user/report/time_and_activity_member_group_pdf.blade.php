<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Time and Activity</title>
    <style>
        @page {
            size: 8.27in 11.7in;
            margin: 0.5in 0.3in;
        }
        body {
            font-family: "Inter", sans-serif;
            font-size: 18px;
            color: #222;
            overflow: hidden;
        }
        .orgname {
            font-size: 76px;
            line-height: 70px;
            font-weight: 800;
            color: #ff6a00;
            font-family: "Urbanist", sans-serif;
        }
        .top-content-area .top-content {
            margin-bottom: 30px;
        }
        .top-content-area .top-content .left-content {
            display: inline-block;
        }
        .top-content-area .top-content .right-content {
            display: inline-block;
            float: right;
        }
        .top-content-area .top-content .subtitle {
            font-size: 56px;
            line-height: 50px;
            font-weight: 600;
            color: #030442;
        }
        .info-wrapper {
          background-color: rgba(0, 0, 0, 0.05);
          padding: 60px;
          height: 0.6in;
          display: block;
          margin-bottom: 60px;
      }
      .info-wrapper .info-left {
        width: 3in;
        height: 1in;
        display: inline-block;
        text-align: center;
    }
    .info-wrapper .info-center {
        width: 2.1in;
        height: 1in;
        display: inline-block;
        text-align: center;
        border-left: 10px solid #ffffff;
        border-right: 10px solid #ffffff;
    }
    .info-wrapper .info-right {
        width: 2in;
        height: 1in;
        display: inline-block;
        text-align: center;
    }
    .info-title{
        font-size: 46px;
        line-height: 40px;
        font-weight: 800;
        color: #ff6a00;
        font-family: "Urbanist", sans-serif;

    }
    .info-details{
        font-size: 76px;
        line-height: 40px;
        font-weight: 800;
        color: #030712;
        font-family: "Inter", sans-serif;

    }

    .info-details-date{
        font-size: 56px;
        line-height: 40px;
        font-weight: 700;
        color: #030712;
        font-family: "Inter", sans-serif;

    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 15px;
    }
    thead {
        background-color:  rgba(255, 105, 0, 1);
        color: #ffffff;
        font-size: 40px;
        line-height:40px;
        font-weight: 700;
        font-family: "Urbanist", sans-serif;
    }
    thead th {
        padding: 10px 0;
        text-align: center;
        font-weight: 700;
        border: 1px solid #eaeaea;
    }
    tbody td {
        border: 1px solid #eaeaea;
        padding: 10px;
        text-align: center;
        font-size: 36px;
        line-height:36px;

        {{-- font-weight: 700; --}}
        color: #030712;
        font-family: "Inter", sans-serif;
    }
    .total-user-row td {
        background-color:  rgba(0, 0, 0, 0.3);
        font-weight: 700;
        font-size: 40px;
        padding: 30px;
    }
    .single-user-total .color{
        background-color:  rgba(255, 105, 0, 0.05);
        font-weight: 700;
    }
    .single-user-total td{
        padding: 16px;
    }

    .fw-bold {
        font-weight: 600;
    }
    .text-end {
        text-align: right;
    }

    .collapsed td{
        padding: 20px;
        font-size: 40px;
        font-weight:700;
    }
    .group-row td{
        color: #ff6a00;
        font-family: "Urbanist", sans-serif;
        padding: 40px 0;
        font-size: 52px;
        line-heignt:52px;
        font-weight:800;
}
</style>
</head>

<body>

        <div class="top-content-area">
        <div class="top-content">
            <div class="left-content" style="width:4.5in">
                <span class="orgname">{{ $organization->name }}</span>

            </div>
            <div class="right-content">
                <img src="{{ siteLogo('dark') }}" alt="@lang('Logo')" width="420px">
            </div>
        </div><!-- //.top content -->
    </div>





    <div class="info-wrapper">
        <div class="info-left">
            <h3 class="info-title">Time and Activity for</h3>
            <h3 class="info-details-date">
                @if (!empty($startDate) && !empty($endDate))
                {{ $startDate->format('M d, Y') }} - {{ $endDate->format('M d, Y') }}
                @endif
            </h3>
        </div>
        <div class="info-center">
            <h3 class="info-title">Total Work Time</h3>
            <h3 class="info-details">{{ $totalWorkTime }}</h3>
        </div>
        <div class="info-right">
            <h3 class="info-title">Average Activity</h3>
            <h3 class="info-details">{{ $activityPercent }}</h3>
        </div>
    </div>



{{-- ############################## --}}
        <table>
            <thead>
                <tr>
                    @if ($dataType == 'expanded')
                    <th>Date</th>
                    <th>Project</th>
                    @else
                    <th>Member</th>
                    @endif
                    <th>@lang('Total Time')</th>
                    <th>@lang('Activity')</th>
                </tr>
            </thead>
            <tbody>
            {{-- LOOP: USERS --}}
            @php
                $members = $tracks->groupBy('user_id')->sortByDesc(function ($members) {
                    return $members->sum('totalSeconds');
                });
            @endphp
            @foreach ($members as $userId => $userTracks)
                @php
                    $user = $userTracks->first()->user ?? null;
                    $userTotalSeconds = $userTracks->sum('totalSeconds');
                    $userTotalActivity = $userTracks->sum('totalActivity');
                    $userActivityPercent = showAmount(
                        $userTotalActivity / ($userTotalSeconds > 0 ? $userTotalSeconds : 1),
                        currencyFormat: false,
                    );
                @endphp

                @if ($dataType == 'expanded')
                    @php
                        // TOTAL ROWS of this USER (for fullname middle)
                        $totalUserRows = $userTracks
                            ->groupBy('created_on')
                            ->sum(fn($tracks) => $tracks->groupBy('project_id')->count());

                        $userMiddle = ceil($totalUserRows / 2);
                        $userRowCounter = 0;
                    @endphp


                    @foreach ($userTracks->groupBy('created_on') as $date => $dateTracks)
                        @php
                            // Date totals
                            $dateRows = $dateTracks->groupBy('project_id')->count();
                            $dateMiddle = ceil($dateRows / 2);
                            $dateRowCounter = 0;

                            $dateTotalSeconds = $dateTracks->sum('totalSeconds');
                            $dateTotalActivity = $dateTracks->sum('totalActivity');
                            $dateTotalActivity = showAmount($dateTotalActivity / ($dateTotalSeconds > 0 ? $dateTotalSeconds : 1), currencyFormat: false);
                        @endphp


                        @foreach ($dateTracks->groupBy('project_id')->sortByDesc(fn($p) => $p->sum('totalSeconds')) as $projectId => $projectTracks)
                            @php
                                $project = $projectTracks->first()->project ?? null;
                                $projSeconds = $projectTracks->sum('totalSeconds');
                                $projActivity = $projectTracks->sum('totalActivity');

                                // increase counters
                                $userRowCounter++;
                                $dateRowCounter++;
                            @endphp


{{-- USER only once--}}
                    @if ($userRowCounter == 1 || $totalUserRows == 1)
                    <tr class="group-row">
                        <td colspan="4" style="" class="fw-bold"> Time and Activity of {{ toTitle($user->fullname) ?? '' }}</td>
                    </tr>
                    @endif



                            <tr>
                                {{-- DATE only on the middle row of this date --}}
                                <td style="border-top: 0; border-bottom: 0" class="fw-bold">
                                    @if ($dateRowCounter == $dateMiddle || $dateRows == 1)
                                        {{ showDateTime($date, 'Y-m-d') }}
                                    @endif
                                </td>

                                {{-- PROJECT --}}
                                <td>
                                    @if ($project?->title)
                                        {{ $project->title }}
                                    @else
                                        
                                    @endif
                                </td>

                                <td>{{ formatSecondsToHoursMinutes($projSeconds) }}</td>
                                <td>{{ showAmount($projActivity / ($projSeconds ?: 1), currencyFormat: false) }}%</td>
                            </tr>
                        @endforeach

                        {{-- user total --}}
                        <tr class="single-user-total">
                            <td colspan="2" class="color text-end">Total</td>
                            <td class="color">{{ formatSecondsToHoursMinutes($dateTotalSeconds) }}</td>
                            <td class="color">{{ $dateTotalActivity }}%</td>
                        </tr>
                    @endforeach


                    {{-- user total --}}
                    <tr class="total-user-row">
                        <td colspan="2" class="fw-bold">Total of {{ toTitle($user->fullname) ?? '' }}</td>
                        <td class="fw-bold">{{ formatSecondsToHoursMinutes($userTotalSeconds) }}</td>
                        <td class="fw-bold">{{ $userActivityPercent }}%</td>
                    </tr>
                @else
                    <tr class="collapsed">
                        <td>{{ toTitle($user->fullname) ?? '' }}</td>
                        <td>{{ formatSecondsToHoursMinutes($userTotalSeconds) }}</td>
                        <td>{{ $userActivityPercent }}%</td>
                    </tr>
                @endif
            @endforeach
        </tbody>
    </table>

</body>

</html>
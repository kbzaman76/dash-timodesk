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
            font-size: 12px;
            color: #222;
            overflow: hidden;
        }

        .orgname {
            font-size: 24px;
            line-height: 22px;
            font-weight: 800;
            color: #ff6a00;
            font-family: "Urbanist", sans-serif;
        }

        .top-content-area .top-content {
            margin-bottom: 10px;
        }

        .top-content-area .top-content .left-content {
            display: inline-block;
        }

        .top-content-area .top-content .right-content {
            display: inline-block;
            float: right;
        }

        .top-content-area .top-content .subtitle {
            font-size: 18px;
            line-height: 16px;
            font-weight: 600;
            color: #030442;
        }

        .info-wrapper {
            background-color: rgba(0, 0, 0, 0.05);
            padding: 20px;
            height: 0.6in;
            display: block;
            margin-bottom: 20px;
        }

        .info-wrapper .info-left {
            width: 3in;
            height: 1in;
            display: inline-block;
            text-align: center;
        }

        .info-wrapper .info-center {
            width: 2.1in;
            display: inline-block;
            text-align: center;
            border-left: 3px solid #ffffff;
            border-right: 3px solid #ffffff;
        }

        .info-wrapper .info-right {
            width: 2in;
            height: 1in;
            display: inline-block;
            text-align: center;
        }

        .info-title {
            font-size: 15px;
            line-height: 13px;
            font-weight: 800;
            color: #ff6a00;
            font-family: "Urbanist", sans-serif;

        }

        .info-details {
            font-size: 22px;
            line-height: 13px;
            font-weight: 800;
            color: #030712;
            font-family: "Inter", sans-serif;

        }

        .info-details-date {
            font-size: 18px;
            line-height: 13px;
            font-weight: 700;
            color: #030712;
            font-family: "Inter", sans-serif;

        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        thead {
            background-color: rgba(255, 105, 0, 1);
            color: #ffffff;
            font-size: 14px;
            line-height: 14px;
            font-weight: 700;
            font-family: "Urbanist", sans-serif;
        }

        thead th {
            padding: 4px 0;
            text-align: center;
            font-weight: 700;
            border: 1px solid #eaeaea;
        }

        tbody td {
            border: 1px solid #eaeaea;
            padding: 6px;
            text-align: center;
            font-size: 12px;
            line-height: 12px;
            color: #030712;
            font-family: "Inter", sans-serif;
        }

        .total-user-row td {
            background-color: rgba(0, 0, 0, 0.3);
            font-weight: 700;
            font-size: 13px;
            padding: 10px;
        }

        .single-user-total .color {
            background-color: rgba(255, 105, 0, 0.05);
            font-weight: 700;
        }

        .single-user-total td {
            padding: 5px;
        }

        .fw-bold {
            font-weight: 600;
        }

        .text-end {
            text-align: right;
        }

        .collapsed td {
            padding: 6px;
            font-size: 13px;
            font-weight: 700;
        }

        .group-row td {
            color: #ff6a00;
            font-family: "Urbanist", sans-serif;
            padding: 13px 0;
            font-size: 17px;
            line-height: 17px;
            font-weight: 800;
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
                <img src="{{ siteLogo('dark') }}" alt="@lang('Logo')" width="120px">
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
                    @role('manager|organizer')
                        <th>Member</th>
                    @endrole
                @else
                    <th>Project</th>
                @endif
                <th>@lang('Total Time')</th>
                <th>@lang('Activity')</th>
            </tr>
        </thead>
        <tbody>
            {{-- LOOP: PROJECTS --}}
            @php
                $projects = $tracks->groupBy('project_id')->sortByDesc(function ($projects) {
                    return $projects->sum('totalSeconds');
                });
            @endphp
            @foreach ($projects as $projectId => $projectTracks)
                @php
                    $project = $projectTracks->first()->project ?? null;
                    $projectTotalSeconds = $projectTracks->sum('totalSeconds');
                    $projectTotalActivity = $projectTracks->sum('totalActivity');
                    $projectTotalActivityPercent = showAmount(
                        $projectTotalActivity / ($projectTotalSeconds > 0 ? $projectTotalSeconds : 1),
                        currencyFormat: false,
                    );
                @endphp

                @if ($dataType == 'expanded')
                    <tr class="group-row">
                        <td @role('manager|organizer') colspan="4" @else colspan="3" @endrole style="" class="fw-bold"> Time and Activity of
                            {{ $project->title }}</td>
                    </tr>


                    @foreach ($projectTracks->groupBy('created_on') as $date => $dateTracks)
                        @php
                            // Date totals
                            $dateRows = $dateTracks->groupBy('user_id')->count();
                            $dateMiddle = ceil($dateRows / 2);
                            $dateRowCounter = 0;

                            $dateTotalSeconds = $dateTracks->sum('totalSeconds');
                            $dateTotalActivity = $dateTracks->sum('totalActivity');
                            $dateTotalActivity = showAmount(
                                $dateTotalActivity / ($dateTotalSeconds > 0 ? $dateTotalSeconds : 1),
                                currencyFormat: false,
                            );

                            $sortedUserTracks = $dateTracks
                                ->groupBy('user_id')
                                ->sortByDesc(fn($p) => $p->sum('totalSeconds'));
                        @endphp


                        @foreach ($sortedUserTracks as $userId => $userTracks)
                            @php
                                $user = $userTracks->first()->user ?? null;
                                $userTotalSeconds = $userTracks->sum('totalSeconds');
                                $userTotalActivity = $userTracks->sum('totalActivity');
                                $userTotalActivityPercent = showAmount(
                                    $userTotalActivity / ($userTotalSeconds > 0 ? $userTotalSeconds : 1),
                                    currencyFormat: false,
                                );
                                $dateRowCounter++;
                            @endphp

                            <tr>
                                <td style="border-top: 0; border-bottom: 0" class="fw-bold">
                                    @if ($dateMiddle == $dateRowCounter || $dateRows == 1)
                                        {{ showDateTime($date, 'Y-m-d') }}
                                    @endif
                                </td>

                                @role('manager|organizer')
                                    <td>{{ $user->fullname }}</td>
                                @endrole

                                <td>
                                    @if ($userTotalSeconds > 60)
                                        {{ formatSecondsToHoursMinutes($userTotalSeconds) }}
                                    @else
                                        &lt; 1m
                                    @endif
                                </td>
                                <td>{{ $userTotalActivityPercent }}%</td>
                            </tr>
                        @endforeach

                        {{-- date total --}}
                        <tr class="single-user-total">
                            <td @role('manager|organizer') colspan="2" @endrole class="color text-end">Total of {{ showDateTime($date, 'Y-m-d') }}</td>
                            <td class="color">
                                @if ($dateTotalSeconds > 60)
                                    {{ formatSecondsToHoursMinutes($dateTotalSeconds) }}
                                @else
                                    &lt; 1m
                                @endif
                            </td>
                            <td class="color">{{ $dateTotalActivity }}%</td>
                        </tr>
                    @endforeach


                    {{-- project total --}}
                    <tr class="total-user-row">
                        <td @role('manager|organizer') colspan="2" @endrole class="fw-bold">Total of {{ toTitle($project->title) ?? '' }}</td>
                        <td class="fw-bold">
                            @if ($projectTotalSeconds > 60)
                                {{ formatSecondsToHoursMinutes($projectTotalSeconds) }}
                            @else
                                &lt; 1m
                            @endif
                        </td>
                        <td class="fw-bold">{{ $projectTotalActivityPercent }}%</td>
                    </tr>
                @else
                    <tr class="collapsed">
                        <td>{{ toTitle($project->title) ?? '' }}</td>
                        <td>
                            @if ($projectTotalSeconds > 60)
                                {{ formatSecondsToHoursMinutes($projectTotalSeconds) }}
                            @else
                                &lt; 1m
                            @endif
                        </td>
                        <td>{{ $projectTotalActivityPercent }}%</td>
                    </tr>
                @endif
            @endforeach
        </tbody>
    </table>

</body>

</html>

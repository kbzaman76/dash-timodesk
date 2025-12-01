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

        .info-title {
            font-size: 46px;
            line-height: 40px;
            font-weight: 800;
            color: #ff6a00;
            font-family: "Urbanist", sans-serif;

        }

        .info-details {
            font-size: 76px;
            line-height: 40px;
            font-weight: 800;
            color: #030712;
            font-family: "Inter", sans-serif;

        }

        .info-details-date {
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
            background-color: rgba(255, 105, 0, 1);
            color: #ffffff;
            font-size: 40px;
            line-height: 40px;
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
            line-height: 36px;

            {{-- font-weight: 700; --}} color: #030712;
            font-family: "Inter", sans-serif;
        }

        .total-user-row td {
            background-color: rgba(0, 0, 0, 0.3);
            font-weight: 700;
            font-size: 40px;
            padding: 30px;
        }

        .single-user-total .color {
            background-color: rgba(255, 105, 0, 0.05);
            font-weight: 700;
        }

        .single-user-total td {
            padding: 16px;
        }

        .fw-bold {
            font-weight: 600;
        }

        .text-end {
            text-align: right;
        }

        .collapsed td {
            padding: 20px;
            font-size: 40px;
            font-weight: 700;
        }

        .group-row td {
            color: #ff6a00;
            font-family: "Urbanist", sans-serif;
            padding: 40px 0;
            font-size: 52px;
            line-heignt: 52px;
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




    {{-- MAIN TABLE --}}
    <table>
        <thead>
            <tr>
                @if ($dataType == 'expanded')
                    @role('manager|organizer')
                    <th>Member</th>
                    @endrole
                    <th>Project</th>
                @else
                    <th>Date</th>
                @endif
                <th>Total Time</th>
                <th>Activity</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($tracks->groupBy('created_on') as $date => $items)
                @php
                    $totalUsers = $items->groupBy('user_id')->count();
                    $totalSeconds = $items->sum('totalSeconds');
                    $totalActivity = $items->sum('totalActivity');
                    $activityPercent = showAmount($totalActivity / ($totalSeconds > 0 ? $totalSeconds : 1), currencyFormat: false);
                @endphp


                {{-- USERS --}}
                @if ($dataType == 'expanded')
                    @php
                        // GROUP USERS SORTED
                        $sortedUsers = $items->groupBy('user_id')->sortByDesc(fn($userTracks) => $userTracks->sum('totalSeconds'));

                        // TOTAL ROWS for this DATE  (sum of all projects of all users)
                        $totalRows = $sortedUsers->sum(fn($userTracks) => $userTracks->groupBy('project_id')->count());

                        $dateMiddle = ceil($totalRows / 2);
                        $rowCounter = 0; // count rows for date middle
                    @endphp


                    @foreach ($sortedUsers as $userId => $userTracks)
                        @php
                            $user = $userTracks->first()->user ?? null;

                            // user project groups
                            $projectGroups = $userTracks->groupBy('project_id')->sortByDesc(fn($p) => $p->sum('totalSeconds'));

                            $userTotalRows = $projectGroups->count(); // total project rows
                            $userMiddle = ceil($userTotalRows / 2); // middle row for user
                            $userRow = 0;
                        @endphp


                        @foreach ($projectGroups as $projectId => $projectTracks)
                            @php
                                $project = $projectTracks->first()->project ?? null;

                                $projSeconds = $projectTracks->sum('totalSeconds');
                                $projActivity = $projectTracks->sum('totalActivity');
                                $projPercent = showAmount($projActivity / max($projSeconds, 1), currencyFormat: false);

                                $rowCounter++; // date row counter
                                $userRow++; // user row counter
                            @endphp



                            {{-- DATE only once --}}
                            @if ($rowCounter == 1 || $totalRows == 1)
                                <tr class="group-row">
                                    <td colspan="4" style="" class="fw-bold"> Time and Activity for {{ showDateTime($date, 'Y-m-d') }}</td>
                                </tr>
                            @endif

                            <tr>



                                {{-- USER NAME only on the middle row of that USER --}}
                                @role('manager|organizer')
                                <td style="border-top: 0; border-bottom: 0; width: 1.5in;" class="fw-bold">
                                    @if ($userRow == $userMiddle || $userTotalRows == 1)
                                        {{ toTitle($user->fullname) ?? '' }}
                                    @endif
                                </td>
                                @endrole

                                <td>{{ $project->title ?? '' }}</td>
                                <td>
                                    @if($projSeconds > 60)
                                    {{ formatSecondsToHoursMinutes($projSeconds) }}
                                    @else
                                    &lt; 1m
                                    @endif
                                </td>
                                <td>{{ $projPercent }}%</td>
                            </tr>
                        @endforeach

                        @php
                            $userTotalSeconds = $userTracks->sum('totalSeconds');
                            $userTotalActivity = $userTracks->sum('totalActivity');
                            $userActivityPercent = showAmount($userTotalActivity / ($userTotalSeconds > 0 ? $userTotalSeconds : 1), currencyFormat: false);
                        @endphp
                        <tr class="single-user-total">
                            {{-- <td style="border-top: 0; border-bottom: 0"></td> --}}
                            <td class="color text-end" colspan="@role('manager|organizer') 2 @else 1 @endrole">Total</td>
                            <td class="color">{{ formatSecondsToHoursMinutes($userTracks->sum('totalSeconds')) }}</td>
                            <td class="color">{{ $userActivityPercent }}%</td>
                        </tr>
                    @endforeach

                    <tr class="total-user-row">
                        <td colspan="@role('manager|organizer') 2 @else 1 @endrole">Total of {{ showDateTime($date, 'Y-m-d') }}</td>
                        <td>
                            @if($totalSeconds > 60)
                            {{ formatSecondsToHoursMinutes($totalSeconds) }}
                            @else
                            &lt; 1m
                            @endif
                        </td>
                        <td>{{ $activityPercent }}%</td>
                    </tr>
                @else
                    <tr class="collapsed">
                        <td>{{ showDateTime($date, 'Y-m-d') }}</td>
                        <td>
                            @if($totalSeconds > 60)
                            {{ formatSecondsToHoursMinutes($totalSeconds) }}
                            @else
                            &lt; 1m
                            @endif
                        </td>
                        <td>{{ $activityPercent }}%</td>
                    </tr>
                @endif
            @endforeach
        </tbody>
    </table>

</body>

</html>

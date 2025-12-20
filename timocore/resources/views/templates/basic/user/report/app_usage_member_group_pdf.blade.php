@php
    $format = $format ?? 'pdf';
    $groupBy = $groupBy ?? 'date';
    $dataType = $dataType ?? 'collapsed';
    $rangeText = '';
    if (!empty($startDate) && !empty($endDate)) {
        $rangeText = $startDate->format('M d, Y') . ' - ' . $endDate->format('M d, Y');
    }
    $totalUsage = formatSecondsToHoursMinutes($apps->sum('totalSeconds'));
@endphp






<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>App Usage Report</title>

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
            <h3 class="info-title">App Usage Report for</h3>
            <h3 class="info-details-date">{{ $rangeText }}</h3>
        </div>
        <div class="info-center">
            <h3 class="info-title">Total Usage</h3>
            <h3 class="info-details">{{ $totalUsage }}</h3>
        </div>
        <div class="info-right">
            <h3 class="info-title">Group by</h3>
            <h3 class="info-details">Member</h3>
        </div>
    </div>



    {{-- ############################## --}}



    <table>
        <thead>
            @if ($dataType === 'collapsed')
                <tr>
                    <th>@lang('Member')</th>
                    <th>@lang('Total App Used')</th>
                    <th>@lang('Total Time')</th>
                </tr>
            @else
                <tr>
                    <th>@lang('App')</th>
                    <th>@lang('Date')</th>
                    <th>@lang('Total Time')</th>
                </tr>
            @endif
        </thead>
        <tbody>
            @php
                $sortedUsers = $apps->groupBy('user_id')->sortByDesc(function ($query) {
                    return $query->sum('totalSeconds');
                });
            @endphp

            @foreach ($sortedUsers as $userApps)
                @php
                    $user = optional($userApps->first())->user;
                @endphp

                @if ($dataType === 'collapsed')
                    <tr class="collapsed">
                        <td>{{ toTitle($user->fullname) ?? __('Unknown Member') }}</td>
                        <td>{{ $userApps->groupBy('app_name')->count() }}</td>
                        <td>
                            @if($userApps->sum('totalSeconds') > 60)
                            {{ formatSecondsToHoursMinutes($userApps->sum('totalSeconds')) }}
                            @else
                            &lt; 1m
                            @endif
                        </td>
                    </tr>
                @else
                    @php

                        // Sort apps
                        $sortedUserApps = $userApps
                            ->groupBy('app_name')
                            ->sortByDesc(fn($query) => $query->sum('totalSeconds'));

                        // Total rows for this user (sum of all dates inside all apps)
                        $totalUserRows = $sortedUserApps->sum(
                            fn($appEntries) => $appEntries->groupBy('created_on')->count(),
                        );

                        $userRowCounter = 0; // count row for user middle calculation
                        $userMiddle = ceil($totalUserRows / 2);
                    @endphp

                    <tr class="group-row">
                        <td colspan="3">
                            App Usage Report of {{ toTitle($user->fullname) ?? __('Unknown Member') }}
                        </td>
                    </tr>



                    @foreach ($sortedUserApps as $appName => $appEntries)
                        @php
                            $dates = $appEntries->groupBy('created_on');
                            $totalAppRows = $dates->count(); // rows inside this app
                            $appMiddle = ceil($totalAppRows / 2); // app middle row
                            $appRowCounter = 0;
                        @endphp


                        @foreach ($dates as $date => $dateEntries)
                            @php
                                $appRowCounter++;
                                $userRowCounter++;
                            @endphp

                            <tr>
                                <td style="border-top: 0; border-bottom: 0" class="fw-bold">
                                    @if ($appRowCounter == $appMiddle || $totalAppRows == 1)
                                        {{ $appName }}
                                    @endif
                                </td>

                                <td style="width: 1.3in;">{{ showDateTime($date, 'Y-m-d') }}</td>
                                <td style="width: 1.0in;">
                                    @if($dateEntries->sum('totalSeconds') > 60)
                                    {{ formatSecondsToHoursMinutes($dateEntries->sum('totalSeconds')) }}
                                    @else
                                    &lt; 1m
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        <tr class="single-user-total">
                            <td class="color text-end" colspan="2">Total</td>
                            <td class="color">
                                @if($appEntries->sum('totalSeconds') > 60)
                                {{ formatSecondsToHoursMinutes($appEntries->sum('totalSeconds')) }}
                                @else
                                &lt; 1m
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    <tr class="total-user-row">
                        <td class="fw-bold" colspan="2">Total of {{ toTitle($user->fullname) ?? __('Unknown Member') }}</td>
                        <td class="fw-bold">
                            @if($userApps->sum('totalSeconds') > 60)
                            {{ formatSecondsToHoursMinutes($userApps->sum('totalSeconds')) }}
                            @else
                            &lt; 1m
                            @endif
                        </td>
                    </tr>
                @endif
            @endforeach

        </tbody>
    </table>

</body>

</html>

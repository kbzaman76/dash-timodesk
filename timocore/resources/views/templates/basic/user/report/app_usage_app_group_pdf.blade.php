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
            <h3 class="info-details">APP</h3>
        </div>
    </div>



    {{-- ############################## --}}


    <table>
        <thead>
            @if ($dataType === 'collapsed')
                <tr>
                    <th>@lang('App')</th>
                    @role('manager|organizer')
                    <th>@lang('Total Members')</th>
                    @endrole
                    <th>@lang('Total Time')</th>
                </tr>
            @else
                <tr>
                    @role('manager|organizer')
                    <th>@lang('Member')</th>
                    @endrole
                    <th>@lang('Date')</th>
                    <th>@lang('Total Time')</th>
                </tr>
            @endif
        </thead>
        <tbody>
            @php
                $sortedApps = $apps->groupBy('app_name')->sortByDesc(function ($query) {
                    return $query->sum('totalSeconds');
                });
            @endphp
            @foreach ($sortedApps as $appName => $appEntries)
                @if ($dataType === 'collapsed')
                    <tr>
                        <td>{{ $appName }}</td>
                        @role('manager|organizer')
                        <td>{{ $appEntries->groupBy('user_id')->count() }}</td>
                        @endrole
                        <td>
                            @if($appEntries->sum('totalSeconds') > 60)
                            {{ formatSecondsToHoursMinutes($appEntries->sum('totalSeconds')) }}
                            @else
                            &lt; 1m
                            @endif
                        </td>
                    </tr>
                @else
                    @php
                        $sortedUsers = $appEntries->groupBy('user_id')->sortByDesc(function ($query) {
                            return $query->sum('totalSeconds');
                        });
                    @endphp
                    <tr class="group-row">
                        <td colspan="@role('manager|organizer') 3 @else 2 @endrole">
                            Usage Report for {{ __($appName) }}
                        </td>
                    </tr>
                    @foreach ($sortedUsers as $userEntries)
                        @php
                            $user = optional($userEntries->first())->user;
                            $totalUserRows = $userEntries->count(); // rows inside this app
                            $appMiddle = ceil($totalUserRows / 2); // app middle row
                            $appRowCounter = 0;
                        @endphp

                        @foreach ($userEntries->groupBy('created_on') as $date => $dateEntries)
                            @php
                                $appRowCounter++;
                            @endphp
                            <tr>
                                @role('manager|organizer')
                                <td style="border-top: 0; border-bottom: 0">
                                    @if ($appRowCounter == $appMiddle || $totalUserRows == 1)
                                        {{ toTitle($user->fullname) ?? __('Unknown Member') }}
                                    @endif
                                </td>
                                @endrole
                                <td>{{ showDateTime($date, 'Y-m-d') }}</td>
                                <td>
                                    @if($dateEntries->sum('totalSeconds') > 60)
                                    {{ formatSecondsToHoursMinutes($dateEntries->sum('totalSeconds')) }}
                                    @else
                                    &lt; 1m
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        @role('manager|organizer')
                            <tr class="single-user-total">
                                <td class="fw-bold text-end color" colspan="2">Total of {{ toTitle($user->fullname) ?? __('Unknown Member') }}</td>
                                <td class="fw-bold color">
                                    @if($userEntries->sum('totalSeconds') > 60)
                                    {{ formatSecondsToHoursMinutes($userEntries->sum('totalSeconds')) }}
                                    @else
                                    &lt; 1m
                                    @endif
                                </td>
                            </tr>
                        @endrole
                    @endforeach
                    <tr class="total-user-row">
                        <td class="fw-bold text-end" colspan="@role('manager|organizer') 2 @else 1 @endrole">Total Usage Report for {{ __($appName) }} </td>
                        <td class="fw-bold">
                            @if($appEntries->sum('totalSeconds') > 60)
                            {{ formatSecondsToHoursMinutes($appEntries->sum('totalSeconds')) }}
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

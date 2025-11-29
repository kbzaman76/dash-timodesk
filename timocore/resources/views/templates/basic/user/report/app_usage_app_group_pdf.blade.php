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
            line-height: 52px;
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
                    <th>@lang('App')</th>
                    <th>@lang('Total Members')</th>
                    <th>@lang('Total Time')</th>
                </tr>
            @else
                <tr>
                    <th>@lang('Member')</th>
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
                        <td>{{ $appEntries->groupBy('user_id')->count() }}</td>
                        <td>{{ formatSecondsToHoursMinutes($appEntries->sum('totalSeconds')) }}</td>
                    </tr>
                @else
                    @php
                        $sortedUsers = $appEntries->groupBy('user_id')->sortByDesc(function ($query) {
                            return $query->sum('totalSeconds');
                        });
                    @endphp
                    <tr class="group-row">
                        <td colspan="3">
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
                                <td style="border-top: 0; border-bottom: 0">
                                    @if ($appRowCounter == $appMiddle || $totalUserRows == 1)
                                        {{ toTitle($user->fullname) ?? __('Unknown Member') }}
                                    @endif
                                </td>
                                <td>{{ showDateTime($date, 'Y-m-d') }}</td>
                                <td>{{ formatSecondsToHoursMinutes($dateEntries->sum('totalSeconds')) }}</td>
                            </tr>
                        @endforeach
                            <tr class="single-user-total">
                                <td class="fw-bold text-end color" colspan="2">Total of {{ toTitle($user->fullname) ?? __('Unknown Member') }}</td>
                                <td class="fw-bold color">{{ formatSecondsToHoursMinutes($userEntries->sum('totalSeconds')) }}
                                </td>
                            </tr>
                    @endforeach
                    <tr class="total-user-row">
                        <td class="fw-bold text-end" colspan="2">Total Usage Report for {{ __($appName) }} </td>
                        <td class="fw-bold">{{ formatSecondsToHoursMinutes($appEntries->sum('totalSeconds')) }}</td>
                    </tr>
                @endif
            @endforeach
        </tbody>
    </table>
</body>

</html>

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

    <div class="header">
        <div>
            <h1>{{ $organization->name ?? __('Organization') }}</h1>
            <p>@lang('App Usage Report')</p>
            @if ($rangeText)
                <p>@lang('Period'): </p>
            @endif
        </div>
        <div class="meta">
            <strong>@lang('Total Usage')</strong>
            <p>{{ $totalUsage }}</p>
        </div>
    </div>

    @if ($groupBy === 'date')
        <h3 class="section-title">@lang('Grouped by Date') -
            {{ $dataType === 'collapsed' ? __('Summary') : __('Detailed') }}</h3>

        <table>
            <thead>
                @if ($dataType === 'collapsed')
                    <tr>
                        <th>@lang('Date')</th>
                        <th>@lang('Total Members')</th>
                        <th>@lang('Total App Used')</th>
                        <th>@lang('Total Time')</th>
                    </tr>
                @else
                    <tr>
                        <th>@lang('Date')</th>
                        <th>@lang('App')</th>
                        <th>@lang('Member')</th>
                        <th>@lang('Total Time')</th>
                    </tr>
                @endif
            </thead>
            <tbody>
                @foreach ($apps->groupBy('created_on') as $date => $items)
                    @if ($dataType === 'collapsed')
                        @php
                            $memberCount = $items->groupBy('user_id')->count();
                            $appCount = $items->groupBy('app_name')->count();
                            $totalSeconds = $items->sum('totalSeconds');
                        @endphp
                        <tr>
                            <td>{{ showDateTime($date, 'Y-m-d') }}</td>
                            <td>{{ $memberCount }}</td>
                            <td>{{ $appCount }}</td>
                            <td>{{ formatSecondsToHoursMinutes($totalSeconds) }}</td>
                        </tr>
                    @else
                        @php
                            $apps = $items->groupBy('app_name')->sortByDesc(function ($app) {
                                return $app->sum('totalSeconds');
                            });
                        @endphp

                        @foreach ($apps as $appName => $appData)
                            @php
                                $sortedUserApps = $appData->groupBy('app_name')->sortByDesc(function ($query) {
                                    return $query->sum('totalSeconds');
                                });
                            @endphp

                            @foreach ($sortedUserApps as $appName => $appData)
                                @php
                                    $members = $appData->groupBy('user_id')->sortByDesc(function ($member) {
                                        return $member->sum('totalSeconds');
                                    });
                                    $totalMembers = $members->count();
                                @endphp

                                @foreach ($members as $userId => $userApps)
                                    @php
                                        $user = optional($userApps->first())->user;
                                    @endphp
                                    <tr>
                                        @if (ceil($totalMembers / 2) == $loop->iteration || $totalMembers == 1)
                                            <td style="border-top: 0; border-bottom: 0">
                                                {{ showDateTime($date, 'Y-m-d') }}
                                            </td>
                                            <td style="border-top: 0; border-bottom: 0">{{ $appName }}</td>
                                        @else
                                            <td style="border-top: 0; border-bottom: 0"></td>
                                            <td style="border-top: 0; border-bottom: 0"></td>
                                        @endif
                                        <td>{{ toTitle($user->fullname) ?? __('Unknown Member') }}</td>
                                        <td>{{ formatSecondsToHoursMinutes($appData->sum('totalSeconds')) }}</td>
                                    </tr>
                                @endforeach
                            @endforeach
                            <tr>
                                <td colspan="2" class="fw-bold text-end">Total</td>
                                <td class="fw-bold">{{ $totalMembers }} Members</td>
                                <td class="fw-bold">{{ formatSecondsToHoursMinutes($appData->sum('totalSeconds')) }}
                                </td>
                            </tr>
                        @endforeach
                    @endif
                @endforeach
            </tbody>
        </table>
    @elseif ($groupBy === 'member')
        <h3 class="section-title">@lang('Grouped by Member') -
            {{ $dataType === 'collapsed' ? __('Summary') : __('Detailed') }}</h3>
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
                        <th>@lang('Member')</th>
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
                                <td style="border-top: 0; border-bottom: 0">
                                    @if ($userRowCounter == $userMiddle || $totalUserRows == 1)
                                        {{ toTitle($user->fullname) ?? __('Unknown Member') }}
                                    @endif
                                </td>
                                <td style="border-top: 0; border-bottom: 0">
                                    @if ($appRowCounter == $appMiddle || $totalAppRows == 1)
                                        {{ $appName }}
                                    @endif
                                </td>

                                <td>{{ showDateTime($date, 'Y-m-d') }}</td>
                                <td>{{ formatSecondsToHoursMinutes($dateEntries->sum('totalSeconds')) }}</td>
                            </tr>
                        @endforeach
                        <tr>
                            <td style="border-top: 0; border-bottom: 0"></td>
                            <td class="fw-bold text-end" colspan="2">Total</td>
                            <td class="fw-bold">{{ formatSecondsToHoursMinutes($appEntries->sum('totalSeconds')) }}
                            </td>
                        </tr>
                    @endforeach
                    <tr>
                        <td class="fw-bold text-end" colspan="3">Total</td>
                        <td class="fw-bold">{{ formatSecondsToHoursMinutes($userApps->sum('totalSeconds')) }}</td>
                    </tr>
                @endforeach

            </tbody>
        </table>
    @else
        <h3 class="section-title">@lang('Grouped by App') -
            {{ $dataType === 'collapsed' ? __('Summary') : __('Detailed') }}</h3>
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
                        <th>@lang('App')</th>
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

                            // Total rows for this user (sum of all dates inside all apps)
                            $totalAppRows = $sortedUsers->sum(
                                fn($appEntries) => $appEntries->groupBy('created_on')->count(),
                            );

                            $userRowCounter = 0; // count row for user middle calculation
                            $userMiddle = ceil($totalAppRows / 2);
                        @endphp
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
                                    $userRowCounter++;
                                @endphp
                                <tr>

                                    <td style="border-top: 0; border-bottom: 0">
                                        @if ($userRowCounter == $userMiddle || $totalAppRows == 1)
                                            {{ $appName }}
                                        @endif
                                    </td>
                                    <td style="border-top: 0; border-bottom: 0">
                                        @if ($appRowCounter == $appMiddle || $totalUserRows == 1)
                                            {{ toTitle($user->fullname) ?? __('Unknown Member') }}
                                        @endif
                                    </td>
                                    <td>{{ showDateTime($date, 'Y-m-d') }}</td>
                                    <td>{{ formatSecondsToHoursMinutes($dateEntries->sum('totalSeconds')) }}</td>
                                </tr>
                            @endforeach
                            <tr>
                                <td style="border-top: 0; border-bottom: 0"></td>
                                <td class="fw-bold text-end" colspan="2">Total</td>
                                <td class="fw-bold">{{ formatSecondsToHoursMinutes($userEntries->sum('totalSeconds')) }}
                                </td>
                            </tr>
                        @endforeach
                        <tr>
                            <td class="fw-bold text-end" colspan="3">Total</td>
                            <td class="fw-bold">{{ formatSecondsToHoursMinutes($appEntries->sum('totalSeconds')) }}</td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    @endif
</body>

</html>

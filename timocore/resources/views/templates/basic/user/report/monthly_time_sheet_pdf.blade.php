<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Monthly Overview</title>


    <style>
        @page {
            size: 11.7in 8.27in;
            margin: 0.5in 0.3in;
        }

        body {
            font-family: "Inter", sans-serif;
            font-size: 2px;
            color: #222;
            overflow: hidden;
        }

        table {
            width: 100%;
        }

        th {
            padding: 4px 0px;
            text-align: center;
        }

        td {
            border: 1px solid #eaeaea;
            padding: 0px;
            text-align: center;
        }

        thead th {
            background: #ff6a00;
            color: #fff;
            font-weight: 700;
            font-size: 30px;
        }


        td.name {
            text-align: left;
            font-weight: 600;
            font-size: 36px;
            line-height: 26px;
        }

        td.day {
            background: #ffffff;
        }

        td.day.empty {
            background: #ffecec;
        }

        td.avg.below {
            color: #c0392b;
        }



        .vtxt {
            display: inline-block;
            transform: rotate(-90deg);
            margin-top: 20px;
            padding: 0;
            font-weight: 600;
            color: #333;
            font-size: 28px;
        }

        .last-two {
            font-weight: 600;
            font-size: 38px;
        }

        .avg {
            font-weight: 600;
            font-size: 32px;
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

        .orgname {
            font-size: 76px;
            line-height: 70px;
            font-weight: 800;
            color: #ff6a00;
            font-family: "Urbanist", sans-serif;
        }

        .main-title {
            font-size: 50px;
            line-height: 30px;
            font-weight: 700;
            color: #030712;
            text-align: center;
        }

        .mark-below {
            background-color: #ff26004d !important;
        }

        .mark-above {
            background-color: #29a84733 !important;
        }
    </style>
</head>

<body>


    <h1 class="main-title">Monthly Overview of {{ $monthTitle }}</h1>


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

    <table class="overview-table">
        <thead class="thead-short">
            <tr>
                <th class="name" style="width: 280px;">@lang('Name')</th>
                @for ($d = 1; $d <= $daysInMonth; $d++)
                    <th style="width:30px;">{{ $d }}</th>
                @endfor
                <th>@lang('Avg')</th>
                <th>@lang('Total')</th>
                <th>@lang('Days')</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($users as $user)
                @php
                    $total = $userStats[$user->id]['total'] ?? 0;
                    $days = $userStats[$user->id]['days'] ?? 0;
                    $avgSec = $days > 0 ? floor($total / $days) : 0;
                @endphp
                <tr>
                    <td class="name" style="width: 280px; padding: 10px 10px 10px 30px;">{{ toTitle($user->fullname) }}</td>

                    @for ($d = 1; $d <= $daysInMonth; $d++)
                        @php
                            $seconds = $groupedTracks[$user->id][$d] ?? 0;
                            $display = $seconds > 0 ? formatSecondsToHoursMinutes($seconds) : '';
                        @endphp
                        <td class="day {{ $display === '' ? 'empty' : '' }} {{ ($seconds < $belowTime && $display) ? 'mark-below' : ''}} {{ ($seconds > $aboveTime && $display) ? 'mark-above' : ''}}" style="width:60px; height:140px;">
                            @if ($display === '')
                                {{-- keep empty cell short --}}
                            @else
                                <span class="vtxt">{{ $display }}</span>
                            @endif
                        </td>
                    @endfor

                    <td class="avg {{ ($avgSec < $belowTime && $avgSec) ? 'mark-below' : ''}} {{ ($avgSec > $aboveTime && $avgSec) ? 'mark-above' : ''}}" style="width:40px">
                        <span style="display: inline-block;transform: rotate(-90deg);">{{ $avgSec > 0 ? formatSecondsToHoursMinutes($avgSec) : '-' }}</span>
                    </td>
                    <td class="last-two" style="width:80px;">{{ formatSecondsToHoursMinutes($total) }}</td>
                    <td class="last-two" style="width:80px;">{{ $days }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ 1 + $daysInMonth + 3 }}" style="text-align:center">{{ __($emptyMessage) }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>

</html>

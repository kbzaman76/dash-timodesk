<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>{{ $pageTitle }}</title>

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
            font-size: 12px;
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
        .main-title {
            font-size: 18px;
            line-height: 10px;
            font-weight: 700;
            color: #030712;
            text-align: center;
            margin-bottom: 10px;
        }
    </style>
</head>

<body>
    <h1 class="main-title">{{ $pageTitle }}</h1>

    <div class="top-content-area">
        <div class="top-content">
            <div class="left-content" style="width:4.5in">
                <span class="orgname">{{ $organization->name }}</span>
                @if (!empty($periodLabel ?? null))
                    <div class="meta-text">
                        {{ $periodLabel }}
                    </div>
                @endif
            </div>
            <div class="right-content">
                <img src="{{ siteLogo('dark') }}" alt="@lang('Logo')" width="120">
            </div>
        </div>
    </div>

    <table class="overview-table">
        <thead>
            <tr>
                <th style="width: 5%;">@lang('Rank')</th>
                <th style="width: 39%;">@lang('Member')</th>
                <th style="width: 16%;">@lang('Activity Percentage')</th>
                <th style="width: 20%;">@lang('Average Time')</th>
                <th style="width: 20%;">@lang('Total Time')</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($performers as $performer)
                <tr class="table-row-alt">
                    <td class="member-cell">
                        {{ $loop->iteration }}
                    </td>
                    <td class="member-cell">
                        {{ toTitle($performer->user->fullname) }}
                    </td>
                    <td class="activity-cell">
                        {{ (int) $performer->avgActivity }}%
                    </td>
                    <td class="worked-cell">
                        {{ formatSeconds($performer->totalSeconds / $performer->totalDates) }}
                    </td>
                    <td class="worked-cell">
                        {{ formatSeconds($performer->totalSeconds) }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>

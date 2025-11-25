<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>{{ $pageTitle }}</title>

    <style>
        @page {
            size:  8.27in 11.7in;
            margin: 0.5in 0.3in;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: "Inter", sans-serif;
            font-size: 2px;
            color: #222;
            overflow: hidden;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            padding: 4px 0;
            text-align: center;
        }

        td {
            border: 1px solid #eaeaea;
            padding: 0;
            text-align: center;
        }

        thead th {
            background: #ff6a00;
            color: #fff;
            font-weight: 700;
            font-size: 36px;
            /* line-height: 20px; */
        }

        .main-title {
            font-size: 50px;
            line-height: 30px;
            font-weight: 700;
            color: #030712;
            text-align: center;
            margin-bottom: 20px;
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
            font-size: 40px;
            line-height: 40px;
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

        .meta-text {
            font-size: 40px;
            line-height: 26px;
            color: #555;
            margin-top: 8px;
        }

        .overview-table {
            margin-top: 20px;
        }

        .overview-table thead th:first-child {
            text-align: left;
            padding-left: 30px;
        }

        .member-cell {
            text-align: left;
            padding: 10px 10px 10px 30px;
            font-weight: 600;
            font-size: 40px;
            line-height: 28px;
        }

        .member-inner {
            display: inline-flex;
            align-items: center;
            gap: 16px;
        }

        .member-avatar {
            --size: 70px;
            width: var(--size);
            height: var(--size);
            border-radius: 50%;
            object-fit: cover;
        }

        .member-avatar-fallback {
            --size: 70px;
            width: var(--size);
            height: var(--size);
            border-radius: 50%;
            background: #ffecec;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            font-weight: 700;
            color: #ff6a00;
        }

        .member-name {
            display: inline-block;
            max-width: 420px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .activity-cell {
            font-weight: 600;
            font-size: 40px;
        }

        .worked-cell {
            font-weight: 700;
            font-size: 40px;
        }

        .progress-wrapper {
            margin-top: 6px;
        }

        .progress {
            width: 100%;
            height: 20px;
            border-radius: 10px;
            background: #f2f2f2;
            overflow: hidden;
        }

        .progress-bar {
            height: 100%;
            background: #ff6a00;
        }

        .table-row-alt:nth-child(odd) {
            background: #fdfdfd;
        }

        .no-data-cell {
            padding: 40px 0;
            font-size: 40px;
            font-weight: 500;
            text-align: center;
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
                <img src="{{ siteLogo('dark') }}" alt="@lang('Logo')" width="420">
            </div>
        </div>
    </div>

    <table class="overview-table">
        <thead>
            <tr>
                <th style="width: 5%;">@lang('Rank')</th>
                <th style="width: 40%;">@lang('Member')</th>
                <th style="width: 15%;">@lang('Activity Percentage')</th>
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

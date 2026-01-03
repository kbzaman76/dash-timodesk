@extends('Template::layouts.master')

@section('content')
    <div class="screen-filter screenshots-filter">
        <div class="datepicker-wrapper">
            <div class="datepicker-arrow">
                <button class="datepicker-arrow-btn js-prev-day" type="button">
                    <i class="fa-solid fa-arrow-left-long"></i>
                </button>
                <button class="datepicker-arrow-btn js-next-day" type="button">
                    <i class="fa-solid fa-arrow-right-long"></i>
                </button>
            </div>
            <div class="datepicker-inner">
                <span class="icon">
                    <x-icons.calendar />
                </span>
                <input id="dateRange" type="text" value="{{ now()->format('m/d/Y') }}"
                    class="form--control md-style datepicker2-single-max-today" autocomplete="off" />
            </div>
        </div>

        <div class="screen-filter-center">
            <div class="screen-filter-tab">
                <button class="screen-filter-tab-btn js-mode active" data-mode="10min">
                    @lang('Every 10 Min')
                </button>
                <button class="screen-filter-tab-btn js-mode" data-mode="all">
                    @lang('All Screenshots')
                </button>
            </div>
        </div>

        @role('manager|organizer')
            <div class="screen-filter-right">
                <div class="select2-wrapper">
                    <select class="img-select2 js-member">
                        @foreach ($members as $memberSingle)
                            <option data-src="{{ $memberSingle->image_url }}" value="{{ $memberSingle->uid }}"
                                @selected($memberId && $memberSingle->id == $memberId)>
                                {{ toTitle($memberSingle->fullname) }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        @endrole
    </div>

    <div class="row gy-4 mb-4">
        <div class="col-xxl-8 col-md-7">
            <div class="screen-activity js-benchmark">
                <h6 class="screen-activity-title">
                    <span class="icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16"
                            fill="none">
                            <path
                                d="M1.83333 5.9987C1.83333 5.35436 2.35567 4.83203 3 4.83203H3.66667C4.311 4.83203 4.83333 5.35436 4.83333 5.9987V12.6653C4.83333 13.3097 4.311 13.832 3.66667 13.832H3C2.35567 13.832 1.83333 13.3097 1.83333 12.6653V5.9987Z"
                                fill="currentColor" />
                            <path
                                d="M6.5 3.33317C6.5 2.68884 7.02233 2.1665 7.66667 2.1665H8.33333C8.97767 2.1665 9.5 2.68884 9.5 3.33317V12.6662C9.5 13.3105 8.97767 13.8328 8.33333 13.8328H7.66667C7.02233 13.8328 6.5 13.3105 6.5 12.6662V3.33317Z"
                                fill="currentColor" />
                            <path
                                d="M11.1667 7.9987C11.1667 7.35436 11.689 6.83203 12.3333 6.83203H13C13.6443 6.83203 14.1667 7.35436 14.1667 7.9987V12.6654C14.1667 13.3097 13.6443 13.832 13 13.832H12.3333C11.689 13.832 11.1667 13.3097 11.1667 12.6654V7.9987Z"
                                fill="currentColor" />
                        </svg>
                    </span>
                    Activity Benchmark
                </h6>
                <div class="screen-activity-wrapper">
                    <div class="screen-activity-card">
                        <h6 class="title">@lang('Work Time')</h6>
                        <h3 class="time js-work-time">--</h3>
                    </div>
                    <div class="screen-activity-card">
                        <h6 class="title">@lang('Average Activity')</h6>
                        <h3 class="time js-avg-activity">--</h3>
                    </div>
                    <div class="screen-activity-card">
                        <h6 class="title">@lang('Screenshot Taken')</h6>
                        <h3 class="time js-ss-count">--</h3>
                    </div>
                    <div class="screen-activity-card">
                        <h6 class="title">@lang('Task Tracked')</h6>
                        <h3 class="time js-task-count">--</h3>
                    </div>
                </div>
            </div>
            <div class="screen-activity d-none" id="benchmark-skeleton">
                <h6 class="screen-activity-title">
                    <span class="icon skeleton-box" style="width:24px; height:24px;"></span>
                    <span class="skeleton-box" style="width:120px; height:16px;"></span>
                </h6>
                <div class="screen-activity-wrapper">
                    @for ($i = 0; $i < 4; $i++)
                        <div class="screen-activity-card">
                            <span class="skeleton-box" style="width:90px; height:14px; display:block;"></span>
                            <span class="skeleton-box mt-2" style="width:80px; height:22px; display:block;"></span>
                        </div>
                    @endfor
                </div>
            </div>
        </div>
        <div class="col-xxl-4  col-md-5">
            <div class="screen-app-card js-app-usage">
                <div class="screen-app-card-header">
                    <h6 class="title">Top App Usage</h6>
                </div>
                <div class="screen-app-card-wrapper">
                    <ul class="screen-app-card-list" id="app-usage-list">
                        <li class="text-muted small">@lang('Loading')</li>
                    </ul>
                    <div class="screen-app-card-chart" id="screen-app-chart"></div>
                </div>
            </div>
            <div class="screen-app-card d-none" id="app-usage-skeleton">
                <div class="screen-app-card-header">
                    <span class="skeleton-box" style="width:90px; height:16px;"></span>
                </div>
                <div class="screen-app-card-wrapper">
                    <ul class="screen-app-card-list w-100">
                        @for ($i = 0; $i < 4; $i++)
                            <li class="item">
                                <div class="content">
                                    <span class="image skeleton-box" style="width:16px; height:16px;"></span>
                                    <p class="name"><span class="skeleton-box" style="width:80px; height:12px;"></span>
                                    </p>
                                </div>
                                <span class="circle skeleton-box" style="width:8px; height:8px;"></span>
                            </li>
                        @endfor
                    </ul>
                    <div class="screen-app-card-chart">
                        <span class="skeleton-box"
                            style="width:100%; height:100%; display:block; border-radius: 500px 500px 0 0;"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div id="screenshot-grid"></div>

    <div id="screenshotGallery"></div>
@endsection

@push('style-lib')
    <link rel="stylesheet" href="{{ asset(activeTemplate(true) . 'css/slick.css') }}">
    <link rel="stylesheet" href="{{ asset(activeTemplate(true) . 'css/magnify-popup.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/admin/css/daterangepicker.css') }}">
    <link rel="stylesheet" href="{{ asset(activeTemplate(true) . 'css/lightbox.min.css') }}">
@endpush

@push('script-lib')
    <script src="{{ asset('assets/admin/js/moment.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/daterangepicker.min.js') }}"></script>
    <script src="{{ asset(activeTemplate(true) . 'js/slick.min.js') }}"></script>
    <script src="{{ asset(activeTemplate(true) . 'js/magnify-popup.js') }}"></script>
    <script src="{{ asset(activeTemplate(true) . 'js/lightbox.min.js') }}"></script>
    <script src="{{ asset(activeTemplate(true) . 'js/echarts.js') }}"></script>
    <script src="{{ asset(activeTemplate(true) . 'js/chart.js') }}?v=1.1.2"></script>
@endpush

@push('script')
    <script>
        (function($) {
            "use strict";

            $(document).ready(function() {
                const url = `{{ route('user.activity.screenshot.load') }}`;
                const $date = $('#dateRange');

                const appColors = ['#CA3500', '#FF6900', '#FFB86A', '#FFEDD4'];
                const $benchmark = $('.js-benchmark');
                const $benchmarkSkeleton = $('#benchmark-skeleton');
                const $appUsage = $('.js-app-usage');
                const $appSkeleton = $('#app-usage-skeleton');

                let requestOnProgress = false;

                function updateSummary(summary = {}) {
                    $('.js-work-time').text(summary.work_time || '0h 00m');
                    const activity = typeof summary.avg_activity !== 'undefined' ? summary.avg_activity : 0;
                    $('.js-avg-activity').text(`${activity}%`);
                    $('.js-ss-count').text(summary.screenshot_count ?? 0);
                    $('.js-task-count').text(summary.task_count ?? 0);
                }

                function updateAppUsage(apps = []) {
                    const $list = $('#app-usage-list');
                    const chartId = 'screen-app-chart';

                    if (!$list.length) {
                        return;
                    }

                    if (!apps.length) {
                        $list.html('<li class="text-muted small">{{ __('No app usage for this day') }}</li>');
                        renderHalfDonutChart({
                            elementId: chartId,
                            data: []
                        });
                        return;
                    }

                    let itemsHtml = '';
                    const chartData = [];

                    apps.forEach(function(app, idx) {
                        const color = appColors[idx % appColors.length];
                        chartData.push({
                            value: Number(app.total_seconds || 0),
                            name: app.name || 'N/A',
                            itemStyle: {
                                color
                            },
                        });

                        itemsHtml += `
                            <li class="item">
                                <div class="content">
                                    <span class="image"><img src="${app.app_icon}"></span>
                                    <p class="name">${app.name || 'N/A'}</p>
                                </div>
                                <span class="circle" style="background-color:${color};"></span>
                            </li>
                        `;
                    });

                    $list.html(itemsHtml);
                    renderHalfDonutChart({
                        elementId: chartId,
                        data: chartData
                    });
                }

                function showBenchmarkSkeleton() {
                    $benchmark.addClass('d-none');
                    $appUsage.addClass('d-none');
                    $benchmarkSkeleton.removeClass('d-none');
                    $appSkeleton.removeClass('d-none');
                }

                function hideBenchmarkSkeleton() {
                    $benchmarkSkeleton.addClass('d-none');
                    $appSkeleton.addClass('d-none');
                }

                function toggleSummaryVisibility(hasData) {
                    hideBenchmarkSkeleton();
                    if (hasData) {
                        $benchmark.removeClass('d-none');
                        $appUsage.removeClass('d-none');
                    } else {
                        $benchmark.addClass('d-none');
                        $appUsage.addClass('d-none');
                    }
                }

                function skeletonHTML() {
                    let html = '<div class="screenshot-wrapper">';

                    for (let g = 0; g < 3; g++) {
                        html += `
                            <div class="mb-3 mt-2 screenshot__title">
                                <h5 class="fw-semibold"><span class="skeleton-box" style="height:25px; width:300px"></span></h5>
                            </div>
                            <div class="screenshot-wrapper-block">
                                <div class="row g-3">
                        `;

                        for (let i = 0; i < 6; i++) {
                            html += `
                                <div class="col-xxl-2 col-md-4 col-sm-6 col-xsm-6 custom__col">
                                    <div class="screenshot-item">
                                        <button class="screenshot-item-thumb skeleton-box">
                                        </button>
                                        <span class="screenshot-item-duration skeleton-box">
                                            <span class="skeleton-box"></span>
                                        </span>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="skeleton-box" style="width:40%; height:12px;"></small>
                                            <small class="skeleton-box" style="width:40%; height:12px;"></small>
                                        </div>
                                        <div class="skeleton-box mt-1" style="height:8px;"></div>
                                    </div>
                                </div>
                            `;
                        }

                        html += `</div></div>`;
                    }

                    html += '</div>';
                    return html;
                }

                function loadGrid() {
                    if(requestOnProgress === true) {
                        return;
                    }
                    requestOnProgress = true;

                    console.log(requestOnProgress, 'call');


                    const date = $date.val();

                    $(document).find('.datepicker-arrow-btn, .screen-filter-tab-btn, .datepicker2-single-max-today, .js-member').prop('disabled', true);

                    let user = $('.js-member').val();
                    if(!user){
                        user = '{{ auth()->user()->uid }}';
                    }
                    const mode = $('.js-mode.active').data('mode') || '10min';

                    $('#screenshot-grid').html(skeletonHTML());
                    showBenchmarkSkeleton();

                    $.get(url, {
                            date,
                            user,
                            mode
                        },
                        function(response) {
                            $('#screenshot-grid').html(response.view);
                            const summaryData = response.summary || {};
                            const hasData = (summaryData.work_seconds || 0) > 0 ||
                                (summaryData.screenshot_count || 0) > 0 ||
                                (summaryData.task_count || 0) > 0 ||
                                (summaryData.top_apps || []).length > 0;
                            updateSummary(summaryData);
                            updateAppUsage(summaryData.top_apps || []);
                            toggleSummaryVisibility(hasData);

                            initLazy();

                            $(document).find('.datepicker-arrow-btn, .screen-filter-tab-btn, .datepicker2-single-max-today, .js-member').prop('disabled', false);

                            updateNav();
                            requestOnProgress = false;
                        }
                    );
                }

                function updateNav() {
                    const selected = moment($date.val(), 'MM/DD/YYYY').startOf('day');
                    const today = moment().startOf('day');
                    const isToday = selected.isSame(today);
                    $(document).find('.js-next-day').prop('disabled', isToday);
                }

                updateNav();

                $('#dateRange').on('change', function() {
                    loadGrid();
                }).change();

                $('.js-member').on('change', loadGrid);
                $('.js-mode').on('click', function() {
                    if ($(this).hasClass('active')) {
                        return;
                    }
                    $('.js-mode').removeClass('active');
                    $(this).addClass('active');
                    loadGrid();
                });

                function setDate(momentDate) {
                    const max = moment().startOf('day');
                    if (momentDate.isAfter(max)) {
                        momentDate = max.clone();
                    }
                    const drp = $date.data('daterangepicker');
                    if (drp) {
                        drp.setStartDate(momentDate.toDate());
                        drp.setEndDate(momentDate.toDate());
                    }
                    $date.val(momentDate.format('MM/DD/YYYY')).trigger('change');
                }

                $(document).on('click', '.js-prev-day', function() {
                    const current = moment($date.val(), 'MM/DD/YYYY').startOf('day');
                    setDate(current.subtract(1, 'day'));
                });
                $(document).on('click', '.js-next-day', function() {
                    const current = moment($date.val(), 'MM/DD/YYYY').startOf('day');
                    setDate(current.add(1, 'day'));
                });
            });

            let actionUrl = `{{ route('user.activity.screenshot.slice.load') }}`;

            $(document).on('click', '.loadSliceScreenshot', function() {
                let user = $('.js-member').val();
                if(!user){
                    user = '{{ auth()->user()->uid }}';
                }
                let date = $(this).data('date');
                let start = $(this).data('start');

                let data = {
                    user,
                    date,
                    start
                };

                $.get(actionUrl, data, function(response) {
                    let gallery = $('#screenshotGallery');
                    gallery.empty();

                    if (response.length === 0) {
                        gallery.html('');
                        return;
                    }

                    response.forEach(function(item) {
                        let link = $('<a/>', {
                            href: item.url,
                            'data-lightbox': 'screenshots',
                            'data-title': `Project: ${item.project?.title || 'N/A'} | Taken at: ${formatTime(item.taken_at)}`,
                            style: 'display:none;'
                        });
                        gallery.append(link);
                    });

                    if (typeof lightbox !== 'undefined') {
                        lightbox.init();
                        lightbox.option({
                            'resizeDuration': 200,
                            'wrapAround': true,
                        });
                    }

                    gallery.find('a[data-lightbox]').first().trigger('click');

                });
            });

            function formatTime(time) {
                time = time.split(' ');
                return time[1] + ' ' + time[2];
            }

        })(jQuery);
    </script>
@endpush




@push('style')
    <style>
        :root {
            --mute-color: #6A7282;
        }

        .screen-app-card {
            padding: 16px 16px 0;
            box-shadow: 0px 3px 5px hsl(var(--black) / 0.05);
            background-color: hsl(var(--white));
            border-radius: 8px;
            height: 100%;
        }

        .screen-app-card-wrapper {
            display: flex;
            align-items: flex-end;
            gap: 32px;
        }

        .screen-app-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 16px;
        }

        .screen-app-card-list {
            flex: 1;
            padding-bottom: 16px;
        }

        .screen-app-card-list .item {
            --circle-color: #CA3500;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            cursor: pointer;
        }

        .screen-app-card-list .item:not(:last-child) {
            margin-bottom: 16px;
        }

        .screen-app-card-list .item:nth-child(2) {
            --circle-color: #FF6900;
        }

        .screen-app-card-list .item:nth-child(3) {
            --circle-color: #FFB86A;
        }

        .screen-app-card-list .item:nth-child(4) {
            --circle-color: #FFEDD4;
        }

        .screen-app-card-list .content {
            flex: 1;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .screen-app-card-list .image {
            height: 16px;
            width: 16px;
            overflow: hidden;
            flex-shrink: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background-color: hsl(var(--base) / 0.08);
            font-size: 10px;
            font-weight: 700;
            color: #030712;
        }

        .screen-app-card-list .name {
            font-size: 14px;
            color: #030712;
            line-height: 1;
            max-width: 130px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .screen-app-card-list .circle {
            height: 8px;
            width: 8px;
            border-radius: 50%;
            background-color: var(--circle-color);
        }

        .screen-app-card-list .duration {
            font-size: 13px;
            color: #606576;
            white-space: nowrap;
        }

        .screen-app-card-chart {
            width: 260px;
            height: 130px;
        }

        .screen-app-card .title {
            color: var(--mute-color);
            font-weight: 500;
        }

        .screen-app-card .link {
            color: #D1D5DC;
        }

        .screen-app-card .link:hover {
            cursor: pointer;
            color: hsl(var(--base));
        }

        .screen-activity {
            padding: 24px;
            box-shadow: 0px 3px 5px hsl(var(--black) / 0.05);
            background-color: hsl(var(--white));
            border-radius: 8px;
            height: 100%;
        }

        .screen-activity-title {
            display: flex;
            align-items: center;
            gap: 4px;
            color: var(--mute-color);
            font-weight: 500;
            margin-bottom: 24px;
        }

        .screen-activity-title .icon {
            --size: 24px;
            height: var(--size);
            width: var(--size);
            border-radius: 4px;
            color: hsl(var(--base));
            background-color: hsl(var(--base) / .1);
            display: grid;
            place-content: center;
        }

        .screen-activity-wrapper {
            display: flex;
            justify-content: space-between;
        }

        .screen-activity-card {
            min-width: 150px;
        }

        .screen-activity-card:not(:first-child) {
            padding-left: 16px;
            border-left: 1px solid #E5E7EB;
        }

        .screen-activity-card .title {
            color: var(--mute-color);
            font-weight: 500;
            margin-bottom: 4px;
        }

        .screen-activity-card .time {
            font-weight: 700;
            color: #030712;
        }

        .screen-app-chart {
            height: 200px;
        }

        @media (max-width: 1799px) {

            .screen-app-card-wrapper {
                gap: 12px;
            }

            .screen-app-card-list .item {
                gap: 12px;
            }

            .screen-app-card-list .name {
                font-size: 13px;
                white-space: nowrap;
                max-width: 80px;
                overflow: hidden;
                text-overflow: ellipsis;
            }
        }

        @media (max-width: 1599px) {
            .screen-activity-card .title {
                font-size: 16px;
            }

            .screen-activity-card .time {
                font-size: 2rem;
            }

            .screen-activity {
                padding: 20px
            }

            .screen-app-card-wrapper {
                gap: 12px;
            }

            .screen-app-card-list .item {
                gap: 12px;
            }

            .screen-app-card-chart {
                width: 180px;
                height: 90px;
            }
        }

        @media (max-width: 1399px) {

            .screen-activity {
                padding: 16px
            }

            .screen-activity-card .title {
                font-size: 14px;
            }

            .screen-activity-card .time {
                font-size: 1.75rem;
            }

        }

        @media (max-width: 1299px) {
            .screen-activity-wrapper {
                flex-wrap: wrap;
                gap: 16px;
            }

            .screen-activity-card {
                min-width: unset;
                width: calc(100% / 2 - 8px);
            }

            .screen-activity-card:nth-child(3) {
                padding-left: 0;
                border: 0;
            }

            .screen-activity-card .time {
                font-size: 1.5rem;
            }
        }

        @media (max-width: 1199px) {
            .screen-app-card-chart {
                height: 100px;
            }
        }

        @media (min-width: 768px) and (max-width: 991px) {
            .screen-app-card-chart {
                width: 100%;
                height: 140px;
            }

            .screen-app-card-list {
                display: none;
            }
        }

        @media (max-width: 767px) {
            .screen-app-card-chart {
                height: 120px;
                width: 260px;
            }
        }

        @media (max-width: 575px) {
            .screen-app-card-chart {
                height: 100px;
                width: 200px;
            }
        }

        @media (max-width: 375px) {
            .screen-activity-wrapper {
                gap: 24px;
            }

            .screen-activity-card {
                min-width: unset;
                width: 100%;
                padding-left: 0 !important;
                border: 0 !important;
            }
        }


        @media (min-width: 900px) and (max-width: 1550px) {
            .custom__col {
                flex: 0 0 auto;
                width: 33.3%;
            }

            .screenshot-item-thumb {
                --screenshot-img-height: 180px;
            }
        }

        .lb-dataContainer {
            background-color: #f8f9fa !important;
        }

        .lb-data .lb-details {
            padding-left: 15px !important;
            color: #000;
        }

        .lb-data .lb-close {
            transform: translate(-9px, 6px);
            filter: invert(0%) sepia(0%) saturate(7500%) hue-rotate(313deg) brightness(13%) contrast(107%);
        }
    </style>
@endpush

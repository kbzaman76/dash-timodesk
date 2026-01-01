@extends('Template::layouts.master')
@section('content')

    @role('organizer')
        @if ($unpaidInvoiceCount > 0)
            <div class="notice-wrapper">
                <div class="notice mb-3 mb-xxl-4">
                    <div class="alert alert--base align-items-center px-2 py-1 pe-3 gap-2" role="alert">
                        <div class="alert__icon">
                            <i class="fa-regular fa-bell"></i>
                        </div>
                        <div class="alert__content">
                            <p class="alert__desc">
                                @lang("You've") <strong class="fs-5">{{ $unpaidInvoiceCount }}</strong>
                                {{ $unpaidInvoiceCount == 1 ? __('unpaid invoice') : __('unpaid invoices') }}.
                                @lang('Please review and make the payment at your earliest convenience.')
                            </p>
                        </div>
                        <a href="{{ route('user.invoice.list') }}" class="btn btn--xsm btn--base">
                            @lang('Invoice List')
                        </a>
                    </div>
                </div>
            </div>
        @endif
    @endrole

    @if ($showOnboarding)
        <div class="row justify-content-center">
            <div class="col-xxl-10">

                @include('Template::user.dashboard.onboarding', ['steps' => $onboardingSteps])
            </div>
        </div>
    @else
        <div class="app-body-wrapper-dashboard">
            <div class="app-body-content">
                <div class="row g-3 g-xxl-4 mb-3 mb-xxl-4">
                    <div class="col-md-4">
                        <div class="summary-wrapper">
                            <div class="summary-wrapper-header">
                                <h5>@lang('Overview')</h5>
                                <x-date-filter :value="$summaryRange ?? ''" :label="$summaryLabel ?? ''" id="summary_filter" />
                            </div>
                            <div id="summary-stats"></div>
                        </div>
                    </div>

                    @role('staff')
                        <div class="col-md-4">
                            @include('Template::user.dashboard.project_timing')
                        </div>
                        <div class="col-md-4">
                            @include('Template::user.dashboard.app_analytics')
                        </div>
                    @endrole

                    @role('manager|organizer')
                        <div class="col-md-8">
                            <div class="card custom--card h-100">
                                <div class="card-header">
                                    <div class="flex-between gap-2 align-items-center w-100">
                                        <h6 class="card-title m-0">@lang('Top Activity Staff')</h6>
                                        <x-date-filter :value="$defaultDateRange ?? ''" :label="$defaultLabel ?? ''" id="top_activity_staff_filter" />
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div id="top-activity-staff-container" class="h-100"></div>
                                </div>
                            </div>
                        </div>
                    @endrole
                </div>

                <div class="row g-3 g-xxl-4 mb-3 mb-xxl-4">
                    <div class="col-md-{{ auth()->user()->isStaff() ? '12' : '8' }}">
                        <div class="card custom--card {{ auth()->user()->isStaff() ? 'screenshot__user' : '' }} h-100">
                            <div class="card-header">
                                <div class="flex-between gap-2">
                                    <h6 class="card-title">@lang('Recent Screenshots')</h6>
                                    <a href="{{ route('user.activity.screenshot.index') }}"
                                        class="btn btn--base btn--sm">@lang('View All')</a>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row g-3 g-xxl-4">
                                    @php
                                        $recentScreenshots = $organization->screenshots()->latest('taken_at')->limit(6)->get();
                                        if (auth()->user()->isStaff()) {
                                            $recentScreenshots = auth()
                                                ->user()
                                                ->screenshots()
                                                ->latest('taken_at')
                                                ->limit(8)
                                                ->get();
                                        }
                                    @endphp

                                    @forelse ($recentScreenshots as $screenshot)
                                        <div class="col-lg-{{ auth()->user()->isStaff() ? '3' : '4' }} col-sm-6 col-xsm-6">
                                            <div class="screenshot-item">
                                                @if (!auth()->user()->isStaff())
                                                    <div class="screenshot-item-header">
                                                        <span class="screenshot-item-user-thumb">
                                                            <img src="{{ $screenshot->user->image_url }}" alt="Image" />
                                                        </span>
                                                        <a href="{{ route('user.member.details', $screenshot->user->uid) }}" class="screenshot-item-name">{{ toTitle($screenshot->user->fullname) }}</a>
                                                    </div>
                                                @endif
                                                <div class="screenshot-item-thumb">
                                                    <a href="{{ $screenshot->url }}" data-lightbox="screenshots"
                                                        data-title="{{ $screenshot->user->fullname }} - {{ showDateTime($screenshot->taken_at, 'h:i A') }}">
                                                        <div class="overlay">
                                                            <span>@lang('View Image')</span>
                                                        </div>
                                                        <img class="fit-image lazy" data-src="{{ $screenshot->url }}"
                                                            src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw=="
                                                            alt="Screenshot">
                                                    </a>
                                                </div>
                                                <span class="screenshot-item-duration">
                                                    {{ showDateTime($screenshot->taken_at, 'h:i A') }}
                                                </span>
                                                <p class="screenshot-item-title" title="{{ $screenshot->project->title }}">
                                                    {{ str($screenshot->project->title)->limit(40, '...', true) }}
                                                </p>
                                            </div>
                                        </div>
                                    @empty
                                        <div
                                            class="d-flex ms-auto text-center justify-content-center flex-column align-items-center h-100 py-5">
                                            <img class="img-fluid w-25" src="{{ emptyImage('screenshots') }}"
                                                alt="No Data">
                                            <h6 class="mt-2 project-empty-title">No screenshots found</h6>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>

                    @role('organizer|manager')
                        <div class="col-md-4">
                            @include('Template::user.dashboard.project_timing')
                        </div>
                    @endrole
                </div>

                <div class="row g-3 g-xxl-4 mb-3 mb-xxl-4">
                    <div class="col-12">
                        <div class="card custom--card h-100">
                            <div class="card-header">
                                <div class="flex-between gap-2">
                                    <h6 class="card-title">@lang('Timing Chart')</h6>
                                    <x-date-filter :value="$defaultDateRange ?? ''" :label="$defaultLabel ?? ''" id="time_tracking_filter"
                                        :disable_options="['Last 6 Months', 'This Year', 'Today', 'Yesterday']" />
                                </div>
                            </div>
                            <div class="card-body">
                                <div id="timeTrackingChart" class="chart-container lg-style"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-3 g-xxl-4 mb-3 mb-xxl-4">
                    @role('manager|organizer')
                        <div class="col-md-4">
                            @include('Template::user.dashboard.app_analytics')
                        </div>
                        <div class="col-md-8">
                            <div class="card custom--card h-100">
                                <div class="card-header">
                                    <div class="flex-between gap-2">
                                        <h6 class="card-title">@lang('Average Activity')</h6>
                                        <x-date-filter :value="$defaultDateRange ?? ''" :label="$defaultLabel ?? ''" id="avg_activity_filter" />
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div id="TrackerVolumeChart" class="chart-container"></div>
                                </div>
                            </div>
                        </div>
                    @endrole
                </div>

                <div id="screenshotGallery"></div>
            </div>

            <div class="app-body-sidebar" id="app-body-sidebar">
                <div class="app-body-sidebar-wrapper">
                    @role('organizer|manager')
                        <div class="app-body-sidebar-block">
                            <div class="card custom--card performers__card h-100 dashboard-card">
                                <div class="card-header">
                                    <div class="flex-between gap-2">
                                        <h6 class="card-title">@lang('Top Performers')</h6>
                                        <x-date-filter :value="$performerDateRange ?? ''" :label="$performerLabel ?? ''" id="top_performer_filter" />
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div id="top-performer-container"></div>
                                    <a class="view-more-btn shown" href="{{ route('user.performer.top') }}">
                                        @lang('View All')
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="app-body-sidebar-block">
                            <div class="card custom--card h-100 performers__card dashboard-card">
                                <div class="card-header">
                                    <div class="flex-between gap-2">
                                        <h6 class="card-title">@lang('Low Performers')</h6>
                                        <x-date-filter :value="$performerDateRange ?? ''" :label="$performerLabel ?? ''" id="low_performer_filter" />
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div id="low-performer-container"></div>
                                    <a class="view-more-btn shown" href="{{ route('user.performer.low') }}">
                                        @lang('View All')
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endrole

                    @role('organizer')
                        <div class="app-body-sidebar-block">
                            <div class="card custom--card h-100">
                                <div class="card-header flex-between gap-2">
                                    <h6 class="card-title">@lang('Bill Details')</h6>
                                </div>
                                <div class="card-body">
                                    <div class="billing-box">
                                        @if ($trialActive)
                                            <p class="billing-box-trail">
                                                <span class="icon"><i class="las la-info-circle"></i></span>
                                                <span>@lang('Your free trial is active. Billing will start automatically after the trial period ends.')</span>
                                            </p>
                                            <div class="billing-box-progress">
                                                <span class="text">{{ number_format((float) $trialDaysLeft, 0) }}
                                                    @lang('days Left')</span>
                                                <div class="progress">
                                                    <div class="progress-bar bg--base"
                                                        style="width: {{ $trialPercentLeft }}%">
                                                    </div>
                                                </div>
                                                <small class="d-block mt-1">@lang('Total') {{ $trialTotalDays }}
                                                    @lang('days')</small>
                                            </div>
                                        @else
                                            <div class="billing-box-employee">
                                                <svg class="billing-box-employee-bg" xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 277 48" fill="currentColor">
                                                    <path
                                                        d="M-11 5C-11 2.23858 -8.76142 0 -6 0H271.129C275.4 0 277.705 5.01069 274.925 8.25404L242.352 46.254C241.402 47.3622 240.015 48 238.556 48H-6.00001C-8.76143 48 -11 45.7614 -11 43V5Z">
                                                    </path>
                                                </svg>
                                                @lang('Billable Members:') {{ $billableStaffCount }}
                                            </div>

                                            @if ($organizationDiscount)
                                                <div class="billing-box-discount">
                                                    <div class="billing-box-discount-text">
                                                        Available Coupon :
                                                        {{ getAmount($organizationDiscount->discount_percent) }}%
                                                    </div>
                                                    <p class="billing-box-amount flex-between mb-2">
                                                        Bill Amount :
                                                        <span class="text--dark fw-bold">{{ showAmount($totalBill) }}</span>
                                                    </p>
                                                    <p class="billing-box-amount flex-between mb-2">
                                                        Discount :
                                                        <span class="text--dark fw-bold">{{ showAmount($discount) }}</span>
                                                    </p>
                                                    <p class="billing-box-amount flex-between">
                                                        After Discount :
                                                        <span class="total"> {{ showAmount($afterDiscount) }}</span>
                                                    </p>
                                                </div>
                                            @endif

                                            @if (!$organizationDiscount)
                                                <p class="billing-box-amount">
                                                    Bill Amount :
                                                    <span class="total">{{ showAmount($totalBill) }}</span>
                                                </p>
                                            @endif

                                            <div class="billing-box-content">
                                                <p class="title">@lang('Next Bill Date')</p>
                                                <p class="date">
                                                    {{ \Carbon\Carbon::parse($organization->next_invoice_date)->format('d M, Y') }}
                                                </p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endrole
                </div>
            </div>
        </div>
    @endif
@endsection

@pushIf(auth()->user()->isStaff(), 'style')
<style>
    .app-body-content {
        width: 100%;
    }
</style>
@endPushIf
@push('style')
    <style>
        .no-performer {
            text-align: center;
        }

        .no-performer img {
            width: 180px;
        }
    </style>
@endpush

@if (!$showOnboarding)
    @push('style-lib')
        <link rel="stylesheet" href="{{ asset(activeTemplate(true) . 'css/lightbox.min.css') }}">
    @endpush

    @push('script-lib')
        <script src="{{ asset(activeTemplate(true) . 'js/echarts.js') }}"></script>
        <script src="{{ asset(activeTemplate(true) . 'js/chart.js') }}?v=1.1.2"></script>
        <script src="{{ asset(activeTemplate(true) . 'js/lightbox.min.js') }}"></script>
    @endpush

    @push('script')
        <script>
            (function($) {
                "use strict";

                $(document).ready(function() {
                    const timingsUrl = "{{ route('user.project.timings') }}";
                    const appUsesUrl = "{{ route('user.app.uses') }}";
                    const timeTrackingUrl = "{{ route('user.time.tracking') }}";
                    const activitySeriesUrl = "{{ route('user.activity.series') }}";
                    const topPerformersUrl = "{{ route('user.top.performers') }}";
                    const lowPerformersUrl = "{{ route('user.low.performers') }}";
                    const summaryUrl = "{{ route('user.summary.statistics') }}";
                    const topActivityStaffUrl = "{{ route('user.top.activity.staff') }}";

                    // Project Timing filter scoped listener
                    function loadProjectTiming(date = "") {
                        const $box = $('.project_timing_container');
                        const $projectModal = $('#project-timing-modal-container');
                        $box.html(
                            `

                            <div class="project-timer">
                                <ul class="project-timer-list">
                                    @for ($i = 0; $i < 7; $i++)
                                        <li class="project-timer-item">
                                            <div class="project-timer-item-top">
                                                <span class="title skeleton-box"></span>
                                            </div>
                                            <div class="project-timer-item-bottom">
                                                <span class="skeleton-box project-timer-progress"></span>
                                                <span class="duration skeleton-box"></span>
                                            </div>
                                        </li>
                                    @endfor
                                </ul>
                            </div>
                            `
                        );

                        $.ajax({
                                url: timingsUrl,
                                type: 'GET',
                                data: {
                                    date
                                }
                            })
                            .done(function(html) {
                                $box.html(html);
                                $projectModal.html(html);
                            })
                            .fail(function() {
                                $box.html(`<div class="text-muted">@lang('Failed to load data')</div>`);
                            })
                            .always(function() {
                                $box.removeClass('opacity-50');
                            });
                    }

                    $('#project_time_filter').on('date-filter:change', function(e, payload) {
                        const date = payload?.value || '';
                        loadProjectTiming(date);
                    });

                    const initialProjectTimeDate = $('#project_time_filter').find('.date-range-value').val() || "";
                    loadProjectTiming(initialProjectTimeDate);


                    // Apps Uses filter scoped listener
                    function loadUserApps(date = "") {
                        const $box = $('#app-uses-container');
                        const $appModal = $('#app-uses-modal-container');
                        $box.html(
                            `
                            <div class="project-timer">
                                <ul class="project-timer-list">
                                    @for ($i = 0; $i < 7; $i++)
                                        <li class="project-timer-item style--two">
                                            <div class="project-timer-item-top flex-between">
                                                <span class="title skeleton-box"></span>
                                                <span class="duration skeleton-box"></span>
                                            </div>
                                            <div class="project-timer-item-bottom skeleton-box"></div>
                                        </li>
                                    @endfor
                                </ul>
                            </div>
                            `
                        );

                        $.ajax({
                                url: appUsesUrl,
                                type: 'GET',
                                data: {
                                    date
                                }
                            })
                            .done(function(html) {
                                $box.html(html);
                                $appModal.html(html);
                            })
                            .fail(function() {
                                $box.html(`<div class="text-muted">@lang('Failed to load data')</div>`);
                            })
                            .always(function() {
                                $box.removeClass('opacity-50');
                            });
                    }

                    $('#app_uses_filter').on('date-filter:change', function(e, payload) {
                        const date = payload?.value || '';
                        loadUserApps(date);
                    });

                    const initialAppUseDate = $('#app_uses_filter').find('.date-range-value').val() || "";
                    loadUserApps(initialAppUseDate);


                    // Summary/overview
                    function loadSummary(date = "") {
                        const $box = $('#summary-stats');
                        $box.html(
                            `
                            @for ($i = 0; $i < 3; $i++)
                                <div class="summary-card">
                                    <div class="summary-card__content">
                                        <p class="summary-card__title skeleton-box"></p>
                                    </div>
                                    <div class="summary-card__bottom">
                                        <p class="summary-card__amount skeleton-box"></p>
                                        <span class="summary-card__icon skeleton-box"></span>
                                    </div>
                                </div>
                            @endfor
                            `
                        );

                        $.ajax({
                                url: summaryUrl,
                                type: 'GET',
                                data: {
                                    date
                                }
                            })
                            .done(function(html) {
                                $box.html(html);
                            })
                            .always(function() {
                                $box.removeClass('opacity-50');
                            });
                    }

                    $('#summary_filter').on('date-filter:change', function(e, payload) {
                        const date = payload?.value || '';
                        loadSummary(date);
                    });

                    const initialDate = $('#summary_filter').find('.date-range-value').val() || "";
                    loadSummary(initialDate);

                    @role('manager|organizer')
                        // Top Activity Staff
                        function loadTopActivity(date = "") {
                            const $box = $('#top-activity-staff-container');
                            $box.html(
                                `
                                <div class="staff-activity">
                                    <div class="staff-activity-content">
                                        @for ($i = 0; $i < 5; $i++)
                                            <div class="staff-activity-item">
                                                <div class="staff-activity-item-thumb skeleton-box"></div>
                                                <div class="staff-activity-item-content">
                                                    <p class="name skeleton-box"></p>
                                                    <p class="average skeleton-box"></p>
                                                </div>
                                            </div>
                                        @endfor
                                    </div>
                                    <div class="staff-activity-chart">
                                        <div class="line-chart-skeleton">
                                            <div class="line-chart-skeleton-line skeleton-box"></div>
                                            <div class="line-chart-skeleton-line skeleton-box"></div>
                                            <div class="line-chart-skeleton-line skeleton-box"></div>

                                            <div class="line-chart-skeleton-border">
                                                <div class="skeleton-box"></div>
                                                <div class="skeleton-box"></div>
                                                <div class="skeleton-box"></div>
                                                <div class="skeleton-box"></div>
                                                <div class="skeleton-box"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                `
                            );

                            $.ajax({
                                    url: topActivityStaffUrl,
                                    type: 'GET',
                                    data: {
                                        date
                                    }
                                })
                                .done(function(response) {
                                    $box.html(response.html);
                                    renderStaffActivityChart();
                                })
                                .always(function() {
                                    $box.removeClass('opacity-50');
                                });
                        }

                        $('#top_activity_staff_filter').on('date-filter:change', function(e, payload) {
                            const date = payload?.value || '';
                            loadTopActivity(date);
                        });

                        const initialTopStaffDate = $('#top_activity_staff_filter').find('.date-range-value')
                            .val() || "";
                        loadTopActivity(initialTopStaffDate);
                    @endrole

                    @role('manager|organizer')
                        // Top performers
                        function loadTopPerformer(date = "") {
                            const $box = $('#top-performer-container');
                            $box.html(
                                `
                                <div class="project-timer">
                                    <ul class="project-timer-list">
                                        @for ($i = 0; $i < 5; $i++)
                                            <li class="project-timer-item">
                                                <div class="project-timer-item-top flex-between">
                                                    <span class="title skeleton-box"></span>
                                                    <span class="duration skeleton-box"></span>
                                                </div>
                                                <div class="project-timer-item-bottom skeleton-box"></div>
                                            </li>
                                        @endfor
                                    </ul>
                                </div>
                                `
                            );
                            $.ajax({
                                    url: topPerformersUrl,
                                    type: 'GET',
                                    data: {
                                        date
                                    }
                                })
                                .done(function(html) {
                                    $box.html(html);
                                })
                                .always(function() {
                                    $box.removeClass('opacity-50');
                                });
                        }

                        $('#top_performer_filter').on('date-filter:change', function(e, payload) {
                            const date = payload?.value || '';
                            loadTopPerformer(date);
                        });

                        const initialTopPerformerDate = $('#top_performer_filter').find('.date-range-value')
                            .val() || "";
                        loadTopPerformer(initialTopPerformerDate);

                        // Low performers
                        function loadLowPerformer(date = "") {
                            const $box = $('#low-performer-container');
                            $box.html(
                                `
                                <div class="project-timer">
                                    <ul class="project-timer-list">
                                        @for ($i = 0; $i < 5; $i++)
                                            <li class="project-timer-item">
                                                <div class="project-timer-item-top flex-between">
                                                    <span class="title skeleton-box"></span>
                                                    <span class="duration skeleton-box"></span>
                                                </div>
                                                <div class="project-timer-item-bottom skeleton-box"></div>
                                            </li>
                                        @endfor
                                    </ul>
                                </div>
                                `
                            );

                            $.ajax({
                                    url: lowPerformersUrl,
                                    type: 'GET',
                                    data: {
                                        date
                                    }
                                })
                                .done(function(html) {
                                    $box.html(html);
                                })
                                .always(function() {
                                    $box.removeClass('opacity-50');
                                });
                        }

                        $('#low_performer_filter').on('date-filter:change', function(e, payload) {
                            const date = payload?.value || '';
                            loadLowPerformer(date);
                        });

                        const initialLowPerformerDate = $('#low_performer_filter').find('.date-range-value')
                            .val() || "";
                        loadLowPerformer(initialLowPerformerDate);
                    @endrole

                    @role('manager|organizer')
                        // Average Activity filter scoped listener
                        let firstTimeAvgActivityLoad = true;

                        function loadAvgActivity(date = "") {
                            const $box = $('#TrackerVolumeChart');
                            $.ajax({
                                    url: activitySeriesUrl,
                                    type: 'GET',
                                    data: {
                                        date
                                    }
                                })
                                .done(function(json) {
                                    const el = document.getElementById('TrackerVolumeChart');
                                    if (el) {
                                        const inst = echarts.getInstanceByDom(el);
                                        if (inst) inst.dispose();
                                    }

                                    renderDotLineChart({
                                        elementId: 'TrackerVolumeChart',
                                        data: json.values || [],
                                        colors: ['#FF6000'],
                                        xAxisData: json.labels || [],
                                        unitLabel: '%',
                                    });
                                })
                                .always(function() {
                                    $box.removeClass('opacity-50');
                                    firstTimeAvgActivityLoad = false;
                                });
                        }

                        $('#avg_activity_filter').on('date-filter:change', function(e, payload) {
                            const date = payload?.value || '';
                            loadAvgActivity(date);
                        });

                        const initialAvgActivityDate = $('#avg_activity_filter').find('.date-range-value').val() ||
                            "";
                        loadAvgActivity(initialAvgActivityDate);
                    @endrole

                    // time tracking filter
                    let firstTimeLoad = true;

                    function loadTimeTracking(date = "") {
                        const $box = $('#timeTrackingChart');

                        $.ajax({
                                url: timeTrackingUrl,
                                type: 'GET',
                                data: {
                                    date
                                }
                            })
                            .done(function(json) {
                                const el = document.getElementById('timeTrackingChart');
                                if (el) {
                                    const inst = echarts.getInstanceByDom(el);
                                    if (inst) inst.dispose();
                                }

                                renderBarChart({
                                    elementId: "timeTrackingChart",
                                    data: json.values || [],
                                    colors: ["#ff6a00"],
                                    xAxisData: json.labels || [],
                                    isTime: true,
                                    showLabels: false,
                                    showTooltip: true,
                                    showYaxis: false
                                });
                            })
                            .fail(function() {
                                $box.html('<div class="text-center text-danger">@lang('Failed to load chart')</div>');
                            })
                            .always(function() {
                                $box.removeClass('opacity-50');
                                firstTimeLoad = false;
                            });
                    }

                    $('#time_tracking_filter').on('date-filter:change', function(e, payload) {
                        const date = payload?.value || '';
                        loadTimeTracking(date);
                    });

                    const initialTimeTrackingDate = $('#time_tracking_filter').find('.date-range-value').val() ||
                        "";
                    loadTimeTracking(initialTimeTrackingDate);

                    function renderStaffActivityChart() {
                        const el = document.getElementById('ViewStaffActivity');
                        if (!el) return;
                        const labelsAttr = el.getAttribute('data-labels') || '[]';
                        const valuesAttr = el.getAttribute('data-values') || '[]';
                        let labels = [];
                        let values = [];
                        try {
                            labels = JSON.parse(labelsAttr) || [];
                        } catch (e) {
                            labels = [];
                        }
                        try {
                            values = JSON.parse(valuesAttr) || [];
                        } catch (e) {
                            values = [];
                        }

                        const inst = echarts.getInstanceByDom(el);
                        if (inst) inst.dispose();

                        renderBarChart({
                            elementId: 'ViewStaffActivity',
                            data: values,
                            colors: ['#ff6a00'],
                            xAxisData: labels,
                            unitLabel: '%',
                            sliceX: true,
                            showTooltip: true
                        });
                    }
                });

                initLazy();

                $(document).on("click", "[data-lightbox]", function() {
                    let img = $(this).find("img.lazy");
                    if (img.attr("data-src")) {
                        img.attr("src", img.attr("data-src"));
                        img.removeAttr("data-src");
                    }
                });

            })(jQuery);
        </script>
    @endpush
@endif

@extends('Template::layouts.master')

@section('content')
    <div class="screen-filter ">
        <div class="screen-filter-right justify-content-between justify-content-lg-start time__activity">
            <div class="datepicker-wrapper">
                <div class="datepicker-inner">
                    <span class="icon">
                        <x-icons.calendar />
                    </span>
                    <input id="dateRange" type="text" value="{{ $dateRange }}"
                        class="form--control md-style datepicker2-range-max-today" date-range="true" />
                </div>
            </div>
            @role('manager|organizer')
                <div class="select2-wrapper">
                    <select class="img-select2" name="user">
                        <option value="0" data-src="{{ asset('assets/images/avatar.png') }}">
                            @lang('All Members')
                        </option>
                        @foreach ($members as $member)
                            <option value="{{ $member->uid }}" data-src="{{ $member->image_url }}"
                                @selected($member->uid == request()->user)>
                                {{ toTitle($member->fullname) }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endrole
        </div>
        <div class="d-flex align-items-center justify-content-between justify-content-lg-start gap-3 time__activity">
            <div class="select2-wrapper">
                <select class="select2 sm-style" name="usage_sort" data-minimum-results-for-search="-1">
                    <option value="top">@lang('Most Used Apps')</option>
                    <option value="least">@lang('Least Used Apps')</option>
                </select>
            </div>
        </div>
    </div>

    <div class="row gy-4 mb-4">
        <div class="col-12">
            <div class="card custom--card h-100">
                <div class="card-header">
                    <h5 class="card-title">@lang('Time Spent Per App')</h5>
                </div>
                <div class="card-body">
                    <div id="appAnalyticsChart" class="chart-container xl-style"></div>
                    <div id="appAnalyticsEmpty" class="text-center text-muted mt-3 d-none">
                        <x-user.no-data :title="__('No app analytics data available')" />
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('style-lib')
    <link rel="stylesheet" href="{{ asset(activeTemplate(true) . 'css/slick.css') }}">
    <link rel="stylesheet" href="{{ asset(activeTemplate(true) . 'css/magnify-popup.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/admin/css/daterangepicker.css') }}">
@endpush

@push('script-lib')
    <script src="{{ asset('assets/admin/js/moment.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/daterangepicker.min.js') }}"></script>
    <script src="{{ asset(activeTemplate(true) . 'js/slick.min.js') }}"></script>
    <script src="{{ asset(activeTemplate(true) . 'js/magnify-popup.js') }}"></script>
    <script src="{{ asset(activeTemplate(true) . 'js/echarts.js') }}"></script>
    <script src="{{ asset(activeTemplate(true) . 'js/chart.js') }}"></script>
@endpush


@push('style')
    <style>
        @media screen and (max-width: 767px) {
            .screen-filter {
                -ms-flex-direction: unset;
                flex-direction: unset;
            }
        }

        .staff-activity-chart {
            width: 100%;
        }

        .line-chart-skeleton-line {
            width: 2%;
        }

        .line-chart-skeleton-line:nth-child(even) {
            height: 100%;
        }

        .line-chart-skeleton-line:nth-child(odd) {
            height: 75%;
        }

        .line-chart-skeleton-border {
            padding-block: 10px 0px;
        }
    </style>
@endpush

@push('script')
    <script>
        (function($) {
            "use strict";

            $(document).ready(function() {

                let date = '';
                let user = '';
                let usageSort = 'top';

                const url = `{{ route('user.report.app.analytics.load') }}`;
                const $chart = $('#appAnalyticsChart');
                const $emptyState = $('#appAnalyticsEmpty');

                $('#dateRange').on('change', function() {
                    date = $(this).val();
                    loadContent();
                }).change();

                $('select[name=user]').on('change', function() {
                    user = $(this).val();
                    loadContent();
                });

                $('select[name=usage_sort]').on('change', function() {
                    usageSort = $(this).val() || 'top';
                    loadContent();
                });

                function toggleEmptyState(hasData) {
                    if (hasData) {
                        $chart.removeClass('d-none');
                        $emptyState.addClass('d-none');
                    } else {
                        $chart.addClass('d-none');
                        $emptyState.removeClass('d-none');
                    }
                }

                function loadContent() {
                    const data = {
                        date,
                        user,
                        usage_sort: usageSort,
                    };

                    if ($chart.length > 0) {
                        $chart.html(`
                            <div class="staff-activity">
                                <div class="staff-activity-chart">
                                    <div class="line-chart-skeleton">
                                        @for ($i = 0; $i < 18; $i++)
                                            <div class="line-chart-skeleton-line skeleton-box"></div>
                                        @endfor

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
                        `);
                    }

                    $.get(url, data, function(response) {
                        if ($chart.length > 0) {
                            $chart.html('');

                            const labels = response.labels || [];
                            const values = response.values || [];

                            if (labels.length && values.length) {
                                toggleEmptyState(true);

                                renderBarChart({
                                    elementId: "appAnalyticsChart",
                                    data: values,
                                    xAxisData: labels,
                                    colors: ['#ff6a00'],
                                    isTime: true,
                                    rotateLabel: true,
                                    showLabels: false,
                                    showTooltip: true,
                                    showYaxis: false
                                });
                            } else {
                                toggleEmptyState(false);
                            }
                        }
                    });
                }

            });

        })(jQuery);
    </script>
@endpush

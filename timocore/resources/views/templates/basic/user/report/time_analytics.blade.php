@extends('Template::layouts.master')

@section('content')
    <div class="screen-filter">
        <div class="screen-filter-right">
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
    </div>

    <div class="row gy-4 mb-4">
        <div class="col-12">
            <div class="col-md-12">
                <div class="card custom--card h-100">
                    <div class="card-header">
                        <h5 class="card-title">@lang('Total Hours Worked Per Day')</h5>
                    </div>
                    <div class="card-body">
                        <div id="timeChart" class="chart-container xl-style"></div>
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

                let date, user = '';
                let url = `{{ route('user.report.time.analytics.load') }}`

                $('#dateRange').on('change', function() {
                    date = $(this).val();
                    user = $('select[name=user]').val();
                    loadContent();
                }).change();

                $('select[name=user]').on('change', function() {
                    user = $(this).val();
                    loadContent();
                });

                function loadContent() {
                    let data = {
                        date,
                        user
                    };


                    if ($("#timeChart").length > 0) {
                        $("#timeChart").html(`
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


                    $.get(url, data,
                        function(response) {
                            // return;
                            if ($("#timeChart").length > 0) {
                                $("#timeChart").html(``)
                                renderBarChart({
                                    elementId: "timeChart",
                                    data: response.timeList,
                                    colors: ['#ff6a00'],
                                    xAxisData: response.dateList,
                                    isTime: true,
                                    showLabels: false,
                                    showTooltip: true,
                                    showYaxis: false
                                });
                            }
                        }
                    );
                }
            });
        })(jQuery);
    </script>
@endpush

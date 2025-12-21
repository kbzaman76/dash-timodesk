@extends('Template::layouts.master')
@section('content')
    <div class="screen-filter">
        <div class="screen-filter-right justify-content-between justify-content-lg-start time__activity">
            <div class="datepicker-wrapper">
                <div class="datepicker-arrow">
                    <button class="datepicker-arrow-btn js-prev-day preMonthBtn" type="button">
                        <i class="fa-solid fa-arrow-left-long"></i>
                    </button>
                    <button class="datepicker-arrow-btn js-next-day nextMonthBtn" type="button">
                        <i class="fa-solid fa-arrow-right-long"></i>
                    </button>
                </div>
            </div>

            <div class="select2-wrapper">
                <select class="select2 sm-style" name="month" data-minimum-results-for-search="-1">
                    @forelse ($monthArray as $monthYear)
                        <option value="{{ $monthYear }}" @selected($monthYear == request()->month)>
                            {{ now()->parse($monthYear)->format('F Y') }}
                        </option>
                    @empty
                        <option>{{ now()->format('Y-m') }}</option>
                    @endforelse
                </select>
            </div>
        </div>
        <div
            class="table-filter-right d-flex align-items-center justify-content-between justify-content-lg-start gap-3 time__activity">
            <button class="btn btn-outline--base btn--md selectionBtn" type="button">
                <i class="las la-hand-pointer"></i> @lang('Highlighter')
            </button>
            <button class="btn btn--base btn--md downloadBtn" type="button">
                <i class="las la-download"></i> @lang('Download')
            </button>
        </div>
    </div>


    <div class="widget-card-main mb-4">
        <div class="row g-3 g-md-4">
            <div class="col-lg-3 col-sm-6">
                <div class="widget-card">
                    <div class="widget-card__body">
                        <div class="widget-card__wrapper">
                            <div class="widget-card__icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round"
                                    class="icon icon-tabler icons-tabler-outline icon-tabler-calendar-week">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path
                                        d="M4 7a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12z" />
                                    <path d="M16 3v4" />
                                    <path d="M8 3v4" />
                                    <path d="M4 11h16" />
                                    <path d="M7 14h.013" />
                                    <path d="M10.01 14h.005" />
                                    <path d="M13.01 14h.005" />
                                    <path d="M16.015 14h.005" />
                                    <path d="M13.015 17h.005" />
                                    <path d="M7.01 17h.005" />
                                    <path d="M10.01 17h.005" />
                                </svg>
                            </div>
                            <p class="widget-card__count daysInMonth"></p>
                        </div>
                        <p class="widget-card__title">@lang('Month Days')</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6">
                <div class="widget-card">
                    <div class="widget-card__body">
                        <div class="widget-card__wrapper">
                            <div class="widget-card__icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round"
                                    class="icon icon-tabler icons-tabler-outline icon-tabler-calendar-check">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path d="M11.5 21h-5.5a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v6" />
                                    <path d="M16 3v4" />
                                    <path d="M8 3v4" />
                                    <path d="M4 11h16" />
                                    <path d="M15 19l2 2l4 -4" />
                                </svg>
                            </div>
                            <p class="widget-card__count totalTime"></p>
                        </div>
                        <p class="widget-card__title">@lang('Total Time')</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6">
                <div class="widget-card">
                    <div class="widget-card__body">
                        <div class="widget-card__wrapper">
                            <div class="widget-card__icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round"
                                    class="icon icon-tabler icons-tabler-outline icon-tabler-calendar-stats">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path d="M11.795 21h-6.795a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v4" />
                                    <path d="M18 14v4h4" />
                                    <path d="M18 18m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0" />
                                    <path d="M15 3v4" />
                                    <path d="M7 3v4" />
                                    <path d="M3 11h16" />
                                </svg>
                            </div>
                            <p class="widget-card__count averageTime"></p>
                        </div>
                        <p class="widget-card__title">@lang('Average Time')</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6">
                <div class="widget-card">
                    <div class="widget-card__body">
                        <div class="widget-card__wrapper">
                            <div class="widget-card__icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round"
                                    class="icon icon-tabler icons-tabler-outline icon-tabler-device-imac-bolt">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path d="M13.5 17h-9.5a1 1 0 0 1 -1 -1v-12a1 1 0 0 1 1 -1h16a1 1 0 0 1 1 1v8.5" />
                                    <path d="M3 13h13" />
                                    <path d="M8 21h5.5" />
                                    <path d="M10 17l-.5 4" />
                                    <path d="M19 16l-2 3h4l-2 3" />
                                </svg>
                            </div>
                            <p class="widget-card__count activityPercent"></p>
                        </div>
                        <p class="widget-card__title">@lang('Average Activity')</p>
                    </div>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="table-wrapper mt-4">
        <div class="table-scroller">
            <table class="table overview-table monthlyOverviewContent">

            </table>
        </div>
    </div>

    <div class="modal custom--modal fade" id="selectionModal" tabindex="-1" aria-labelledby="selectionModal"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Highlighter')</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>

                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label class="form--label">@lang('Above')</label>
                        <div class="input-group">
                            <input type="number" class="form-control form--control md-style" name="above_hour"
                                min="0" max="23" placeholder="HH">
                            <span class="input-group-text">@lang('Hours')</span>
                            <input type="number" class="form-control form--control md-style" name="above_minute"
                                min="0" max="59" placeholder="MM">
                            <span class="input-group-text">@lang('Minutes')</span>
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label class="form--label">@lang('Below')</label>
                        <div class="input-group">
                            <input type="number" class="form-control form--control md-style" name="below_hour"
                                min="0" max="23" placeholder="HH">
                            <span class="input-group-text">@lang('Hours')</span>
                            <input type="number" class="form-control form--control md-style" name="below_minute"
                                min="0" max="59" placeholder="MM">
                            <span class="input-group-text">@lang('Minutes')</span>
                        </div>
                    </div>
                    <div>
                        <small><strong>Note:</strong> Set time limits to highlight above (green) and below (red).</small>
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn--base btn--md w-100 selectBtn">@lang('Check Time')</button>
                </div>

            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        (function($) {
            "use strict";

            let url = `{{ route('user.report.monthly.time.sheet.load') }}`;
            let months = JSON.parse(`@php echo json_encode(array_reverse($monthArray)) @endphp`);
            let month;
            let selectedAboveSeconds = 0;
            let selectedBelowSeconds = 0;

            $('.preMonthBtn').on('click', function() {
                month = $('select[name="month"]').val();
                let index = months.indexOf(month);
                if (index > 0) {
                    month = months[index - 1];
                    $('select[name="month"]').val(month).trigger('change');
                }
                updateMonthButtons();
            });

            $('.nextMonthBtn').on('click', function() {
                month = $('select[name="month"]').val();
                let index = months.indexOf(month);

                if (index < months.length - 1) {
                    month = months[index + 1];
                    $('select[name="month"]').val(month).trigger('change');
                }
                updateMonthButtons();
            });

            $('select[name="month"]').on('change', function() {
                month = $(this).val();
                updateMonthButtons();
                loadContent();
            }).change();

            function updateMonthButtons() {
                month = $('select[name="month"]').val();
                let index = months.indexOf(month);

                if (index <= 0) {
                    $('.preMonthBtn').prop('disabled', true);
                } else {
                    $('.preMonthBtn').prop('disabled', false);
                }

                if (index >= months.length - 1) {
                    $('.nextMonthBtn').prop('disabled', true);
                } else {
                    $('.nextMonthBtn').prop('disabled', false);
                }
            }

            function loadContent(pdf = false) {
                const options = {
                    month
                };
                if (pdf) options['pdf'] = true;
                $.get(url, options,
                    function(response) {
                        $('.monthlyOverviewContent').html(response.view);
                        $('.daysInMonth').html(response.days_in_month);
                        $('.totalTime').html(response.total_time);
                        $('.averageTime').html(response.average_time);
                        $('.activityPercent').html(response.activity_percent);
                    }
                );
            }

            let modal = $('#selectionModal');

            $('.selectionBtn').on('click', function() {
                modal.modal('show');
            });

            $('.selectBtn').on('click', function() {
                let aboveHour = parseInt($('[name=above_hour]').val() || 0);
                let aboveMinute = parseInt($('[name=above_minute]').val() || 0);
                let aboveTimeInSeconds = aboveHour * 3600 + aboveMinute * 60;

                let belowHour = parseInt($('[name=below_hour]').val() || 0);
                let belowMinute = parseInt($('[name=below_minute]').val() || 0);
                let belowTimeInSeconds = belowHour * 3600 + belowMinute * 60;

                if (aboveTimeInSeconds > 0 && belowTimeInSeconds > 0 && belowTimeInSeconds >=
                    aboveTimeInSeconds) {
                    notify('error', 'Above time must be greater than below time');
                    return;
                }

                $('td[data-second]').each(function() {
                    let td = $(this);
                    let tdTimeInSeconds = parseInt(td.data('second'));
                    if (!tdTimeInSeconds) return;

                    td.removeClass('danger-mark success-mark');

                    if (!isNaN(tdTimeInSeconds)) {
                        if (belowTimeInSeconds > 0 && tdTimeInSeconds < belowTimeInSeconds) {
                            td.addClass('danger-mark');
                        }

                        if (aboveTimeInSeconds > 0 && tdTimeInSeconds > aboveTimeInSeconds) {
                            td.addClass('success-mark');
                        }
                    }
                });

                selectedAboveSeconds = aboveTimeInSeconds;
                selectedBelowSeconds = belowTimeInSeconds;

                modal.modal('hide');
            });

            $('.downloadBtn').on('click', function() {
                const month = $('select[name="month"]').val();
                const params = [
                    `month=${encodeURIComponent(month)}`,
                    'pdf=1'
                ];
                if (selectedAboveSeconds > 0) {
                    params.push(`above_time=${selectedAboveSeconds}`);
                }
                if (selectedBelowSeconds > 0) {
                    params.push(`below_time=${selectedBelowSeconds}`);
                }
                window.location.href = `${url}?${params.join('&')}`;
            });

        })(jQuery);
    </script>
@endpush

@push('style')
    <style>
        .overview-table thead tr th {
            background-color: hsl(var(--base));
            color: hsl(var(--white));
            border-right: 1px solid hsl(var(--white));
            font-weight: 600;
            text-align: center;
            padding: 8px;
            font-size: 0.8rem;
        }

        .overview-table tbody tr td {
            word-break: break-all;
            overflow-wrap: normal;
            white-space: normal;
            border-right: 1px solid hsl(var(--border-color));
            padding: 8px;
            text-align: center;
            font-size: 0.8rem;
        }

        .overview-table tbody tr td:has(.overview-col-name) {
            background-color: hsl(var(--black) / .1);
        }

        .overview-table tbody tr td .overview-col-name {
            width: 100px;
            text-align: center;
            color: hsl(var(--heading-color));
            font-weight: 500;
            margin: 0 auto;
            display: block;
        }

        .overview-table tbody tr td:not(:has(.overview-col-name, .overview-col-avg, .overview-col-abs)) {
            writing-mode: vertical-rl;
            text-orientation: mixed;
            font-weight: 500;
            color: hsl(var(--heading-color));
            transform: rotate(180deg)
        }

        @media screen and (max-width: 1199px) {
            .overview-table tbody tr td:not(:has(.overview-col-name, .overview-col-avg, .overview-col-abs)) {
                text-orientation: unset;
                transform: none;
                writing-mode: horizontal-tb;
            }
            td {
                min-width: 70px;
            }

        }


        .overview-table tbody tr td.empty-data {
            writing-mode: horizontal-tb;
        }

        .overview-table tbody tr td:has(.bg--muted) {
            background-color: #ffecec;
            border-color: hsl(var(--white));
        }

        .overview-table tbody tr td:has(.overview-col-avg, .overview-col-abs) {
            white-space: nowrap;
            font-weight: 500;
            color: hsl(var(--heading-color));
        }

        .overview-table tbody tr td:has(.overview-col-abs) {
            text-align: center;

        }

        .overview-table tbody tr:hover td {
            background-color: hsl(var(--black) / .02) !important;
        }

        .overview-table tbody tr:hover td:has(.overview-col-name) {
            background-color: hsl(var(--black) / .15) !important;
        }

        .overview-table tbody tr:hover td:has(.bg--muted) {
            background-color: #ffd9d9 !important;
        }

        .datepicker-month {
            font-size: 16px;
            font-weight: 600;
            color: hsl(var(--heading-color));
        }

        .success-mark {
            background-color: hsl(var(--success) / .2) !important;
        }

        .overview-table tbody tr:has(.success-mark):hover .success-mark {
            background-color: hsl(var(--success) / .3) !important;
        }

        .danger-mark {
            background-color: hsl(var(--danger) / .3) !important;
        }

        .overview-table tbody tr:has(.danger-mark):hover .danger-mark {
            background-color: hsl(var(--danger) / .4) !important;
        }

        .time_sheet_table-no__data {
            writing-mode: unset !important;
            transform: none !important;
        }
    </style>
@endpush

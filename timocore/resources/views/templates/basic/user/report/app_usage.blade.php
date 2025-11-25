@extends('Template::layouts.master')

@section('content')
    <div class="screen-filter">
        <div class="screen-filter-right justify-content-between justify-content-lg-start time__activity">
            <div class="datepicker-wrapper">
                <div class="datepicker-inner">
                    <span class="icon">
                        <x-icons.calendar />
                    </span>
                    <input id="dateRange" type="text" value="{{ $dateRange }}" class="form--control md-style datepicker2-range-max-today" date-range="true" />
                </div>
            </div>
            @role('manager|organizer')
                <div class="select2-wrapper">
                    <select class="img-select2" name="user">
                        <option value="0" data-src="{{ asset('assets/images/avatar.png') }}">
                            @lang('All Members')
                        </option>
                        @foreach ($members as $member)
                            <option value="{{ $member->id }}" data-src="{{ $member->image_url }}" @selected($member->id == request()->user)>
                                {{ toTitle($member->fullname) }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endrole
        </div>
        <div class="d-flex align-items-center justify-content-between justify-content-lg-start gap-3 time__activity">
            <select class="select2 sm-style" name="group_by" data-minimum-results-for-search="-1">
                <option value="date">@lang('Group by Date')</option>
                <option value="member">@lang('Group by Member')</option>
                <option value="app">@lang('Group by App')</option>
            </select>
            <div class="dropdown table-filter-dropdown">
                <button class="btn btn--base btn--md dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    @lang('Export')
                </button>
                <ul class="dropdown-menu">
                    <li>
                        <a class="dropdown-item exportBtn" data-type="pdf">
                            <x-icons.pdf />
                            @lang('PDF')
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item exportBtn" data-type="csv">
                            <x-icons.csv />
                            @lang('CSV')
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="table-scroller allContent"></div>

    <div class="modal custom--modal fade" id="dataTypeModal" tabindex="-1" aria-labelledby="dataTypeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="dataTypeModalLabel">@lang('Export Options')</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="d-gird gap-2">
                        <input type="hidden" id="export_type">
                        <div class="form--group d-flex">
                            <label class="form--label" for="collapsed">
                                <input type="radio" value="collapsed" id="collapsed" name="data_type" required>
                                @lang('Export with collapsed data')
                            </label>
                        </div>
                        <div class="form--group d-flex">
                            <label class="form--label" for="expanded">
                                <input type="radio" value="expanded" id="expanded" name="data_type" required>
                                @lang('Export with expanded data')
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn--dark btn--sm" data-bs-dismiss="modal">@lang('Close')</button>
                    <button type="button" class="btn btn--base btn--sm" id="download" disabled>@lang('Download')</button>
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
        .activity-table-project {
            justify-content: flex-start;
        }
    </style>
@endpush

@push('script')
    <script>
        (function($) {
            "use strict";

            $(document).ready(function() {

                let date, user = '';
                let groupBy = 'date';

                let url = `{{ route('user.report.app.usage.load') }}`;
                const dateLevelSelector =
                    '.collapse[data-level="member_app_dates"], .collapse[data-level="app_member_dates"], .collapse[data-level="date_app_members"]';
                const dateRootSelector =
                    '.collapse[data-level="member_apps"], .collapse[data-level="app_members"], .collapse[data-level="date_apps"]';

                function setDateHeadingVisibility(isVisible = false) {
                    const $heading = $('.allContent .date-heading');
                    if (!$heading.length) {
                        return;
                    }
                    
                    const label = $heading.data('label') || '';
                    $heading.text(isVisible ? label : '');
                }

                function syncDateHeadingWithState() {
                    const hasOpenDateRows = $('.allContent').find(dateLevelSelector).filter('.show').length > 0;
                    setDateHeadingVisibility(hasOpenDateRows);
                }

                function hideCollapseElement(element) {
                    if (typeof bootstrap !== 'undefined' && bootstrap.Collapse) {
                        const instance = bootstrap.Collapse.getOrCreateInstance(element, {
                            toggle: false
                        });
                        instance.hide();
                    } else {
                        $(element).removeClass('show');
                    }
                }

                $('select[name=group_by]').on('change', function() {
                    groupBy = $(this).val();
                    loadContent();
                });

                $('select[name=user]').on('change', function() {
                    user = $(this).val();
                    loadContent();
                });

                $('#dateRange').on('change', function() {
                    date = $(this).val();
                    loadContent();
                }).change();


                function loadContent(is_export = false, data_type = "", export_type = "") {
                    let data = {
                        date,
                        user,
                        group_by: groupBy,
                        level: 'root',
                        is_export,
                        data_type,
                        export_type
                    };

                    if (is_export === true) {
                        let queryString = $.param(data);
                        let fullUrl = url + "?" + queryString;
                        window.location.href = fullUrl;
                        return;
                    }

                    $('.allContent').html(`
                        <table class="activity-table-main">
                            <tbody>
                                @for ($i = 1; $i <= 7; $i++)
                                    <tr>
                                        <td colspan="100%">
                                            <table class="table activity-table">
                                                <tbody>
                                                    <tr class="parent-row">
                                                        <td>
                                                            <span class="skeleton-box"></span>
                                                        </td>
                                                        <td>
                                                            <span class="skeleton-box"></span>
                                                        </td>
                                                        <td>
                                                            <span class="skeleton-box"></span>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </td>
                                    </tr>
                                @endfor
                            </tbody>
                        </table>
                    `);

                    setDateHeadingVisibility(false);

                    $.get(url, data,
                        function(response) {
                            $('.allContent').html(response.view);
                            setDateHeadingVisibility(false);
                        }
                    );
                }

                function fetchLazyContent($collapse) {
                    const level = $collapse.data('level');
                    if (!level) {
                        return;
                    }

                    const requestData = {
                        date,
                        user,
                        group_by: groupBy,
                        level,
                        date_key: $collapse.data('date'),
                        app_name: $collapse.data('app'),
                        member_id: $collapse.data('member'),
                    };

                    const $target = $collapse.find('.lazy-content');
                    $collapse.data('loading', true);
                    $target.html(`<span class="text-muted">{{ __('Loading data...') }}</span>`);

                    $.get(url, requestData)
                        .done(function(response) {
                            $collapse.data('loaded', true);
                            $target.html(response.view);
                        })
                        .fail(function() {
                            $target.html(`<span class="text-danger">{{ __('Unable to load data. Please try again.') }}</span>`);
                        })
                        .always(function() {
                            $collapse.data('loading', false);
                        });
                }

                $('.allContent').on('show.bs.collapse', '.collapse[data-lazy="true"]', function() {
                    const $collapse = $(this);

                    if ($collapse.data('loaded') || $collapse.data('loading')) {
                        return;
                    }

                    fetchLazyContent($collapse);
                });

                $('.allContent').on('shown.bs.collapse hidden.bs.collapse', dateLevelSelector, function() {
                    syncDateHeadingWithState();
                });

                $('.allContent').on('hidden.bs.collapse', dateRootSelector, function() {
                    const $root = $(this);
                    const $nestedDates = $root.find(dateLevelSelector).filter('.show');
                    if ($nestedDates.length) {
                        $nestedDates.each(function() {
                            hideCollapseElement(this);
                        });
                    }
                    syncDateHeadingWithState();
                });

                // export
                $('.exportBtn').on('click', function() {
                    let exportType = $(this).data('type');
                    if (exportType != "") {
                        $('#export_type').val(exportType);
                        $(this).val("").change();
                        $("#collapsed, #expanded").prop('checked', false);
                        $('#download').prop('disabled', true);
                        $('#dataTypeModal').modal('show');
                    }
                });

                $("input[name='data_type']").on('click', function() {
                    let dataType = $(this).val();
                    let exportType = $('#export_type').val();

                    if (dataType && exportType) {
                        $('#download').prop('disabled', false);
                    } else {
                        $('#download').prop('disabled', true);
                    }
                });

                $('#download').on('click', function() {
                    let dataType = $("input[name='data_type']:checked").val();
                    let exportType = $('#export_type').val();
                    let dateDuration = getDateDuration(date);

                    if (dataType == 'expanded' && exportType == 'pdf' && dateDuration > 31) {
                        notify('error', 'The expanded PDF can only be exported for up to 1 month.');
                        $('#dataTypeModal').modal('hide');
                        return;
                    }

                    if (!dataType) {
                        $('#collapsed').focus();
                        return;
                    }

                    if (dataType && exportType) {
                        loadContent(true, dataType, exportType);
                        $('#dataTypeModal').modal('hide');
                    }
                });
            });

        })(jQuery);
    </script>
@endpush

@push('style')
    <style>
        .time__activity {
            @media (max-width: 991.98px) {
                width: 100%;
            }
        }
        .parent-row {
            cursor: pointer;
        }

        .toggle-btn i {
            transition: transform 0.3s ease;
        }

        .parent-row[aria-expanded="true"] .toggle-btn i {
            transform: rotate(180deg);
        }

        .collapse:not(.show) {
            display: none;
        }

        .parent-row {
            cursor: pointer;
        }

        .user-row {
            cursor: pointer;
        }

        .toggle-btn i {
            transition: transform 0.3s ease;
        }

        .parent-row[aria-expanded="true"] .toggle-btn i,
        .user-row[aria-expanded="true"] .toggle-btn i {
            transform: rotate(180deg);
        }

        .collapse:not(.show) {
            display: none;
        }
    </style>
@endpush

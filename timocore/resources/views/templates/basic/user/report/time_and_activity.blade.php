@extends('Template::layouts.master')

@section('content')
    <div class="screen-filter">
        <div class="screen-filter-right justify-content-between justify-content-lg-start time__activity">
            <div class="datepicker-wrapper">
                <div class="datepicker-inner">
                    <span class="icon">
                        <x-icons.calendar />
                    </span>
                    <input id="dateRange" type="text" value="{{ $dateRange }}"
                        class="form--control md-style datepicker2-range-max-today" date-range+="true" />
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
            <select class="select2 sm-style" name="group_by" data-minimum-results-for-search="-1">
                <option value="date">@lang('Group by Date')</option>
                @role('manager|organizer')
                    <option value="member">@lang('Group by Member')</option>
                @endrole
                <option value="project">@lang('Group by Project')</option>
            </select>
            <div class="dropdown table-filter-dropdown">
                <button class="btn btn--base btn--md dropdown-toggle" type="button" data-bs-toggle="dropdown"
                    aria-expanded="false">
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


    <div class="row g-3 g-lg-4 mb-4 time-activity-widgets">
        <div class="{{ auth()->user()->isStaff() ? 'col-lg-4' : 'col-lg-3' }} col-sm-6 widget-parent">
            <div class="widget-card h-100 d-none">
                <div class="widget-card__body">
                    <div class="widget-card__wrapper">
                        <span class="widget-card__icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24"
                                fill="none" class="injected-svg"
                                data-src="https://cdn.hugeicons.com/icons/loading-01-solid-standard.svg?v=1.0.1"
                                xmlns:xlink="http://www.w3.org/1999/xlink" role="img" color="currentColor">
                                <path fill-rule="evenodd" clip-rule="evenodd"
                                    d="M18 1.25C19.5188 1.25 20.75 2.48122 20.75 4V4.23047C20.75 5.5687 20.1855 6.84489 19.1953 7.74512L15.7441 10.8818C15.4292 11.1681 15.25 11.5744 15.25 12C15.25 12.4256 15.4292 12.8319 15.7441 13.1182L19.1953 16.2549C20.1855 17.1551 20.75 18.4313 20.75 19.7695V20C20.75 21.5188 19.5188 22.75 18 22.75H6C4.48122 22.75 3.25 21.5188 3.25 20V19.7695C3.25 18.4313 3.81451 17.1551 4.80469 16.2549L8.25586 13.1182C8.57077 12.8319 8.75 12.4256 8.75 12C8.75 11.5744 8.57077 11.1681 8.25586 10.8818L4.80469 7.74512C3.81451 6.84489 3.25 5.5687 3.25 4.23047V4C3.25 2.48122 4.48122 1.25 6 1.25H18ZM11.8154 17.2559C11.3751 17.2853 10.9939 17.4406 10.6162 17.6562C10.2526 17.864 9.83914 18.1603 9.34863 18.5107C9.0423 18.7296 8.69345 18.9598 8.48633 19.3145C8.45654 19.3655 8.42873 19.4188 8.4043 19.4727C8.27683 19.7541 8.25355 20.0594 8.25 20.3516V20.7959H15.75V20.3516C15.7464 20.0594 15.7232 19.7541 15.5957 19.4727C15.5713 19.4188 15.5435 19.3655 15.5137 19.3145C15.3065 18.9598 14.9577 18.7296 14.6514 18.5107L14.6221 18.4902C14.1443 18.1489 13.7402 17.8598 13.3838 17.6562C13.0061 17.4406 12.6249 17.2853 12.1846 17.2559C12.0617 17.2477 11.9383 17.2477 11.8154 17.2559Z"
                                    fill="currentColor"></path>
                            </svg>
                        </span>
                        <p class="widget-card__count widget-total-time">--</p>
                    </div>
                    <p class="widget-card__title">@lang('Total Worked Time')</p>
                </div>
            </div>
        </div>
        <div class="{{ auth()->user()->isStaff() ? 'col-lg-4' : 'col-lg-3' }} col-sm-6 widget-parent">
            <div class="widget-card h-100 d-none">
                <div class="widget-card__body">
                    <div class="widget-card__wrapper">
                        <span class="widget-card__icon">
                            <x-icons.percent />
                        </span>
                        <p class="widget-card__count widget-avg-activity">--</p>
                    </div>
                    <p class="widget-card__title">@lang('Average Activity')</p>
                </div>
            </div>
        </div>
        @role('manager|organizer')
            <div class="col-lg-3 col-sm-6 widget-parent">
                <div class="widget-card h-100 d-none">
                    <div class="widget-card__body">
                        <div class="widget-card__wrapper">
                            <span class="widget-card__icon">
                                <x-icons.people />
                            </span>
                            <p class="widget-card__count widget-active-members">--</p>
                        </div>
                        <p class="widget-card__title">@lang('Active Members')</p>
                    </div>
                </div>
            </div>
        @endrole
        <div class="{{ auth()->user()->isStaff() ? 'col-lg-4' : 'col-lg-3' }} col-sm-6 widget-parent">
            <div class="widget-card h-100 d-none">
                <div class="widget-card__body">
                    <div class="widget-card__wrapper">
                        <span class="widget-card__icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24"
                                fill="none" class="injected-svg"
                                data-src="https://cdn.hugeicons.com/icons/time-04-solid-standard.svg?v=1.0.1"
                                xmlns:xlink="http://www.w3.org/1999/xlink" role="img" color="currentColor">
                                <path fill-rule="evenodd" clip-rule="evenodd"
                                    d="M12 1.25C6.06294 1.25 1.25 6.06294 1.25 12C1.25 17.9371 6.06294 22.75 12 22.75C17.9371 22.75 22.75 17.9371 22.75 12C22.75 6.06294 17.9371 1.25 12 1.25ZM16.7071 7.29289C16.3166 6.90237 15.6834 6.90237 15.2929 7.29289L12 10.5858L9.70711 8.29289C9.31658 7.90237 8.68342 7.90237 8.29289 8.29289C7.90237 8.68342 7.90237 9.31658 8.29289 9.70711L10.5858 12L10.2929 12.2929C9.90237 12.6834 9.90237 13.3166 10.2929 13.7071C10.6834 14.0976 11.3166 14.0976 11.7071 13.7071L12 13.4142L12.2929 13.7071C12.6834 14.0976 13.3166 14.0976 13.7071 13.7071C14.0976 13.3166 14.0976 12.6834 13.7071 12.2929L13.4142 12L16.7071 8.70711C17.0976 8.31658 17.0976 7.68342 16.7071 7.29289Z"
                                    fill="currentColor"></path>
                            </svg>
                        </span>
                        <p class="widget-card__count widget-avg-hours">--</p>
                    </div>
                    @role('manager|organizer')
                        <p class="widget-card__title">@lang('Avg Hours / Member')</p>
                    @else
                        <p class="widget-card__title">@lang('Average Hours')</p>
                    @endrole
                </div>
            </div>
        </div>
    </div>

    <div class="table-scroller allContent"></div>

    <div class="modal custom--modal fade" id="dataTypeModal" tabindex="-1" aria-labelledby="dataTypeModalLabel"
        aria-hidden="true">
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
                    <button type="button" class="btn btn--dark btn--sm"
                        data-bs-dismiss="modal">@lang('Close')</button>
                    <button type="button" class="btn btn--base btn--sm" id="download"
                        disabled>@lang('Download')</button>
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
    <script src="{{ asset(activeTemplate(true) . 'js/chart.js') }}?v=1.1.2"></script>
@endpush

@push('script')
    <script>
        (function($) {
            "use strict";

            $(document).ready(function() {

                let date, user = '';
                let groupBy = 'date';

                let url = `{{ route('user.report.time.activity.load') }}`;
                const projectLevelSelector =
                    '.collapse[data-level="member_date_projects"], .collapse[data-level="date_user_projects"], .collapse[data-level="project_date_members"]';
                const rootLevelSelector =
                    '.collapse[data-level="member_dates"], .collapse[data-level="date_users"], .collapse[data-level="project_dates"]';

                function setProjectHeadingVisibility(isVisible = false) {
                    const $heading = $('.allContent .project-heading');
                    if (!$heading.length) {
                        return;
                    }
                    const label = $heading.data('label') || '';
                    $heading.text(isVisible ? label : '');
                }

                function syncProjectHeadingWithState() {
                    const hasOpenProjects = $('.allContent').find(projectLevelSelector).filter('.show').length >
                        0;
                    setProjectHeadingVisibility(hasOpenProjects);
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
                    user = $('select[name=user]').val();
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

                    if (is_export == true) {
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

                    $('.widget-parent .widget-card').addClass('d-none');
                    $('.widget-parent').append(`
                        <div class="widget-card h-100 skeleton-card">
                            <div class="widget-card__body">
                                <div class="widget-card__wrapper">
                                    <span class="widget-card__icon skeleton-box">
                                    </span>
                                    <p class="widget-card__count widget-total-time skeleton-box"></p>
                                </div>
                                <p class="widget-card__title skeleton-box"></p>
                            </div>
                        </div>
                    `);

                    setProjectHeadingVisibility(false);


                    $.get(url, data,
                        function(response) {
                            $('.allContent').html(response.view);
                            setProjectHeadingVisibility(false);
                            if (typeof response.total_work_time !== 'undefined') {
                                $('.totalTime').html(response.total_work_time);
                                $('.activityPercent').html(response.activity_percent);
                                $('.widget-total-time').html(response.total_work_time || '--');
                                $('.widget-avg-activity').html(response.activity_percent || '--');
                                $('.widget-active-members').html(typeof response.active_members !==
                                    'undefined' ? response.active_members : '--');
                                $('.widget-tracked-days').html(typeof response.tracked_days !==
                                    'undefined' ?
                                    response.tracked_days : '--');
                                $('.widget-avg-hours').html(response.avg_hours_per_member || '--');
                                $('.widget-parent .skeleton-card').remove();
                                $('.widget-parent .widget-card').removeClass('d-none');
                            }

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
                        project_id: $collapse.data('project'),
                        member_id: $collapse.data('member'),
                    };

                    console.log(requestData);
                    const $target = $collapse.find('.lazy-content');
                    $collapse.data('loading', true);
                    $target.html(`<span class="text-muted">{{ __('Loading data...') }}</span>`);

                    if (level === 'member_date_projects' || level === 'date_user_projects' || level ===
                        'project_date_members') {
                        setProjectHeadingVisibility(true);
                    }

                    $.get(url, requestData)
                        .done(function(response) {
                            $collapse.data('loaded', true);
                            $target.html(response.view);
                            setTimeout(() => {
                                $collapse.attr('loaded', true);
                            }, 500);
                        })
                        .fail(function() {
                            $target.html(
                                `<span class="text-danger">{{ __('Unable to load data. Please try again.') }}</span>`
                            );
                        })
                        .always(function() {
                            $collapse.data('loading', false);
                        });
                }

                $('.allContent').on('show.bs.collapse', '.collapse[data-lazy="true"]', function() {
                    const $collapse = $(this);

                    if ($collapse.data('loaded') || $collapse.data('loading')) {
                        if ($collapse.data('level') == 'date_user_projects' || $collapse.data(
                            'level') == 'member_date_projects' || $collapse.data('level') ==
                            'project_date_members') {
                            setProjectHeadingVisibility(true);
                        }
                        return;
                    }
                    fetchLazyContent($collapse);
                });

                $('.allContent').on('hidden.bs.collapse', rootLevelSelector, function() {
                    const $root = $(this);
                    const $parentRow = $(this).prev('.parent-row');

                    syncProjectHeadingWithState();
                    if (!$parentRow.length || !$parentRow.hasClass('collapsed')) {
                        return;
                    }

                    const $nestedProjects = $root.find(projectLevelSelector).filter('.show');
                    if ($nestedProjects.length) {
                        $nestedProjects.each(function() {
                            hideCollapseElement(this);
                        });
                    }
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

                    if (dataType == "" || dataType == undefined) {
                        $('#collapsed').focus();
                        return;
                    } else {

                        if (dataType && exportType) {
                            loadContent(true, dataType, exportType);
                            $('#dataTypeModal').modal('hide');
                        }
                    }

                });

                function exportToCSV(data, filename = 'export.csv') {
                    const csvRows = [];

                    // Get headers
                    const headers = Object.keys(data[0]);
                    csvRows.push(headers.join(','));

                    // Loop through data and escape commas/newlines
                    for (const row of data) {
                        const values = headers.map(header => {
                            let val = row[header] ?? '';
                            val = val.toString().replace(/"/g, '""'); // Escape quotes
                            if (val.search(/("|,|\n)/g) >= 0) val =
                                `"${val}"`; // Wrap in quotes if needed
                            return val;
                        });
                        csvRows.push(values.join(','));
                    }

                    // Create a Blob and download
                    const csvString = csvRows.join('\n');
                    const blob = new Blob([csvString], {
                        type: 'text/csv'
                    });
                    const url = window.URL.createObjectURL(blob);

                    const a = document.createElement('a');
                    a.setAttribute('hidden', '');
                    a.setAttribute('href', url);
                    a.setAttribute('download', filename);
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                }
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
        .date-row[aria-expanded="true"] .toggle-btn i,
        .user-row[aria-expanded="true"] .toggle-btn i {
            transform: rotate(180deg);
        }

        .collapse:not(.show) {
            display: none;
        }

        /* skelton css */
        .widget-card__icon.skeleton-box {
            height: 36px;
            width: 36px;
            border-radius: 4px;
            background-color: hsl(var(--base) / .1);
        }

        .widget-total-time.skeleton-box {
            height: 32px;
            border-radius: 4px;
            width: 100px;
        }

        .widget-card__title.skeleton-box {
            width: 50%;
            height: 20px;
            border-radius: 4px;
        }
    </style>
@endpush

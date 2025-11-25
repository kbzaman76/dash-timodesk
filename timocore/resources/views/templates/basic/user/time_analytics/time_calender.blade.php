@extends('Template::layouts.master')
@section('content')
    <div class="table-wrapper">
        <div id="calender-header" class="table-filter">
            @include('Template::user.time_analytics.calender_header')
        </div>
        <div class="table-scroller">
            <div class="calendar">
                <div id="calendar-grid">
                    @include('Template::user.time_analytics.calender')
                </div>
                <div id="calendar-loader" class="calendar-loader d-none">
                    <i class="las la-spinner la-spin"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Projects Modal -->
    <div class="modal fade custom--modal" id="projectsModal" tabindex="-1" aria-labelledby="projectsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="projectsModalLabel">@lang('Projects')</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <div class="modal-body" id="projectsModalBody">
                    <div class="py-5 text-center">
                        <i class="las la-spinner la-spin" style="font-size:28px"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('style')
    <style>
        .calendar-wrapper {
            --col-bg: 0 100% 93%;
            --calendar-bg: 220 33% 96%;
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            background: hsl(var(--calendar-bg));
            padding: 6px;
        }

        .table-wrapper:has(.calendar-wrapper) {
            box-shadow: unset;
        }

        .calendar-head {
            padding: 8px 12px;
            background: hsl(var(--calendar-bg));
            color: hsl(var(--heading-color));
            font-weight: 600;
            text-align: center;
            font-size: 1rem;
        }

        @media(max-width: 767px) {
            .calendar-head {
                font-size: 0.875rem;
            }
        }

        .calendar-col {
            min-height: 110px;
            border-right: 1px solid hsl(var(--border-color));
            background: hsl(var(--white));
            position: relative;
            padding: 12px;
            margin: 4px;
            border-radius: 8px;
            border-top: 6px solid hsl(var(--black) / 0.1);
        }

        .calendar-col .btn--white {
            background-color: hsl(var(--white) / .6) !important;
        }

        @media (max-width: 767px) {
            .calendar-col .btn {
                font-size: 0.75rem;
            }
        }

        .selected-month {
            background: #e5e5e5;
            border-top: 6px solid hsl(var(--black) / .1);
        }

        .selected-day {
            background: #f5ded0;
            border-top: 6px solid hsl(var(--base));
        }

        .selected-day .calendar-time-count {
            color: hsl(var(--heading-color));
        }

        .calendar-count-wrapper {
            text-align: right;
        }

        .calendar-count {
            font-size: 0.9125rem;
            color: hsl(var(--heading-color));
        }

        .calendar-time-count {
            font-weight: 700;
            text-align: center;
            padding-block: 32px;
            color: hsl(var(--body-color) / .85);
        }

        @media(max-width: 1399px) {
            .calendar-time-count {
                padding-block: 24px;
            }
        }

        @media(max-width: 767px) {
            .calendar-time-count {
                padding-block: 16px;
            }
        }

        .calendar-loader {
            position: absolute;
            inset: 0;
            background: hsl(var(--white) / .6);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 5;
        }

        .calendar-loader .la-spinner {
            font-size: 36px;
            color: hsl(var(--base));
        }

        .btn--secondary.active {
            background-color: hsl(var(--black) / .1) !important;
        }
    </style>
@endpush

@push('script')
    <script>
        (function($) {
            'use strict';
            $(function() {
                var $grid = $('#calendar-grid');
                var $calenderHeader = $('#calender-header');
                var $memberSelect = $('select[name="member"]');

                function loadCalendar(y, m) {
                    var params = {};
                    if (y) params.y = y;
                    if (m) params.m = m;
                    $memberSelect = $('select[name="member"]');
                    var memberVal = $memberSelect.val();

                    if (memberVal) params.member = memberVal;

                    $.ajax({
                        url: '{{ route('user.time.load.calender') }}',
                        method: 'GET',
                        data: params,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        beforeSend: function() {
                            $('#calendar-loader').removeClass('d-none');
                            $('#btn-prev, #btn-next').prop('disabled', true);
                            $memberSelect.prop('disabled', true);
                        },
                        success: function(data) {
                            if (!data) return;
                            if (data.html) $grid.html(data.html);
                            if (data.calender_header) $calenderHeader.html(data.calender_header);
                            $(".img-select2").select2();

                            if (memberVal) {
                                $('select[name="member"]').val(memberVal).trigger('change.select2');
                            }

                            $(".img-select2").select2({
                                templateResult: formatState,
                                templateSelection: formatState,
                            });
                        },
                        complete: function() {
                            let nextData = $('#btn-next').data();
                            $('#calendar-loader').addClass('d-none');
                            $('#btn-prev').prop('disabled', false);
                            if(nextData.current > nextData.y+''+nextData.m) {
                                $('#btn-next').prop('disabled', false);
                            }
                            $memberSelect.prop('disabled', false);
                        },
                        error: function(xhr) {
                            if (window.console) console.error(xhr);
                        }
                    });
                }

                $(document).on('click', '#btn-prev', function() {
                    loadCalendar($(this).data('y'), $(this).data('m'));
                });
                $(document).on('click', '#btn-next', function() {
                    loadCalendar($(this).data('y'), $(this).data('m'));
                });

                $(document).on('change', 'select[name="member"]', function() {
                    var y = $('#month-label').attr('data-y');
                    var m = $('#month-label').attr('data-m');

                    loadCalendar(y, m);
                });

                // Show per-day projects in modal (no extra request)
                $(document).on('click', '.js-projects', function() {
                    var date = $(this).data('date');
                    var items = $(this).data('projects') || [];
                    var total = $(this).data('total') || [];
                    var rowsHtml = '';
                    if (items.length) {
                        items.forEach(function(it) {
                            rowsHtml += '<tr>' +
                                '<td>' + (it.project || '') + '</td>' +
                                '<td class="text-end">' + (it.display || '0:00') + '</td>' +
                                '</tr>';
                        });
                    } else {
                        rowsHtml =
                            `<tr>
                                <td colspan="100%" class="text-center">
                                    <x-user.no-data />
                                </td>
                            </tr>`;
                    }

                    function fmt(sec) {
                        sec = parseFloat(sec || 0);

                        var decimalHours = Math.round((sec / 3600) * 100) / 100;

                        var hours = Math.floor(decimalHours);
                        var minutes = Math.floor((decimalHours - hours) * 60);

                        return (hours < 10 ? '0' + hours : hours) + ':' + (minutes < 10 ? '0' + minutes : minutes);
                    }

                    var html = '<div class="table-responsive">' +
                        '<table class="table table-striped">' +
                        '<thead><tr><th>@lang('Project')</th><th class="text-end">@lang('Time')</th></tr></thead>' +
                        '<tbody>' + rowsHtml + '</tbody>' +
                        '<tfoot class="table-footer"><tr><th class="text-start">@lang('Total')</th><th class="text-end">' +
                        total + '</th></tr></tfoot>' +
                        '</table></div>';

                    $('#projectsModalLabel').text(`@lang('Projects') - ${date}`);
                    $('#projectsModalBody').html(html);
                    var modal = new bootstrap.Modal(document.getElementById('projectsModal'));
                    modal.show();
                });
            });
        })(jQuery);
    </script>
@endpush

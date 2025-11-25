@extends('Template::layouts.master')
@section('content')
    <div class="table-wrapper">
        <div id="calender-header" class="table-filter">
            @include('Template::user.time_analytics.weekly_calender_header')
        </div>

        <div class="table-scroller">
            <div id="weekly-table">
                @include('Template::user.time_analytics.weekly_table')
            </div>
            <div id="weekly-loader" class="calendar-loader d-none">
                <i class="las la-spinner la-spin"></i>
            </div>
        </div>
    </div>
@endsection

@push('style')
    <style>
        .calendar-loader {
            position: absolute;
            inset: 0;
            background: rgba(255, 255, 255, 0.6);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 5;
        }

        .calendar-loader .la-spinner {
            font-size: 36px;
            color: hsl(var(--base));
        }
    </style>
@endpush

@push('script')
    <script>
        (function($) {
            'use strict';
            $(function() {
                var $tableWrap = $('#weekly-table');
                var $headerWrap = $('#calender-header');
                var $memberSelect = $('select[name="member"]');

                function loadWeekly(date) {
                    var params = {};
                    if (date) params.date = date;
                    $memberSelect = $('select[name="member"]');
                    var memberVal = $memberSelect.val();

                    if (memberVal) params.member = memberVal;

                    $.ajax({
                        url: '{{ route('user.time.weekly.load') }}',
                        method: 'GET',
                        data: params,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        beforeSend: function() {
                            $('#weekly-loader').removeClass('d-none');
                            $('#btn-prev, #btn-next').prop('disabled', true);
                            $memberSelect.prop('disabled', true);
                        },
                        success: function(data) {
                            if (!data) return;
                            if (data.html) $tableWrap.html(data.html);
                            if (data.calender_header) $headerWrap.html(data.calender_header);
                            $(document).find('.select2').each(function() {
                                const $select = $(this);

                                if (!$select.parent().hasClass('select2-wrapper')) {
                                    $select.wrap('<div class="select2-wrapper"></div>');
                                }

                                $select.select2({
                                    dropdownParent: $select.closest(
                                        '.select2-wrapper')
                                });
                            });

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
                            $('#weekly-loader').addClass('d-none');
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
                    var date = $(this).data('date');
                    loadWeekly(date);
                });
                $(document).on('click', '#btn-next', function() {
                    var date = $(this).data('date');
                    loadWeekly(date);
                });
                $(document).on('change', 'select[name="member"]', function() {
                    loadWeekly($('#week-label').data('date'));
                });
            });
        })(jQuery);
    </script>
@endpush

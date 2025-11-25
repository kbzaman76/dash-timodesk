@extends('Template::layouts.master')

@section('content')
    <div class="screen-filter project__timing-header">
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
                            <option value="{{ $member->id }}" data-src="{{ $member->image_url }}"
                                @selected($member->id == request()->user)>
                                {{ toTitle($member->fullname) }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endrole
        </div>
        <div class="select2-wrapper">
            <select class="select2 sm-style" name="group_by" data-minimum-results-for-search="-1">
                <option value="date">@lang('Group by Date')</option>
                <option value="member">@lang('Group by Member')</option>
            </select>
        </div>
    </div>

    <div class="table-scroller allContent"></div>
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

@push('script')
    <script>
        (function($) {
            "use strict";

            $(document).ready(function() {

                let date, user = '';
                let groupBy = 'date';

                let url = `{{ route('user.report.project.timing.load') }}`

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


                function loadContent() {
                    let data = {
                        date,
                        user,
                        group_by: groupBy
                    };

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
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </td>
                                    </tr>
                                @endfor
                            </tbody>
                        </table>
                    `);

                    $.get(url, data,
                        function(response) {
                            $('.allContent').html(response.view);
                        }
                    );
                }
            });

        })(jQuery);
    </script>
@endpush

@push('style')
    <style>
        
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

        .activity-table-project {
            justify-content: flex-start;
        }
    </style>
@endpush

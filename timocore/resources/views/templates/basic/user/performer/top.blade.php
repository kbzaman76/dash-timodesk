@extends('Template::layouts.master')
@section('content')
    <div id="calender-header" class="table-filter">
        @include('Template::user.performer.header')
    </div>

    <div id="weekly-table"></div>
@endsection

@push('style-lib')
    <link rel="stylesheet" href="{{ asset(activeTemplate(true) . 'css/slick.css') }}">
    <link rel="stylesheet" href="{{ asset(activeTemplate(true) . 'css/magnify-popup.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/admin/css/daterangepicker.css') }}">
@endpush

@push('script')
    <script>
        (function($) {
            'use strict';
            $(function() {
                var $tableWrap = $('#weekly-table');
                var $filter = $('#performer_page_filter');
                var $sortSelect = $('select.perfomer-filter');
                var url = '{{ route('user.performer.top.load') }}';

                var currentDateRange = '';
                var currentSortBy = $sortSelect.val() || '';
                let page = 1;

                function loadTop(dateRange, sortBy) {
                    $.ajax({
                        url,
                        method: 'GET',
                        data: {
                            date: currentDateRange || '',
                            sort_by: currentSortBy || '',
                            page
                        },
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        beforeSend: function() {
                            $tableWrap.html(`
                                <table class="table performer-table table-striped">
                                    <tbody>
                                        @foreach (range(1, 15) as $value)
                                        <tr>
                                            <td width="400px"><span class="skeleton-box"></span></td>
                                            <td><span class="skeleton-box"></span></td>
                                            <td><span class="skeleton-box"></span></td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            `);
                        },
                        success: function(data) {
                            if (data && data.html) {
                                $tableWrap.html(data.html);
                                // Scroll to top smoothly
                                $('html, body').animate({ scrollTop: 0 }, 'fast');
                            }
                        },
                        error: function(xhr) {
                            if (window.console) console.error(xhr);
                        }
                    });
                }

                $('#performer_page_filter').on('change', function() {
                    currentDateRange = $(this).val();
                    page = 1;
                    loadTop();
                }).change();

                $sortSelect.on('change', function() {
                    currentSortBy = $(this).val() || '';
                    page = 1;
                    loadTop();
                });

                $(document).on('click', '.page-link', function (event) {
                    event.preventDefault();
                    page = $(this).attr('href').split('page=')[1];
                    loadTop();
                });


                $('.exportBtn').on('click', function() {
                    var type = $(this).data('type');
                    var exportUrl = url + '?' + type + '=1&date=' + encodeURIComponent(
                        currentDateRange) + '&sort_by=' + encodeURIComponent(currentSortBy);
                    window.location.href = exportUrl;
                });
            });
        })(jQuery);
    </script>
@endpush

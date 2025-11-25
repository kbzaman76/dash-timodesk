@extends('Template::layouts.master')

@section('content')
    <div class="screen-filter screenshots-filter">
        <div class="datepicker-wrapper">
            <div class="datepicker-arrow">
                <button class="datepicker-arrow-btn js-prev-day" type="button">
                    <i class="fa-solid fa-arrow-left-long"></i>
                </button>
                <button class="datepicker-arrow-btn js-next-day" type="button">
                    <i class="fa-solid fa-arrow-right-long"></i>
                </button>
            </div>
            <div class="datepicker-inner">
                <span class="icon">
                    <x-icons.calendar />
                </span>
                <input id="dateRange" type="text" value="{{ now()->format('m/d/Y') }}"
                    class="form--control md-style datepicker2-single-max-today" autocomplete="off" />
            </div>
        </div>

        <div class="screen-filter-center">
            <div class="screen-filter-tab">
                <button class="screen-filter-tab-btn js-mode active" data-mode="10min">
                    @lang('Every 10 Min')
                </button>
                <button class="screen-filter-tab-btn js-mode" data-mode="all">
                    @lang('All Screenshots')
                </button>
            </div>
        </div>

        @role('manager|organizer')
            <div class="screen-filter-right">
                <div class="select2-wrapper">
                    <select class="img-select2 js-member">
                        @foreach ($members as $memberSingle)
                            <option data-src="{{ $memberSingle->image_url }}" value="{{ $memberSingle->id }}"
                                @selected($memberId && $memberSingle->id == $memberId)>
                                {{ toTitle($memberSingle->fullname) }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        @endrole
    </div>

    <div id="screenshot-grid"></div>

    <div id="screenshotGallery"></div>
@endsection

@push('style-lib')
    <link rel="stylesheet" href="{{ asset(activeTemplate(true) . 'css/slick.css') }}">
    <link rel="stylesheet" href="{{ asset(activeTemplate(true) . 'css/magnify-popup.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/admin/css/daterangepicker.css') }}">
    <link rel="stylesheet" href="{{ asset(activeTemplate(true) . 'css/lightbox.min.css') }}">
@endpush

@push('script-lib')
    <script src="{{ asset('assets/admin/js/moment.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/daterangepicker.min.js') }}"></script>
    <script src="{{ asset(activeTemplate(true) . 'js/slick.min.js') }}"></script>
    <script src="{{ asset(activeTemplate(true) . 'js/magnify-popup.js') }}"></script>
    <script src="{{ asset(activeTemplate(true) . 'js/lightbox.min.js') }}"></script>
@endpush

@push('script')
    <script>
        (function($) {
            "use strict";

            $(document).ready(function() {
                const url = `{{ route('user.activity.screenshot.load') }}`;
                const $date = $('#dateRange');

                function skeletonHTML() {
                    let html = '<div class="screenshot-wrapper">';

                    for (let g = 0; g < 3; g++) {
                        html += `
                            <div class="mb-3 mt-2 screenshot__title">
                                <h5 class="fw-semibold"><span class="skeleton-box" style="height:25px; width:300px"></span></h5>
                            </div>
                            <div class="screenshot-wrapper-block">
                                <div class="row g-3">
                        `;

                        for (let i = 0; i < 6; i++) {
                            html += `
                                <div class="col-xxl-2 col-md-4 col-sm-6 col-xsm-6 custom__col">
                                    <div class="screenshot-item">
                                        <button class="screenshot-item-thumb skeleton-box">
                                        </button>
                                        <span class="screenshot-item-duration skeleton-box">
                                            <span class="skeleton-box"></span>
                                        </span>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="skeleton-box" style="width:40%; height:12px;"></small>
                                            <small class="skeleton-box" style="width:40%; height:12px;"></small>
                                        </div>
                                        <div class="skeleton-box mt-1" style="height:8px;"></div>
                                    </div>
                                </div>
                            `;
                        }

                        html += `</div></div>`;
                    }

                    html += '</div>';
                    return html;
                }

                function loadGrid() {
                    const date = $date.val();
                    const user = $('.js-member').val();
                    const mode = $('.js-mode.active').data('mode') || '10min';

                    $('#screenshot-grid').html(skeletonHTML());

                    $.get(url, {
                            date,
                            user,
                            mode
                        },
                        function(response) {
                            $('#screenshot-grid').html(response.view);
                            updateNav();

                            initLazy();
                        }
                    );
                }

                function updateNav() {
                    const selected = moment($date.val(), 'MM/DD/YYYY').startOf('day');
                    const today = moment().startOf('day');
                    const isToday = selected.isSame(today);
                    $(document).find('.js-next-day').prop('disabled', isToday);
                }

                updateNav();

                $('#dateRange').on('change', function() {
                    loadGrid();
                    updateNav();
                }).change();

                $('.js-member').on('change', loadGrid);
                $('.js-mode').on('click', function() {
                    if ($(this).hasClass('active')) {
                        return;
                    }
                    $('.js-mode').removeClass('active');
                    $(this).addClass('active');
                    loadGrid();
                });

                function setDate(momentDate) {
                    const max = moment().startOf('day');
                    if (momentDate.isAfter(max)) {
                        momentDate = max.clone();
                    }
                    const drp = $date.data('daterangepicker');
                    if (drp) {
                        drp.setStartDate(momentDate.toDate());
                        drp.setEndDate(momentDate.toDate());
                    }
                    $date.val(momentDate.format('MM/DD/YYYY')).trigger('change');
                }

                $(document).on('click', '.js-prev-day', function() {
                    const current = moment($date.val(), 'MM/DD/YYYY').startOf('day');
                    setDate(current.subtract(1, 'day'));
                });
                $(document).on('click', '.js-next-day', function() {
                    const current = moment($date.val(), 'MM/DD/YYYY').startOf('day');
                    setDate(current.add(1, 'day'));
                });
            });

            let actionUrl = `{{ route('user.activity.screenshot.slice.load') }}`;

            $(document).on('click', '.loadSliceScreenshot', function() {
                let user = $('.js-member').val();
                let date = $('#dateRange').val();
                let start = $(this).data('start');

                let data = {
                    user,
                    date,
                    start
                };

                $.get(actionUrl, data, function(response) {
                    let gallery = $('#screenshotGallery');
                    gallery.empty(); // clear old images

                    if (response.length === 0) {
                        gallery.html('');
                        return;
                    }

                    // Append new screenshots as hidden links
                    response.forEach(function(item) {
                        let link = $('<a/>', {
                            href: item.url,
                            'data-lightbox': 'screenshots',
                            'data-title': `Project: ${item.project?.title || 'N/A'} | Taken at: ${formatTime(item.taken_at)}`,
                            style: 'display:none;' // hide links so they don't show on the page
                        });
                        gallery.append(link);
                    });

                    // Reinitialize Lightbox for dynamically added elements
                    if (typeof lightbox !== 'undefined') {
                        lightbox.init(); // binds click events to new <a data-lightbox> links
                        lightbox.option({
                            'resizeDuration': 200,
                            'wrapAround': true,
                        });
                    }

                    // Auto-open the first screenshot
                    gallery.find('a[data-lightbox]').first().trigger('click');

                });
            });

            function formatTime(time) {
                time = time.split(' ');
                return time[1]+' '+time[2];
            }

        })(jQuery);
    </script>
@endpush




@push('style')
    <style>
        @media (min-width: 900px) and (max-width: 1550px) {
            .custom__col {
                flex: 0 0 auto;
                width: 33.3%;
            }
            .screenshot-item-thumb {
                --screenshot-img-height: 180px;
            }
        }

        .lb-dataContainer {
            background-color: #f8f9fa !important;
        }

        .lb-data .lb-details {
            padding-left: 15px !important;
            color: #000;
        }

        .lb-data .lb-close {
            transform: translate(-9px, 6px);
            filter: invert(0%) sepia(0%) saturate(7500%) hue-rotate(313deg) brightness(13%) contrast(107%);
        }
    </style>
@endpush

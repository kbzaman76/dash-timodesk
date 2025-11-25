@extends('Template::layouts.master')

@section('content')
    <div class="table-wrapper">
        <div id="calender-header" class="table-filter">
            <div class="table-filter-left d-flex align-items-center gap-2">
                <button id="btn-prev" class="datepicker-arrow-btn" type="button">
                    <i class="fa-solid fa-arrow-left-long"></i>
                </button>

                <button id="btn-next" class="datepicker-arrow-btn" type="button">
                    <i class="fa-solid fa-arrow-right-long"></i>
                </button>

                <h6 id="year-label" class="mb-0">{{ now()->year }}</h6>
            </div>
        </div>
    </div>

    <div id="leaderboardWrapper" class="mt-4">

    </div>
@endsection

@push('script')
    <script>
        (function($) {
            "use strict";

            let years = JSON.parse(`@php echo json_encode(array_reverse($years)) @endphp`);

            let currentYear = parseInt($('#year-label').text());

            function updateButtons() {
                $('#btn-prev').prop('disabled', years.indexOf(currentYear) === 0);
                $('#btn-next').prop('disabled', years.indexOf(currentYear) === years.length - 1);
            }

            function loadLeaderboard(year) {
                $('#leaderboardWrapper').html(`
                    <div class="row gy-3">
                        @foreach (range(1, 4) as $value)
                            <div class="col-md-3">
                                <div class="card custom--card h-100">
                                    <div class="card-header">
                                        <h5 class="card-title skeleton-box"></h5>
                                    </div>
                                    <div class="card-body text-center">
                                        <div class="user-image skeleton-box"></div>
                                        <p class="skeleton-box user-name"></p>
                                    </div>
                                    <div class="card-footer">
                                        <div class="d-flex flex-wrap justify-content-between gap-3">
                                            <p class="user-info skeleton-box"></p>
                                            <p class="user-info skeleton-box"></p>
                                            <p class="user-info skeleton-box"></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                `);
                $.ajax({
                    url: "{{ route('user.performer.leaderboard.load') }}",
                    type: 'GET',
                    data: {
                        year: year
                    },
                    success: function(res) {
                        // return;
                        $('#leaderboardWrapper').html(res.html);
                        $('#year-label').text(year);
                        currentYear = year;
                        updateButtons();
                    }
                });
            }

            // Initial load
            loadLeaderboard(currentYear);

            $('#btn-prev').on('click', function() {
                let index = years.indexOf(currentYear);
                if (index > 0) {
                    loadLeaderboard(years[index - 1]);
                }
            });

            $('#btn-next').on('click', function() {
                let index = years.indexOf(currentYear);
                if (index < years.length - 1) {
                    loadLeaderboard(years[index + 1]);
                }
            });

        })(jQuery);
    </script>
@endpush


@push('style')
    <style>
        .user-image {
            width: 100px;
        }

        .user-image.skeleton-box {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            margin-inline: auto;
            margin-bottom: 16px;
        }

        .user-name.skeleton-box {
            width: 240px;
            height: 24px;
            margin-inline: auto;
        }

        .user-info.skeleton-box {
            width: calc(50% - 16px);
            height: 24px;
        }
    </style>
@endpush

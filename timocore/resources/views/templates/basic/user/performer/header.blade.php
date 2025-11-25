<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 w-100 filter-header">
    <div class="d-flex gap-2 align-items-center">
        <select class="perfomer-filter sm-style form--control select2" data-minimum-results-for-search="-1">
            <option value="time_activity">@lang('Time & Activity')</option>
            <option value="time">@lang('Time')</option>
            <option value="activity">@lang('Activity')</option>
        </select>
        <div class="datepicker-wrapper">
            <div class="datepicker-inner">
                <span class="icon">
                    <x-icons.calendar />
                </span>
                <input id="performer_page_filter" type="text" value="{{ $performerDateRange }}"
                    class="form--control md-style datepicker2-range-max-today" date-range="true" />
            </div>
        </div>
    </div>

    <div>
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


@pushOnce('script-lib')
    <script src="{{ asset('assets/admin/js/moment.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/daterangepicker.min.js') }}"></script>
@endpushOnce

@pushOnce('style-lib')
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/admin/css/daterangepicker.css') }}">
@endpushOnce

@props(['value' => '', 'label' => '', 'id' => null, 'disable_options' => []])
@php($__df_id = $id ?? 'df-' . \Illuminate\Support\Str::uuid())

<div id="{{ $__df_id }}" class="datepicker-wrapper" data-date-filter>
    <div class="datepicker-inner">
        <span class="icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="128" height="128" viewBox="0 0 24 24" fill="none"
                color="currentColor">
                <path fill-rule="evenodd" clip-rule="evenodd"
                    d="M5.99977 8.25005L17.9998 8.25C18.3031 8.25 18.5766 8.43273 18.6927 8.71298C18.8088 8.99324 18.7446 9.31583 18.5301 9.53033L12.5301 15.5303C12.2373 15.8232 11.7624 15.8232 11.4695 15.5303L5.46944 9.53038C5.25494 9.31588 5.19077 8.9933 5.30686 8.71304C5.42294 8.43278 5.69642 8.25005 5.99977 8.25005Z"
                    fill="currentColor"></path>
            </svg>
        </span>

        <input name="date_label" type="search" class="form--control md-style date-range"
            placeholder="@lang('Select Range')" autocomplete="off" value="{{ $label }}" readonly>
    </div>

    <input type="hidden" name="date" class="date-range-value" value="{{ $value }}">
</div>


@push('script-lib')
    <script src="{{ asset('assets/admin/js/moment.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/daterangepicker.min.js') }}"></script>
@endpush

@push('style-lib')
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/admin/css/daterangepicker.css') }}">
@endpush
@push('script')
    <script>
        (function($) {
            "use strict"

            const $root = $('#{{ $__df_id }}');
            const $input = $root.find('.date-range');
            const $hidden = $root.find('.date-range-value');

            var allowOptions = {
                'Today': [moment(), moment()],
                'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                'Last 15 Days': [moment().subtract(14, 'days'), moment()],
                'Last 30 Days': [moment().subtract(30, 'days'), moment()],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month')
                    .endOf('month')
                ],
                'Last 6 Months': [moment().subtract(6, 'months').startOf('month'), moment().endOf('month')],
                'This Year': [moment().startOf('year'), moment().endOf('year')],
            };

            const disAllowOptions = @json($disable_options);

            var filteredOptions = {};
            Object.keys(allowOptions).forEach(function(key) {
                if (!disAllowOptions.includes(key)) {
                    filteredOptions[key] = allowOptions[key];
                }
            });


            const datePicker = $input.daterangepicker({
                autoUpdateInput: false,
                applyButtonClasses: "btn btn--base btn--sm",
                cancelClass: "btn btn--secondary btn--sm",
                locale: {
                    cancelLabel: 'Clear'
                },
                showDropdowns: true,
                ranges: filteredOptions,
                maxDate: moment()
            });
            const rangeMap = {
                'Today': [moment().startOf('day'), moment().endOf('day')],
                'Yesterday': [moment().subtract(1, 'days').startOf('day'), moment().subtract(1, 'days').endOf(
                    'day')],
                'Last 7 Days': [moment().subtract(6, 'days').startOf('day'), moment().endOf('day')],
                'Last 15 Days': [moment().subtract(14, 'days').startOf('day'), moment().endOf('day')],
                'Last 30 Days': [moment().subtract(30, 'days').startOf('day'), moment().endOf('day')],
                'This Month': [moment().startOf('month').startOf('day'), moment().endOf('month').endOf('day')],
                'Last Month': [moment().subtract(1, 'month').startOf('month').startOf('day'), moment().subtract(1,
                    'month').endOf('month').endOf('day')],
                'Last 6 Months': [moment().subtract(6, 'months').startOf('month').startOf('day'), moment().endOf(
                    'month').endOf('day')],
                'This Year': [moment().startOf('year').startOf('day'), moment().endOf('year').endOf('day')],
            };

            const formatRange = (start, end) => start.format('MMMM DD, YYYY') + ' - ' + end.format('MMMM DD, YYYY');

            const findLabelFor = (start, end) => {
                for (const [label, [rs, re]] of Object.entries(rangeMap)) {
                    if (start.isSame(rs) && end.isSame(re)) return label;
                }
                return null;
            };

            $input.on('apply.daterangepicker', (event, picker) => {
                const start = picker.startDate.clone().startOf('day');
                const end = picker.endDate.clone().endOf('day');
                const label = picker.chosenLabel && picker.chosenLabel !== 'Custom Range' ? picker.chosenLabel :
                    null;
                const display = label ?? formatRange(start, end);
                $input.val(display);
                $hidden.val(formatRange(start, end));
                $root.trigger('date-filter:change', [{
                    id: '{{ $__df_id }}',
                    value: $hidden.val(),
                    label: display
                }]);
            });

            $input.on('cancel.daterangepicker', function(event, picker) {
                $input.val('');
                $hidden.val('');
                $root.trigger('date-filter:change', [{
                    id: '{{ $__df_id }}',
                    value: '',
                    label: ''
                }]);
            });


            const initVal = $hidden.val();
            if (initVal) {
                let dateRange = initVal.split(' - ');
                const start = moment(new Date(dateRange[0])).startOf('day');
                const end = moment(new Date(dateRange[1])).endOf('day');
                $input.data('daterangepicker').setStartDate(start.toDate());
                $input.data('daterangepicker').setEndDate(end.toDate());
                const label = findLabelFor(start, end);
                $input.val(label ?? formatRange(start, end));
            }

        })(jQuery)
    </script>
@endpush

@push('style')
    <style>
        .form--control.date-range {
            position: relative;
            background: transparent;
            padding: 0;
            padding-right: 10px !important;
            field-sizing: content;
            box-shadow: unset !important;
            max-width: 150px;
            text-overflow: ellipsis;
            cursor: pointer;
        }

        .datepicker-inner:has(.date-range) {
            line-height: 1;
        }

        @media (max-width: 991px) {
            .form--control.date-range {
                font-size: 13px;
                max-width: 110px;
            }

            .datepicker-inner:has(.date-range) .icon svg {
                height: 16px;
                width: 16px;
            }
        }

        .datepicker-inner:has(.date-range) .icon {
            width: auto;
        }
    </style>
@endpush

<table class="activity-table-main table-striped">
    <thead>
        <tr>
            <th>@lang('Date')</th>
            <th class="project-heading" data-label="@lang('Project')"></th>
            <th class="text-center">@lang('Total Time') (@lang('hh:mm'))</th>
            <th class="text-center">@lang('Activity')</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        @forelse ($dates as $date)
            @php
                $collapseKey = 'time-date-' . $loop->index;
                $totalSeconds = $date->totalSeconds ?? 0;
                $activityPercent = showAmount(($date->totalActivity ?? 0) / ($totalSeconds > 0 ? $totalSeconds : 1), currencyFormat: false);
            @endphp
            <tr>
                <td colspan="100%">
                    <table class="table activity-table">
                        <tbody>
                            <tr class="parent-row" data-bs-toggle="collapse" data-bs-target=".{{ $collapseKey }}"
                                aria-expanded="false">
                                <td>
                                    <span class="activity-date d-inline-flex align-items-center gap-2">
                                        <span class="icon">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                                                fill="none">
                                                <path d="M16 2V6M8 2V6" stroke="currentColor" stroke-width="2"
                                                    stroke-linecap="round" stroke-linejoin="round"></path>
                                                <path
                                                    d="M13 4H11C7.23 4 5.34 4 4.17 5.17C3 6.34 3 8.23 3 12V14C3 17.77 3 19.66 4.17 20.83C5.34 22 7.23 22 11 22H13C16.77 22 18.66 22 19.83 20.83C21 19.66 21 17.77 21 14V12C21 8.23 21 6.34 19.83 5.17C18.66 4 16.77 4 13 4Z"
                                                    stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                    stroke-linejoin="round"></path>
                                                <path d="M3 10H21" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                    stroke-linejoin="round"></path>
                                                <path opacity="0.4"
                                                    d="M12 14H12.01M12 18H12.01M16 14H16.01M8 14H8.01M8 18H8.01"
                                                    stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                    stroke-linejoin="round"></path>
                                            </svg>
                                        </span>
                                        {{ showDateTime($date->usage_date, 'Y-m-d') }}
                                    </span>
                                </td>
                                <td><span class="opacity-0">hidden</span></td>
                                <td>{{ formatSecondsToHoursMinutes($totalSeconds) }}</td>
                                <td>{{ $activityPercent }}%</td>
                                <td>
                                    <button class="toggle-btn" type="button"
                                        data-bs-toggle="collapse" data-bs-target=".{{ $collapseKey }}">
                                        <i class="las la-angle-down"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr class="collapse {{ $collapseKey }}" data-lazy="true" data-loaded="0"
                                data-level="date_users" data-date="{{ $date->usage_date }}">
                                <td class="border-0" colspan="100%">
                                    <div class="lazy-content p-1 text-center text-muted section-bg">
                                        @lang('Expand to view members')
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="100%" class="py-4">
                    <x-user.no-data title="No time & activity data found" />
                </td>
            </tr>
        @endforelse
    </tbody>
</table>

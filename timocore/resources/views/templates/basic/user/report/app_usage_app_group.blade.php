<table class="activity-table-main">
    <thead>
        <tr>
            <th>@lang('App')</th>
            <th class="date-heading" data-label="@lang('Date')"></th>
            <th class="text-center">@lang('Total Time') (@lang('hh:mm'))</th>
            <th class="text-center"></th>
        </tr>
    </thead>
    <tbody>
        @forelse ($apps as $app)
            @php
                $collapseKey = 'app-group-' . $loop->index;
            @endphp
            <tr>
                <td colspan="100%">
                    <table class="table activity-table">
                        <tbody>
                            <tr class="parent-row" data-bs-toggle="collapse" data-bs-target=".{{ $collapseKey }}" aria-expanded="false">
                                <td>
                                    <div class="activity-table-project">
                                        <x-icons.app :name="$app->app_name" />
                                        {{ __($app->app_name) }}
                                    </div>
                                </td>
                                <td></td>
                                <td>{{ formatSecondsToHoursMinutes($app->totalSeconds ?? 0) }}</td>
                                <td>
                                    <button class="toggle-btn" type="button" data-bs-toggle="collapse"
                                        data-bs-target=".{{ $collapseKey }}">
                                        <i class="las la-angle-down"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr class="collapse {{ $collapseKey }}" data-lazy="true" data-loaded="0" data-level="app_members"
                                data-app="{{ $app->app_name }}">
                                <td colspan="100%" class="border-0">
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
                <td colspan="100%">
                    <x-user.no-data title="No app usage data found" />
                </td>
            </tr>
        @endforelse
    </tbody>
</table>

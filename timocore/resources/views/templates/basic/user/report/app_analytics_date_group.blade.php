<table class="activity-table-main">
    <thead>
        <tr>
            <th>@lang('App')</th>
            <th>@lang('User/Date')</th>
            <th>@lang('Total Time')</th>
            <th>@lang('Action')</th>
        </tr>
    </thead>
    <tbody>
        @php
            $appsByName = $apps->groupBy('app_name')->sortByDesc(function ($g) {
                return $g->sum('totalSeconds');
            });
        @endphp
        @foreach ($appsByName as $appName => $items)
            @php($appTotalSeconds = $items->sum('totalSeconds'))
            <tr>
                <td colspan="100%">
                    <table class="table activity-table">
                        <tbody>
                            <tr class="parent-row" data-bs-toggle="collapse" data-bs-target=".group-{{ $loop->index }}" aria-expanded="false">
                                <td>
                                    <div class="activity-table-project">
                                        <x-icons.app :name="$appName" />
                                        {{ __($appName) }}
                                    </div>
                                </td>
                                <td></td>
                                <td>{{ formatSecondsToHoursMinutes($appTotalSeconds) }}</td>
                                <td>
                                    <button class="toggle-btn" type="button" data-bs-toggle="collapse" data-bs-target=".group-{{ $loop->index }}">
                                        <i class="las la-angle-down"></i>
                                    </button>
                                </td>
                            </tr>

                            <tr class="collapse group-{{ $loop->index }}">
                                <td class="border-0" colspan="100%">
                                    <table class="table activity-inner-table">
                                        <tbody>
                                            @foreach ($items->groupBy('created_on') as $date => $dateApps)
                                                @php($dateTotalSeconds = $dateApps->sum('totalSeconds'))
                                                <tr>
                                                    <td></td>
                                                    <td>
                                                        <div class="activity-table-user">
                                                            {{ showDateTime($date,'Y-m-d') }}
                                                        </div>
                                                    </td>
                                                    <td>{{ formatSecondsToHoursMinutes($dateTotalSeconds) }}</td>
                                                    <td></td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

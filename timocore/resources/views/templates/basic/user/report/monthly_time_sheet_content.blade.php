<thead>
    <tr>
        <th>@lang('Name')</th>
        @for ($d = 1; $d <= $daysInMonth; $d++)
            <th>{{ $d }}</th>
        @endfor
        <th>@lang('Avg')</th>
        <th>@lang('Total')</th>
        <th>@lang('Worked Day')</th>
    </tr>
</thead>
<tbody>
    @forelse ($users as $user)
        <tr>
            <td class="text-start">
                <span class="overview-col-abs">{{ toTitle($user->fullname) }}</span>
            </td>

            @for ($d = 1; $d <= $daysInMonth; $d++)
                @php
                    $seconds = $groupedTracks[$user->id][$d] ?? 0;
                    $display = $seconds > 0 ? formatSecondsToHoursMinutes($seconds) : '';
                @endphp

                <td data-second="{{ $seconds }}">
                    <span class="{{ $display == '' ? 'bg--muted' : '' }}">
                        {{ $display }}
                    </span>
                </td>
            @endfor

            @php
                $total = $userStats[$user->id]['total'] ?? 0;
                $days = $userStats[$user->id]['days'] ?? 0;
                $avgSecs = $days > 0 ? floor($total / $days) : 0;
            @endphp

            <td data-second="{{ $avgSecs }}">
                <span class="overview-col-avg">
                    {{ $avgSecs > 0 ? formatSecondsToHoursMinutes($avgSecs) : '-' }}
                </span>
            </td>
            <td>
                <span class="overview-col-abs">
                    {{ formatSecondsToHoursMinutes($total) }}
                </span>
            </td>
            <td>
                <span class="overview-col-abs">
                    {{ $days }}
                </span>
            </td>
        </tr>
    @empty
        <tr >
            <td class="time_sheet_table-no__data" colspan="100%">
                <x-user.no-data title="No app usage data found" />
            </td>
        </tr>
    @endforelse
</tbody>

@if ($dates->isEmpty())
    <div class="text-center py-3">
        <span class="text-muted">@lang('No usage records available')</span>
    </div>
@else
    <table class="table activity-inner-table mb-0">
        <tbody>
            @foreach ($dates as $date)
                <tr>
                    <td></td>
                    <td>
                        <div class="activity-table-user">
                            {{ showDateTime($date->usage_date, 'Y-m-d') }}
                        </div>
                    </td>
                    <td>{{ formatSecondsToHoursMinutes($date->totalSeconds ?? 0) }}</td>
                    <td></td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif

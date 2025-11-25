@if ($projects->isEmpty())
    <div class="text-center py-3">
        <span class="text-muted">@lang('No project data available')</span>
    </div>
@else
    <table class="table activity-inner-table mb-0">
        <tbody>
            @foreach ($projects as $projectTrack)
                @php
                    $project = $projectTrack->project ?? null;
                    $title = $project->title ?? null;
                    $totalSeconds = $projectTrack->totalSeconds ?? 0;
                    $activityPercent = showAmount(($projectTrack->totalActivity ?? 0) / ($totalSeconds > 0 ? $totalSeconds : 1), currencyFormat: false);
                @endphp
                <tr>
                    <td></td>
                    <td>
                        <div class="activity-table-project justify-content-start">
                            @if ($title)
                                <span class="icon">
                                    {{ $title[0] }}
                                </span>
                                {{ __($title) }}
                            @else
                                @lang('N/A')
                            @endif
                        </div>
                    </td>
                    <td>{{ formatSecondsToHoursMinutes($totalSeconds) }}</td>
                    <td>{{ $activityPercent }}%</td>
                    <td></td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif

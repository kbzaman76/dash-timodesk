@if ($dates->isEmpty())
    <div class="text-center py-3">
        <span class="text-muted">@lang('No date records available')</span>
    </div>
@else
    <table class="table activity-inner-table mb-0">
        <tbody>
            @foreach ($dates as $dateTrack)
                @php
                    $collapseKey = 'time-project-date-' . \Illuminate\Support\Str::slug(($projectId ?? 'project') . '-' . ($dateTrack->usage_date ?? 'date') . '-' . $loop->index);
                    $totalSeconds = $dateTrack->totalSeconds ?? 0;
                    $activityPercent = showAmount(($dateTrack->totalActivity ?? 0) / ($totalSeconds > 0 ? $totalSeconds : 1), currencyFormat: false);
                @endphp
                <tr class="date-row fw-semibold" data-bs-toggle="collapse" data-bs-target=".{{ $collapseKey }}"
                    aria-expanded="false">
                    <td class="text-start">{{ showDateTime($dateTrack->usage_date, 'Y-m-d') }}</td>
                    <td></td>
                    <td>
                        @if($totalSeconds > 60)
                        {{ formatSecondsToHoursMinutes($totalSeconds) }}
                        @else
                        < 1m
                        @endif
                    </td>
                    <td>{{ $activityPercent }}%</td>
                    <td>
                        <button class="toggle-btn" type="button"
                            data-bs-toggle="collapse" data-bs-target=".{{ $collapseKey }}">
                            <i class="las la-angle-down"></i>
                        </button>
                    </td>
                </tr>
                <tr class="collapse {{ $collapseKey }}" data-lazy="true" data-loaded="0"
                    data-level="project_date_members" data-date="{{ $dateTrack->usage_date }}"
                    data-project="{{ $projectId }}">
                    <td colspan="100%" class="border-0">
                        <div class="lazy-content p-1 text-center text-muted section-bg">
                            @lang('Expand to view projects')
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif

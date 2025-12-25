<table class="activity-table-main">
    <thead>
        <tr>
            <th>@lang('Project')</th>
            <th class="project-heading" data-label="@lang('Member')"></th>
            <th class="text-center">@lang('Total Time') (@lang('hh:mm'))</th>
            <th class="text-center">@lang('Activity')</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        @foreach ($projects as $projectTrack)
            @php
                $project = $projectTrack->project ?? null;
                $title = $project->title ?? null;
                $totalSeconds = $projectTrack->totalSeconds ?? 0;
                $activityPercent = showAmount(
                    ($projectTrack->totalActivity ?? 0) / ($totalSeconds > 0 ? $totalSeconds : 1),
                    currencyFormat: false,
                );
                $collapseKey = 'project-date-' . $loop->index;
            @endphp
            <tr>
                <td colspan="100%">
                    <table class="table activity-table">
                        <tbody>
                            <tr class="parent-row" data-bs-toggle="collapse" data-bs-target=".{{ $collapseKey }}"
                                aria-expanded="false">
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
                                <td></td>
                                <td>
                                    @if ($totalSeconds > 60)
                                        {{ formatSecondsToHoursMinutes($totalSeconds) }}
                                    @else
                                        < 1m @endif
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
                                data-level="project_dates" data-project="{{ $projectTrack->project_id }}">
                                <td class="border-0" colspan="100%">
                                    <div class="lazy-content p-1 text-center text-muted section-bg">
                                        @lang('Expand to view dates')
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

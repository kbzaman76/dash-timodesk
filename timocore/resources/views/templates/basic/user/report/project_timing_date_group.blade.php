<table class="activity-table-main">
    <thead>
        <tr>
            <th>@lang('Project')</th>
            <th><span class="toggle-label">@lang('Date')</span></th>
            <th class="text-center">@lang('Total Time') (@lang('hh:mm'))</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        @php
            $projectsByTitle = $tracks
                ->groupBy(function ($row) {
                    return optional($row->project)->title ?? __('(No Project)');
                })
                ->sortByDesc(function ($g) {
                    return $g->sum('totalSeconds');
                });
        @endphp
        @forelse ($projectsByTitle as $projectTitle => $items)
            @php $projectTotalSeconds = $items->sum('totalSeconds') @endphp
            @php
                $project = $items->first()->project ?? null;
            @endphp
            <tr>
                <td colspan="100%">
                    <table class="table activity-table">
                        <tbody>
                            <tr class="parent-row" data-bs-toggle="collapse" data-bs-target=".group-{{ $loop->index }}"
                                aria-expanded="false">
                                <td>
                                    <div class="activity-table-project project__timing-project-thumb">
                                        <x-user.project-thumb :project="$project" :disable_link="true" />
                                    </div>
                                </td>
                                <td></td>
                                <td>{{ formatSecondsToHoursMinutes($projectTotalSeconds) }}</td>
                                <td>
                                    <button class="toggle-btn" type="button" data-bs-toggle="collapse"
                                        data-bs-target=".group-{{ $loop->index }}">
                                        <i class="las la-angle-down"></i>
                                    </button>
                                </td>
                            </tr>

                            <tr class="collapse group-{{ $loop->index }}">
                                <td class="border-0" colspan="100%">
                                    <div class="lazy-content p-1 text-center text-muted section-bg">
                                        <table class="table activity-inner-table">
                                            <tbody>
                                                @foreach ($items->groupBy('created_on') as $date => $dateItems)
                                                    @php($dateTotalSeconds = $dateItems->sum('totalSeconds'))
                                                    <tr>
                                                        <td></td>
                                                        <td>
                                                            <div class="activity-table-user">
                                                                {{ $date }}
                                                            </div>
                                                        </td>
                                                        <td>{{ formatSecondsToHoursMinutes($dateTotalSeconds) }}</td>
                                                        <td></td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
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
                    <x-user.no-data title="No project timing data found" />
                </td>
            </tr>
        @endforelse
    </tbody>
</table>

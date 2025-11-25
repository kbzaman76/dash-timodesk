<div class="project-timer h-100">
    <ul class="project-timer-list h-100">
        @php $total = $projectTimings->sum('total_seconds'); @endphp
        @forelse ($projectTimings as $projectTiming)
            @php
                $timeInPercent = $total > 0 ? ($projectTiming->total_seconds / $total) * 100 : 0;
            @endphp
            <li class="project-timer-item">
                <div class="project-timer-item-top">
                    <span class="title">{{ $projectTiming->project->title }}</span>
                </div>
                <div class="project-timer-item-bottom">
                    <div class="progress flex-grow-1">
                        <div class="progress-bar bg--base" style="width: {{ $timeInPercent }}%"></div>
                    </div>
                    <span class="duration">{{ formatSeconds($projectTiming->total_seconds) }}</span>
                </div>
            </li>
        @empty
            <li class="project-timer-item h-100">
                <div class="d-flex ms-auto text-center justify-content-center flex-column align-items-center h-100">
                    <img class="img-fluid w-50" src="{{ emptyImage('no-project') }}" alt="No Data">
                    <h6 class="project-empty-title">No project timing yet</h6>
                </div>
            </li>
        @endforelse
    </ul>
</div>

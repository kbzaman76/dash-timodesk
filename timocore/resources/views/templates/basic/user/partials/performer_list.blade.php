@php($max = max(1, $performers->max('seconds') ?? 1))
<div class="project-timer">
    <ul class="project-timer-list">
        @forelse ($performers as $p)
            @php($pct = $max > 0 ? ($p->seconds / $max) * 100 : 0)
            <li class="project-timer-item">
                <div class="project-timer-item-top flex-between">
                    <span class="title">{{ toTitle($p->name) }}</span>
                    <span class="duration">{{ formatSeconds($p->seconds) }}</span>
                </div>
                <div class="project-timer-item-bottom">
                    <div class="progress flex-grow-1">
                        <div class="progress-bar bg--base" style="width: {{ $pct }}%"></div>
                    </div>
                </div>
            </li>
        @empty
            <li class="project-timer-item empty__project">
                <div class="project-timer-item-top no-performer">
                    <img src="{{ emptyImage('top_perfomers') }}" />
                    <h6 class="project-empty-title">No Performer Found</h6>
                </div>
            </li>
        @endforelse
    </ul>
</div>
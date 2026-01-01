@php($total = $appUsages->sum('totalSeconds'))
<div class="project-timer">
    @if (!blank($appUsages))
        <ul class="project-timer-list">
            @foreach ($appUsages as $app)
                @php($pct = $total > 0 ? ($app->totalSeconds / $total) * 100 : 0)
                <li class="project-timer-item style--two">
                    <div class="project-timer-item-top">
                        <span class="title">
                            <x-icons.app :name="$app->app_name" />
                            {{ $app->app_name }}
                        </span>
                        <span class="duration">{{ formatSeconds($app->totalSeconds) }}</span>
                    </div>
                    <div class="project-timer-item-bottom">
                        <div class="progress flex-grow-1">
                            <div class="progress-bar bg--base" style="width: {{ $pct }}%"></div>
                        </div>
                    </div>
                </li>
            @endforeach
        </ul>
    @else
        <div class="empty__analytics">
            <x-user.no-data :title="__('No Apps Data Found')" />
        </div>
    @endif
</div>

<div class="task-list h-100">
    @php $total = $projectTasks->sum('total_seconds'); @endphp
    @forelse ($projectTasks as $projectTask)
        @php
            $timeInPercent = $total > 0 ? ($projectTask->total_seconds / $total) * 100 : 0;
            $project = $projectTask->project;
        @endphp
        <a class="task-list-item" href="{{ route('user.project.details', $project->uid) }}">
            @if ($project->icon_url)
                <img class="task-list-item__thumb" src="{{ $project->icon_url }}" alt="{{ $project->title }}" />
            @else
                <div class="task-list-item__thumb"
                    style="--color-bg: {{ $project->color ? $project->color->bg : getSweetColors()['bg'] }}; --color-text: {{ $project->color ? $project->color->text : getSweetColors()['text'] }}">
                    {{ $project->title[0] }}
                </div>
            @endif

            <div class="task-list-item__content">
                <div class="task-list-item__content-top">
                    <div class="task-list-item__content-wrapper ">
                        <p class="task-list-item__title" href="{{ route('user.project.details', $project->uid) }}">
                            {{ $project->title }}
                        </p>
                        <span class="task-list-item__task">{{ $projectTask->title }}</span>
                    </div>
                    <span class="task-list-item__duration">{{ formatSeconds($projectTask->total_seconds) }}</span>
                </div>
                <div class="task-list-item__content-bottom">
                    <div class="progress">
                        <div class="progress-bar bg--base" style="width: {{ $timeInPercent }}%"></div>
                    </div>
                </div>
            </div>

        </a>
    @empty
        <div class="empty__analytics">
            <x-user.no-data :title="__('No Tasks Found')" />
        </div>

    @endforelse
</div>

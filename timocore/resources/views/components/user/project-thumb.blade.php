@props(['project', 'show_desc' => false, 'disable_link'=>false])

@php
    if (is_array($project)) {
        $project = (object) $project;
    }
@endphp

<div class="flex-align flex-nowrap gap-3">
    @if ($project->icon_url)
        <img src="{{ $project->icon_url }}" alt="{{ $project->title }}" class="project-thumb" />
    @else
        <span
            style="--color-bg: {{ $project->color ? $project->color->bg : getSweetColors()['bg'] }}; --color-text: {{ $project->color ? $project->color->text : getSweetColors()['text'] }}"
            class="project-thumb">{{ $project->title[0] }}
        </span>
    @endif
    <div class="project-content">
        <a href="@if($disable_link) javascript:void(0) @else {{ route('user.project.details', $project->uid) }} @endif"
            class="fw-medium text--base fs-14 project__title">{{ $project->title }}</a>

        @if ($show_desc && ($project?->description ?? false))
            <p class="fs-14">{{ $project->description }}</p>
        @endif
    </div>
</div>

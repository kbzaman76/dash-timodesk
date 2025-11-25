<div class="card custom--card h-100">
    <div class="card-header">
        <div class="flex-between gap-2">
            <h6 class="card-title">@lang('Project Timing')</h6>
            <x-date-filter :value="$defaultDateRange ?? ''" :label="$defaultLabel ?? ''" id="project_time_filter" />
        </div>
    </div>
    <div class="card-body">
        <div class="project-timing-wrapper h-100">
            <div class="project_timing_container h-100"></div>
            <a href="{{ route('user.report.project.timing') }}" class="view-more-btn">
                @lang('View More')
            </a>
        </div>
    </div>
</div>

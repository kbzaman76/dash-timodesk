<div class="card custom--card overflow-hidden analytics__card h-100">
    <div class="card-header">
        <div class="flex-between gap-2">
            <h6 class="card-title">@lang('Apps Analytics')</h6>
            <x-date-filter :value="$defaultDateRange ?? ''" :label="$defaultLabel ?? ''" id="app_uses_filter" />
        </div>
    </div>
    <div class="card-body">
        <div class="app-uses-wrapper">
            <div id="app-uses-container"></div>
            <a href="{{ route('user.report.app.analytics') }}" class="view-more-btn">
                @lang('View More')
            </a>
        </div>
    </div>
</div>

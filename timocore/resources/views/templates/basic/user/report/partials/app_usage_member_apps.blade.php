@if ($apps->isEmpty())
    <div class="text-center py-4">
        <span class="text-muted">@lang('No app usage data available')</span>
    </div>
@else
    <table class="table activity-inner-table mb-0">
        <tbody>
            @foreach ($apps as $app)
                @php
                    $collapseKey = 'member-app-' . \Illuminate\Support\Str::slug($memberId . '-' . $app->app_name . '-' . $loop->index);
                @endphp
                <tr class="parent-row" data-bs-toggle="collapse" data-bs-target=".{{ $collapseKey }}" aria-expanded="false">
                    <td>
                        <div class="activity-table-project">
                            <x-icons.app :name="$app->app_name" />
                            {{ __($app->app_name) }}
                        </div>
                    </td>
                    <td></td>
                    <td>
                        @if($app->totalSeconds > 60)
                        {{ formatSecondsToHoursMinutes($app->totalSeconds ?? 0) }}
                        @else
                        < 1m
                        @endif
                    </td>
                    <td>
                        <button class="toggle-btn" type="button" data-bs-toggle="collapse"
                            data-bs-target=".{{ $collapseKey }}">
                            <i class="las la-angle-down"></i>
                        </button>
                    </td>
                </tr>
                <tr class="collapse {{ $collapseKey }}" data-lazy="true" data-loaded="0" data-level="member_app_dates"
                    data-member="{{ $memberId }}" data-app="{{ $app->app_name }}">
                    <td colspan="100%" class="border-0">
                        <div class="lazy-content p-1 text-center text-muted section-bg">
                            @lang('Expand to view dates')
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif

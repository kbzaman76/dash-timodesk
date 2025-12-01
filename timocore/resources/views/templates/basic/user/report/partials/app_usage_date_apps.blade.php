@if ($apps->isEmpty())
    <div class="text-center py-4">
        <x-user.no-data title="No app usage data found" />
    </div>
@else
    <table class="table activity-inner-table mb-0">
        <tbody>
            @foreach ($apps as $app)
                @php
                    $collapseKey = 'date-app-' . \Illuminate\Support\Str::slug($dateKey . '-' . $app->app_name . '-' . $loop->index);
                @endphp
                <tr class="user-row" @role('manager|organizer') data-bs-toggle="collapse" data-bs-target=".{{ $collapseKey }}" aria-expanded="false" @endrole>
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
                        @role('manager|organizer')
                        <button class="toggle-btn" type="button" data-bs-toggle="collapse"
                            data-bs-target=".{{ $collapseKey }}">
                            <i class="las la-angle-down"></i>
                        </button>
                        @endrole
                    </td>
                </tr>
                @role('manager|organizer')
                <tr class="collapse {{ $collapseKey }}" data-lazy="true" data-loaded="0" data-level="date_app_members"
                    data-date="{{ $dateKey }}" data-app="{{ $app->app_name }}">
                    <td colspan="100%" class="border-0">
                        <div class="lazy-content p-1 text-center text-muted section-bg">
                            @lang('Expand to view members')
                        </div>
                    </td>
                </tr>
                @endrole
            @endforeach
        </tbody>
    </table>
@endif

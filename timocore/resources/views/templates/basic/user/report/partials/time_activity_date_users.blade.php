@if ($users->isEmpty())
    <div class="text-center py-3">
        <span class="text-muted">@lang('No member data available')</span>
    </div>
@else
    <table class="table activity-inner-table mb-0">
        <tbody>
            @foreach ($users as $userTrack)
                @php
                    $user = $userTrack->user ?? null;
                    $collapseKey = 'time-date-user-' . \Illuminate\Support\Str::slug(($dateKey ?? 'date') . '-' . ($userTrack->user_id ?? 'user') . '-' . $loop->index);
                    $totalSeconds = $userTrack->totalSeconds ?? 0;
                    $activityPercent = showAmount(($userTrack->totalActivity ?? 0) / ($totalSeconds > 0 ? $totalSeconds : 1), currencyFormat: false);
                @endphp
                <tr class="user-row" data-bs-toggle="collapse" data-bs-target=".{{ $collapseKey }}" aria-expanded="false">
                    <td>
                        <div class="activity-table-user user-table__cell ">
                            <span class="icon">
                                <img class="fit-image" src="{{ $user->image_url ?? asset('assets/images/avatar.png') }}" alt="@lang('Image')">
                            </span>
                            {{ toTitle($user->fullname) ?? '' }}
                        </div>
                    </td>
                    <td></td>
                    <td>
                        @if($totalSeconds > 60)
                        {{ formatSecondsToHoursMinutes($totalSeconds) }}
                        @else
                        < 1m
                        @endif
                    </td>
                    <td>{{ $activityPercent }}%</td>
                    <td>
                        <button class="toggle-btn" type="button"
                            data-bs-toggle="collapse" data-bs-target=".{{ $collapseKey }}">
                            <i class="las la-angle-down"></i>
                        </button>
                    </td>
                </tr>
                <tr class="collapse {{ $collapseKey }}" data-lazy="true" data-loaded="0" data-level="date_user_projects"
                    data-date="{{ $dateKey }}" data-member="{{ $userTrack->user_id }}">
                    <td colspan="100%" class="border-0">
                        <div class="lazy-content text-muted text-center section-bg p-1">
                            @lang('Expand to view projects')
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif

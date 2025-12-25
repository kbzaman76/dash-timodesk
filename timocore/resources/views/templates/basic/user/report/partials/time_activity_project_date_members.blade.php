@if ($members->isEmpty())
    <div class="text-center py-3">
        <span class="text-muted">@lang('No project data available')</span>
    </div>
@else
    <table class="table activity-inner-table mb-0">
        <tbody>
            @foreach ($members as $memberTrack)
                @php
                    $user = $memberTrack->user ?? null;
                    $totalSeconds = $memberTrack->totalSeconds ?? 0;
                    $activityPercent = showAmount(($memberTrack->totalActivity ?? 0) / ($totalSeconds > 0 ? $totalSeconds : 1), currencyFormat: false);
                @endphp
                <tr>
                    <td></td>
                    <td class="text-start">
                        <div class="activity-table-user user-table__cell">
                            <span class="icon">
                                <img class="fit-image" src="{{ $user->image_url ?? asset('assets/images/avatar.png') }}"
                                    alt="@lang('Image')">
                            </span>
                            {{ toTitle($user->fullname) ?? __('Unknown Member') }}
                        </div>
                    </td>
                    <td>
                        @if($totalSeconds > 60)
                        {{ formatSecondsToHoursMinutes($totalSeconds) }}
                        @else
                        < 1m
                        @endif
                    </td>
                    <td>{{ $activityPercent }}%</td>
                    <td></td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif

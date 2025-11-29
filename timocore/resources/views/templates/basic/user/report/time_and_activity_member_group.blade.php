<table class="activity-table-main">
    <thead>
        <tr>
            <th>@lang('Member')</th>
            <th class="project-heading" data-label="@lang('Project')"></th>
            <th class="text-center">@lang('Total Time') (@lang('hh:mm'))</th>
            <th class="text-center">@lang('Activity')</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        @forelse ($members as $member)
            @php
                $user = $member->user ?? null;
                $collapseKey = 'time-member-' . $loop->index;
                $totalSeconds = $member->totalSeconds ?? 0;
                $activityPercent = showAmount(($member->totalActivity ?? 0) / ($totalSeconds > 0 ? $totalSeconds : 1), currencyFormat: false);
            @endphp
            <tr>
                <td colspan="100%">
                    <table class="table activity-table">
                        <tbody>
                            <tr class="parent-row" data-bs-toggle="collapse" data-bs-target=".{{ $collapseKey }}"
                                aria-expanded="false">
                                <td>
                                    <div class="activity-table-user user-table__cell">
                                        <span class="icon">
                                            <img class="fit-image" src="{{ $user->image_url ?? asset('assets/images/avatar.png') }}"
                                                alt="@lang('Image')">
                                        </span>
                                        {{ toTitle($user->fullname) ?? '' }}
                                    </div>
                                </td>
                                <td><span class="opacity-0">hidden</span></td>
                                <td>
                                    @if($totalSeconds > 60)
                                    {{ formatSecondsToHoursMinutes($totalSeconds ?? 0) }}
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
                            <tr class="collapse {{ $collapseKey }}" data-lazy="true" data-loaded="0"
                                data-level="member_dates" data-member="{{ $member->user_id }}">
                                <td class="border-0" colspan="100%">
                                    <div class="lazy-content p-1 text-center text-muted section-bg">
                                        @lang('Expand to view dates')
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="100%" class="py-4">
                    <x-user.no-data title="No time & activity data found" />
                </td>
            </tr>
        @endforelse
    </tbody>
</table>

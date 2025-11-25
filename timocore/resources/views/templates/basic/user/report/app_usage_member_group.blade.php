<table class="activity-table-main">
    <thead>
        <tr>
            <th>@lang('Member')</th>
            <th class="text-left date-heading" data-label="@lang('Date')"></th>
            <th class="text-center">@lang('Total Time') (@lang('hh:mm'))</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        @forelse ($members as $member)
            @php
                $user        = $member->user ?? null;
                $collapseKey = 'member-group-' . $loop->index;
            @endphp
            <tr>
                <td colspan="100%">
                    <table class="table activity-table">
                        <tbody>
                            <tr class="parent-row" data-bs-toggle="collapse" data-bs-target=".{{ $collapseKey }}" aria-expanded="false">
                                <td>
                                    <div class="activity-table-user user-table__cell">
                                        <span class="icon">
                                            <img class="fit-image" src="{{ $user->image_url ?? asset('assets/images/avatar.png') }}"
                                                alt="@lang('Image')">
                                        </span>
                                        {{ toTitle($user->fullname) ?? '' }}
                                    </div>
                                </td>
                                <td></td>
                                <td>{{ formatSecondsToHoursMinutes($member->totalSeconds ?? 0) }}</td>
                                <td>
                                    <button class="toggle-btn" type="button" data-bs-toggle="collapse"
                                        data-bs-target=".{{ $collapseKey }}">
                                        <i class="las la-angle-down"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr class="collapse {{ $collapseKey }}" data-lazy="true" data-loaded="0" data-level="member_apps"
                                data-member="{{ $member->user_id }}">
                                <td colspan="100%" class="border-0">
                                    <div class="lazy-content p-1 text-center text-muted section-bg">
                                        @lang('Expand to view apps')
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="100%">
                    <x-user.no-data title="No app usage data found" />
                </td>
            </tr>
        @endforelse
    </tbody>
</table>

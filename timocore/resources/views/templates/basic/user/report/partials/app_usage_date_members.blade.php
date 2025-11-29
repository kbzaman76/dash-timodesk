@if ($members->isEmpty())
    <div class="text-center py-3">
        <span class="text-muted">@lang('No member data available')</span>
    </div>
@else
    <table class="table activity-inner-table mb-0">
        <tbody>
            @foreach ($members as $member)
                @php
                    $user = $member->user ?? null;
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
                        @if($member->totalSeconds > 60)
                        {{ formatSecondsToHoursMinutes($member->totalSeconds ?? 0) }}
                        @else
                        < 1m
                        @endif
                    </td>
                    <td></td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif

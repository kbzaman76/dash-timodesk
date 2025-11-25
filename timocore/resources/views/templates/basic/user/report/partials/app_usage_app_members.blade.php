@if ($members->isEmpty())
    <div class="text-center py-4">
        <span class="text-muted">@lang('No member data available')</span>
    </div>
@else
    <table class="table activity-inner-table mb-0">
        <tbody>
            @foreach ($members as $member)
                @php
                    $user        = $member->user ?? null;
                    $collapseKey = 'app-member-' . \Illuminate\Support\Str::slug($appName . '-' . $member->user_id . '-' . $loop->index);
                @endphp
                <tr class="user-row" data-bs-toggle="collapse" data-bs-target=".{{ $collapseKey }}" aria-expanded="false">
                    <td>
                        <div class="activity-table-user user-table__cell">
                            <span class="icon">
                                <img class="fit-image" src="{{ $user->image_url ?? asset('assets/images/avatar.png') }}"
                                    alt="@lang('Image')">
                            </span>
                            {{ toTitle($user->fullname) ?? __('Unknown Member') }}
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
                <tr class="collapse {{ $collapseKey }}" data-lazy="true" data-loaded="0" data-level="app_member_dates"
                    data-app="{{ $appName }}" data-member="{{ $member->user_id }}">
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

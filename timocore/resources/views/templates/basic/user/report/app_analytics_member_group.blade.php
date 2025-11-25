<table class="activity-table-main">
    <thead>
        <tr>
            <th>@lang('App')</th>
            <th>@lang('User/Date')</th>
            <th>@lang('Total Time')</th>
            <th>@lang('Action')</th>
        </tr>
    </thead>
    <tbody>
        @php
            $appsByName = $apps->groupBy('app_name')->sortByDesc(function ($g) {
                return $g->sum('totalSeconds');
            });
        @endphp
        @foreach ($appsByName as $appName => $appGroup)
            @php
                $appTotalSeconds = $appGroup->sum('totalSeconds');
            @endphp
            <tr>
                <td colspan="100%">
                    <table class="table activity-table">
                        <tbody>
                            <tr class="parent-row" data-bs-toggle="collapse" data-bs-target=".group-{{ $loop->index }}" aria-expanded="false">
                                <td>
                                    <div class="activity-table-project">
                                        <x-icons.app :name="$appName" />
                                        {{ __($appName) }}
                                    </div>
                                </td>
                                <td></td>
                                <td>{{ formatSecondsToHoursMinutes($appTotalSeconds) }}</td>
                                <td>
                                    <button class="toggle-btn" type="button" data-bs-toggle="collapse" data-bs-target=".group-{{ $loop->index }}">
                                        <i class="las la-angle-down"></i>
                                    </button>
                                </td>
                            </tr>

                            <tr class="collapse group-{{ $loop->index }}">
                                <td colspan="100%">
                                    <table class="table activity-inner-table">
                                        <tbody>
                                            @php
                                                $members = $appGroup->groupBy('user_id')->sortBy(function ($g) {
                                                    $name = optional(optional($g->first())->user)->fullname ?? '';
                                                    return strtolower($name);
                                                });
                                            @endphp
                                            @foreach ($members as $userId => $userApps)
                                                @php
                                                    $user = $userApps->first()->user ?? null;
                                                    $userTotalSeconds = $userApps->sum('totalSeconds');
                                                @endphp
                                                <tr class="user-row">
                                                    <td></td>
                                                    <td>
                                                        <div class="activity-table-user">
                                                            <span class="icon">
                                                                <img class="fit-image" src="{{ $user->image_url }}" alt="">
                                                            </span>
                                                            {{ toTitle($user->fullname) ?? '' }}
                                                        </div>
                                                    </td>
                                                    <td>{{ formatSecondsToHoursMinutes($userTotalSeconds) }}</td>
                                                    <td></td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

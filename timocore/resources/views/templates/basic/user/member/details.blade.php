@extends('Template::layouts.master')
@section('content')
    <div class="member-top mb-3">
        <h5 class="member-top__title">{{ $user->fullname }} @if (!$user->isStaff())
                <span>({{ $user->roleText }})</span>
            @endif
        </h5>
        <x-date-filter :value="$dateRange ?? ''" :label="$dateLabel ?? 'Last 30 Days'" id="summaryFilterDate" :disable_options="['Last 6 Months', 'This Year', 'Today', 'Yesterday']" />
    </div>
    <div class="row g-3 mb-3">
        <div class="col-md-6 col-lg-8">
            <div class="card custom--card mb-4 h-100">
                <div class="card-body">
                    <div class="member-form-top">
                        <div class="member-view">
                            <img class="member-view__img" src="{{ $user->image_url }}" alt="member-view" />
                            <div class="member-form-align">
                                <div
                                    class="w-100 d-flex flex-wrap flex-sm-nowrap align-items-center justify-content-between mb-2 mb-lg-4 gap-2">
                                    <h5 class="member-view__name mb-0">{{ toTitle($user->fullname) }}</h5>
                                    <button class="btn btn--sm btn--base newProjectBtn"
                                        type="button">{{ count($user->projects) }} Assigned Projects</button>
                                </div>
                                <div class="row g-3">
                                    <div class="col-xxl-4">
                                        <div class="member-view__info">
                                            <div
                                                class="member-view__info-title d-flex align-items-center justify-content-between gap-2">
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="icon">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="18"
                                                            height="18" viewBox="0 0 24 24" fill="none"
                                                            color="currentColor">
                                                            <path
                                                                d="M7 8.5L9.94202 10.2394C11.6572 11.2535 12.3428 11.2535 14.058 10.2394L17 8.5"
                                                                stroke="currentColor" stroke-width="2"
                                                                stroke-linecap="round" stroke-linejoin="round">
                                                            </path>
                                                            <path
                                                                d="M2.01576 13.4756C2.08114 16.5411 2.11382 18.0739 3.24495 19.2093C4.37608 20.3448 5.95033 20.3843 9.09883 20.4634C11.0393 20.5122 12.9607 20.5122 14.9012 20.4634C18.0497 20.3843 19.6239 20.3448 20.755 19.2093C21.8862 18.0739 21.9189 16.5411 21.9842 13.4756C22.0053 12.4899 22.0053 11.51 21.9842 10.5244C21.9189 7.45883 21.8862 5.92606 20.755 4.79063C19.6239 3.6552 18.0497 3.61565 14.9012 3.53654C12.9607 3.48778 11.0393 3.48778 9.09882 3.53653C5.95033 3.61563 4.37608 3.65518 3.24495 4.79062C2.11382 5.92605 2.08113 7.45882 2.01576 10.5243C1.99474 11.51 1.99474 12.4899 2.01576 13.4756Z"
                                                                stroke="currentColor" stroke-width="2"
                                                                stroke-linejoin="round">
                                                            </path>
                                                        </svg>
                                                    </span>
                                                    <span class="text">Email</span>
                                                </div>
                                                @php
                                                    echo $user->emailStatusBadge;
                                                @endphp
                                            </div>
                                            <div class="member-view__info-desc">{{ $user->email }}</div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-xxl-4">
                                        <div class="member-view__info">
                                            <div class="member-view__info-title">
                                                <div class="flex-align gap-2">
                                                    <span class="icon">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="18"
                                                            height="18" viewBox="0 0 24 24" fill="none"
                                                            color="currentColor">
                                                            <path
                                                                d="M16.5 2H7.5C6.39543 2 5.5 2.89543 5.5 4V20C5.5 21.1046 6.39543 22 7.5 22H16.5C17.6046 22 18.5 21.1046 18.5 20V4C18.5 2.89543 17.6046 2 16.5 2Z"
                                                                stroke="currentColor" stroke-width="2"
                                                                stroke-linecap="round" stroke-linejoin="round">
                                                            </path>
                                                            <path d="M12 19H12.01" stroke="currentColor" stroke-width="2"
                                                                stroke-linecap="round" stroke-linejoin="round"></path>
                                                        </svg>
                                                    </span>
                                                    <span class="text">Phone</span>
                                                </div>
                                                <button type="button" class="member-view__info-edit editPhoneBtn"
                                                    data-phone="{{ $user->mobile }}">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                        viewBox="0 0 24 24" fill="none" color="currentColor">
                                                        <path fill-rule="evenodd" clip-rule="evenodd"
                                                            d="M18.7116 3.40901C17.833 2.53033 16.4083 2.53033 15.5296 3.40901L13.4997 5.43906L18.5604 10.4997L20.5903 8.46965C21.469 7.59098 21.469 6.16637 20.5903 5.28769L18.7116 3.40901ZM17.4997 11.5604L12.4391 6.49975L3.40899 15.5303C2.98705 15.9523 2.75 16.5246 2.75 17.1213V20.5C2.75 20.9142 3.08579 21.25 3.5 21.25H6.87868C7.47542 21.25 8.04773 21.0129 8.46969 20.591L17.4997 11.5604Z"
                                                            fill="currentColor"></path>
                                                    </svg>
                                                </button>
                                            </div>
                                            <div class="member-view__info-desc">{{ $user->mobile ?? 'N/A' }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-xxl-4">
                                        <div class="member-view__info">
                                            <div class="member-view__info-title">
                                                <div class="flex-align gap-2">
                                                    <span class="icon">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="18"
                                                            height="18" viewBox="0 0 24 24" fill="none"
                                                            color="currentColor">
                                                            <path
                                                                d="M11.5 3.99973L16.4998 3.99923C17.6044 3.99911 18.5 4.89458 18.5 5.99922V9.08714C18.5 9.31462 18.3156 9.49902 18.0881 9.49902C18.0056 9.49902 17.9251 9.47426 17.8568 9.42793L16.3462 8.40255"
                                                                stroke="currentColor" stroke-width="1.5"
                                                                stroke-linecap="round" stroke-linejoin="round">
                                                            </path>
                                                            <path
                                                                d="M12 20H7C5.89543 20 5 19.1046 5 18V14.9109C5 14.684 5.18399 14.5 5.41095 14.5C5.494 14.5 5.57511 14.5252 5.64358 14.5722L7.15385 15.6093"
                                                                stroke="currentColor" stroke-width="1.5"
                                                                stroke-linecap="round" stroke-linejoin="round">
                                                            </path>
                                                            <path
                                                                d="M9.00325 11V11.75C9.41746 11.75 9.75325 11.4142 9.75325 11H9.00325ZM2 11H1.25C1.25 11.4142 1.58579 11.75 2 11.75V11ZM9.00325 10.25H2V11.75H9.00325V10.25ZM2.75 11C2.75 9.48154 3.98161 8.25 5.50162 8.25V6.75C3.15384 6.75 1.25 8.65246 1.25 11H2.75ZM5.50162 8.25C7.02164 8.25 8.25325 9.48154 8.25325 11H9.75325C9.75325 8.65246 7.84941 6.75 5.50162 6.75V8.25ZM6.50162 3.75C6.50162 4.30196 6.05387 4.75 5.50081 4.75V6.25C6.88165 6.25 8.00162 5.13104 8.00162 3.75H6.50162ZM5.50081 4.75C4.94775 4.75 4.5 4.30196 4.5 3.75H3C3 5.13104 4.11998 6.25 5.50081 6.25V4.75ZM4.5 3.75C4.5 3.19804 4.94775 2.75 5.50081 2.75V1.25C4.11998 1.25 3 2.36896 3 3.75H4.5ZM5.50081 2.75C6.05387 2.75 6.50162 3.19804 6.50162 3.75H8.00162C8.00162 2.36896 6.88165 1.25 5.50081 1.25V2.75Z"
                                                                fill="currentColor"></path>
                                                            <path
                                                                d="M22.0032 22V22.75C22.4175 22.75 22.7532 22.4142 22.7532 22H22.0032ZM15 22H14.25C14.25 22.4142 14.5858 22.75 15 22.75V22ZM22.0032 21.25H15V22.75H22.0032V21.25ZM15.75 22C15.75 20.4815 16.9816 19.25 18.5016 19.25V17.75C16.1538 17.75 14.25 19.6525 14.25 22H15.75ZM18.5016 19.25C20.0216 19.25 21.2532 20.4815 21.2532 22H22.7532C22.7532 19.6525 20.8494 17.75 18.5016 17.75V19.25ZM19.5016 14.75C19.5016 15.302 19.0539 15.75 18.5008 15.75V17.25C19.8816 17.25 21.0016 16.131 21.0016 14.75H19.5016ZM18.5008 15.75C17.9477 15.75 17.5 15.302 17.5 14.75H16C16 16.131 17.12 17.25 18.5008 17.25V15.75ZM17.5 14.75C17.5 14.198 17.9477 13.75 18.5008 13.75V12.25C17.12 12.25 16 13.369 16 14.75H17.5ZM18.5008 13.75C19.0539 13.75 19.5016 14.198 19.5016 14.75H21.0016C21.0016 13.369 19.8816 12.25 18.5008 12.25V13.75Z"
                                                                fill="currentColor"></path>
                                                        </svg>
                                                    </span>
                                                    <span class="text">Role</span>
                                                </div>
                                                @if (auth()->user()->role <= $user->role)
                                                    <button type="button" class="member-view__info-edit editRoleBtn"
                                                        data-role="{{ $user->role }}">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16"
                                                            height="16" viewBox="0 0 24 24" fill="none"
                                                            color="currentColor">
                                                            <path fill-rule="evenodd" clip-rule="evenodd"
                                                                d="M18.7116 3.40901C17.833 2.53033 16.4083 2.53033 15.5296 3.40901L13.4997 5.43906L18.5604 10.4997L20.5903 8.46965C21.469 7.59098 21.469 6.16637 20.5903 5.28769L18.7116 3.40901ZM17.4997 11.5604L12.4391 6.49975L3.40899 15.5303C2.98705 15.9523 2.75 16.5246 2.75 17.1213V20.5C2.75 20.9142 3.08579 21.25 3.5 21.25H6.87868C7.47542 21.25 8.04773 21.0129 8.46969 20.591L17.4997 11.5604Z"
                                                                fill="currentColor"></path>
                                                        </svg>
                                                    </button>
                                                @endif
                                            </div>
                                            <div class="member-view__info-desc">
                                                {{ $user->getRole() ?? 'N/A' }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="member-form-top-action">
                                    <div class="form--switch">
                                        @if ($user->status != Status::USER_PENDING)
                                            <input type="checkbox" id="member-connect" class="form-check-input"
                                                data-action="{{ route('user.member.status', $user->uid) }}"
                                                @checked($user->status == Status::USER_ACTIVE) @disabled(isEditDisabled($user) ? true : false) />
                                            <label for="member-connect" class="form-check-label fs-15 fw-medium"
                                                @disabled(isEditDisabled($user) ? true : false)>Member Status</label>
                                        @else
                                            <div title="You'll be able to enable member after the member is approved.">
                                                <input type="checkbox" id="disalbe-member" class="form-check-input"
                                                    disabled />
                                                <label for="disalbe-member" class="form-check-label fs-15 fw-medium"
                                                    disabled>Member
                                                    Status</label>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="form--switch">
                                        @if ($user->status != Status::USER_PENDING && $user->ev == Status::YES)
                                            <input type="checkbox" id="track-able" class="form-check-input"
                                                data-action="{{ route('user.member.tracking.status', $user->uid) }}"
                                                @checked($user->tracking_status) @disabled($user->status == Status::USER_BAN) />
                                            <label for="track-able" class="form-check-label fs-15 fw-medium"
                                                @disabled($user->status == Status::USER_BAN)>Able to track
                                                time</label>
                                        @else
                                            <div
                                                title="You'll be able to enable tracking for the member after he/she verify the email.">
                                                <input type="checkbox" id="track-able" class="form-check-input"
                                                    disabled />
                                                <label for="track-able" class="form-check-label fs-15 fw-medium" disabled>
                                                    Able to track time
                                                </label>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-4">
            <div class="card custom--card h-100">
                <div class="card-body">
                    <div class="activity-info activity-info-skeleton-area">
                        <div class="activity-info-item">
                            <div class="activity-info-item__icon skeleton-box">
                            </div>
                            <div class="activity-info-item__content">
                                <h2 class="activity-info-item__title skeleton-box"></h2>
                                <p class="activity-info-item__label skeleton-box"></p>
                            </div>
                        </div>
                        <div class="activity-info-item">
                            <div class="activity-info-item__icon skeleton-box">
                            </div>
                            <div class="activity-info-item__content">
                                <h2 class="activity-info-item__title skeleton-box"></h2>
                                <p class="activity-info-item__label skeleton-box"></p>
                            </div>
                        </div>
                        <div class="activity-info-item">
                            <div class="activity-info-item__icon skeleton-box">
                            </div>
                            <div class="activity-info-item__content">
                                <h2 class="activity-info-item__title skeleton-box"></h2>
                                <p class="activity-info-item__label skeleton-box"></p>
                            </div>
                        </div>
                        <div class="activity-info-item">
                            <div class="activity-info-item__icon skeleton-box">
                            </div>
                            <div class="activity-info-item__content">
                                <h2 class="activity-info-item__title skeleton-box"></h2>
                                <p class="activity-info-item__label skeleton-box"></p>
                            </div>
                        </div>
                    </div>
                    <div class="activity-info activity-info-area d-none">
                        <a class="activity-info-item"
                            href="{{ route('user.report.time.activity.index') }}?user={{ $user->uid }}">
                            <div class="activity-info-item__icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round"
                                    class="lucide lucide-clock-icon lucide-clock">
                                    <path d="M12 6v6l4 2" />
                                    <circle cx="12" cy="12" r="10" />
                                </svg>
                            </div>
                            <div class="activity-info-item__content">
                                <h2 class="activity-info-item__title" id="totalTimeTracked"></h2>
                                <p class="activity-info-item__label">
                                    Total Time Tracked
                                </p>
                            </div>
                        </a>
                        <a class="activity-info-item"
                            href="{{ route('user.report.time.activity.index') }}?user={{ $user->uid }}">
                            <div class="activity-info-item__icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round"
                                    class="lucide lucide-trending-up-icon lucide-trending-up">
                                    <path d="M16 7h6v6" />
                                    <path d="m22 7-8.5 8.5-5-5L2 17" />
                                </svg>
                            </div>
                            <div class="activity-info-item__content">
                                <h2 class="activity-info-item__title" id="averageActivity"></h2>
                                <p class="activity-info-item__label">
                                    Average Activity
                                </p>
                            </div>
                        </a>
                        <a class="activity-info-item"
                            href="{{ route('user.report.project.timing') }}?user={{ $user->uid }}">
                            <div class="activity-info-item__icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round"
                                    class="lucide lucide-chart-area-icon lucide-chart-area">
                                    <path d="M3 3v16a2 2 0 0 0 2 2h16" />
                                    <path
                                        d="M7 11.207a.5.5 0 0 1 .146-.353l2-2a.5.5 0 0 1 .708 0l3.292 3.292a.5.5 0 0 0 .708 0l4.292-4.292a.5.5 0 0 1 .854.353V16a1 1 0 0 1-1 1H8a1 1 0 0 1-1-1z" />
                                </svg>
                            </div>
                            <div class="activity-info-item__content">
                                <h2 class="activity-info-item__title" id="totalProjects"></h2>
                                <p class="activity-info-item__label">
                                    Total Projects
                                </p>
                            </div>
                        </a>
                        <a class="activity-info-item" href="{{ route('user.performer.top') }}">
                            <div class="activity-info-item__icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round"
                                    class="lucide lucide-trophy-icon lucide-trophy">
                                    <path d="M10 14.66v1.626a2 2 0 0 1-.976 1.696A5 5 0 0 0 7 21.978" />
                                    <path d="M14 14.66v1.626a2 2 0 0 0 .976 1.696A5 5 0 0 1 17 21.978" />
                                    <path d="M18 9h1.5a1 1 0 0 0 0-5H18" />
                                    <path d="M4 22h16" />
                                    <path d="M6 9a6 6 0 0 0 12 0V3a1 1 0 0 0-1-1H7a1 1 0 0 0-1 1z" />
                                    <path d="M6 9H4.5a1 1 0 0 1 0-5H6" />
                                </svg>
                            </div>
                            <div class="activity-info-item__content">
                                <h2 class="activity-info-item__title" id="performanceLevel"></h2>
                                <p class="activity-info-item__label">
                                    Performance Level
                                </p>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row g-3 mb-3">
        <div class="col-md-4 col-xxxl-3">
            <div class="card custom--card overflow-hidden analytics__card h-100">
                <div class="card-header">
                    <div class="flex-between gap-2">
                        <h6 class="card-title">@lang('Top Used Apps')</h6>
                        <a href="{{ route('user.report.app.usage') }}?user={{ $user->uid }}&group_by=app"
                            class="btn btn-outline--base btn--sm">
                            @lang('View All')
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="app-uses-wrapper">
                        <div id="topUsedApps"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8 col-xxxl-9">
            <div class="card custom--card h-100">
                <div class="card-header">
                    <div class="flex-between gap-2">
                        <h6 class="card-title">@lang('Timing Chart')</h6>
                        <a href="{{ route('user.report.time.analytics') }}?user={{ $user->uid }}"
                            class="btn btn-outline--base btn--sm">
                            View All
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div id="timeTrackingChart" class="h-100"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="row g-3 mb-3">
        <div class="col-md-8 col-xxxl-9">
            <div class="card custom--card h-100">
                <div class="card-header">
                    <div class="flex-between gap-2">
                        <h6 class="card-title">@lang('Average Activity')</h6>
                        <a href="{{ route('user.report.time.activity.index') }}?user={{ $user->uid }}"
                            class="btn btn-outline--base btn--sm">
                            @lang('View All')
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div id="TrackerVolumeChart" class="chart-container"></div>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-xxxl-3">
            <div class="card custom--card h-100">
                <div class="card-header">
                    <h6 class="card-title">@lang('Top Tracked Task')</h6>
                </div>
                <div class="card-body">
                    <div class="h-100" id="topTrackedTask"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="row g-3 mb-3">
        <div class="col-lg-6 col-xl-6 col-xxxl-4">
            <div class="card custom--card h-100">
                <div class="card-header pb-0">
                    <div class="flex-between gap-2">
                        <h6 class="card-title">@lang('Top Tracked Project')</h6>
                        <a href="{{ route('user.project.list') }}?search={{ $user->fullname }}"
                            class="btn btn-outline--base btn--sm">
                            @lang('View All')
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="w-100 project_timing_container h-100" id="topTrackedProject"></div>
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-xl-6 col-xxxl-8">
            <div class="card custom--card {{ auth()->user()->isStaff() ? 'screenshot__user' : '' }} h-100">
                <div class="card-header">
                    <div class="flex-between gap-2">
                        <h6 class="card-title">@lang('Recent Screenshots')</h6>
                        <a href="{{ route('user.activity.screenshot.index', $user->uid) }}"
                            class="btn btn-outline--base btn--sm">@lang('View All')</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3 g-xxl-4" id="screenshotsArea">
                        @forelse ($recentScreenshots as $screenshot)
                            <div class="col-xsm-6 col-sm-6 col-md-4 col-xl-6 col-xxl-4 ">
                                <div class="screenshot-item">
                                    <div class="screenshot-item-thumb">
                                        <a href="{{ $screenshot->url }}" data-lightbox="screenshots"
                                            data-title="{{ $screenshot->user->fullname }} - {{ showDateTime($screenshot->taken_at, 'M d, Y h:i A') }}">
                                            <div class="overlay">
                                                <span>@lang('View Image')</span>
                                            </div>
                                            <img class="fit-image lazy" data-src="{{ $screenshot->url }}"
                                                src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw=="
                                                alt="Screenshot">
                                        </a>
                                    </div>
                                    <span class="screenshot-item-duration">
                                        {{ showDateTime($screenshot->taken_at, 'M d, Y h:i A') }}
                                    </span>
                                    <p class="screenshot-item-title" title="{{ $screenshot->project->title }}">
                                        {{ str($screenshot->project->title)->limit(40, '...', true) }}
                                    </p>
                                </div>
                            </div>
                        @empty
                            <div
                                class="d-flex ms-auto text-center justify-content-center flex-column align-items-center h-100 py-5">
                                <img class="img-fluid w-25" src="{{ emptyImage('screenshots') }}" alt="No Data">
                                <h6 class="mt-2 project-empty-title">No screenshots found</h6>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade custom--modal" id="editPhoneModal" tabindex="-1" aria-labelledby="NameEditModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        @lang('Update Phone')
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <form action="{{ route('user.member.phone.update', $user->uid) }}" method="post" id="profileForm">
                    <div class="modal-body">
                        @csrf
                        <div class="form-group">
                            <label class="form--label">@lang('Phone')</label>
                            <input type="text" name="phone" class="form--control md-style" maxlength="40"
                                required />
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn--dark btn--md"
                            data-bs-dismiss="modal">@lang('Cancel')</button>
                        <button type="submit" class="btn btn--base btn--md">@lang('Submit')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal fade custom--modal" id="editRoleModal" tabindex="-1" aria-labelledby="NameEditModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        @lang('Update Role')
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <form action="{{ route('user.member.role.update', $user->uid) }}" method="post" id="profileForm">
                    <div class="modal-body">
                        @csrf
                        <div class="form-group">
                            <label class="form--label">@lang('Role')</label>
                            <select name="role" class="form--control select2 sm-style"
                                data-minimum-results-for-search="-1">
                                @if (auth()->user()->role == Status::ORGANIZER)
                                    <option value="{{ Status::ORGANIZER }}">Organizer</option>
                                @endif
                                <option value="{{ Status::MANAGER }}">MANAGER</option>
                                <option value="{{ Status::STAFF }}">Staff</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn--dark btn--md"
                            data-bs-dismiss="modal">@lang('Cancel')</button>
                        <button type="submit" class="btn btn--base btn--md">@lang('Submit')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal fade custom--modal" id="projectModal" tabindex="-1" aria-labelledby="NameEditModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Assigned Project</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <div class="modal-body pt-0">
                    @if ($user->ev)
                        <div class="member__modal__list py-3 mb-3">
                            <div class="nav nav-tabs add-tab-nav custom--tab-bar">
                                <button type="button"
                                    class="nav-link add-member-link active assignedProjects">@lang('Assigned Projects')</button>
                                <button type="button"
                                    class="nav-link add-member-link assignProject">@lang('Assign Existing Project')</button>
                                <button type="button"
                                    class="nav-link add-member-link createAndAssign">@lang('Create and Assign Project')</button>
                            </div>
                        </div>
                        <div id="assignedProjectSection">
                            <div class="project-show-list">
                                @forelse ($user->projects as $userProject)
                                    <span class="project-show-item">
                                        <x-user.project-thumb :project="$userProject" />
                                        <span class="project-close-btn confirmationBtn"
                                            data-question="Are you sure to remove this project?"
                                            data-action="{{ route('user.member.project.remove', [$user->uid, $userProject->uid]) }}"
                                            data-mode="remove">
                                            <i class="las la-times"></i>
                                        </span>
                                    </span>
                                @empty
                                    <div class="empty__analytics">
                                        <x-user.no-data title="No Projects Found" />
                                    </div>
                                @endforelse
                            </div>
                        </div>
                        <div id="assignProjectSection">
                            <form action="{{ route('user.member.project.add', $user->uid) }}" method="post">
                                @csrf
                                <div class="form-group">
                                    <label class="form--label">@lang('Select Project')</label>

                                    <div class="select2-wrapper">
                                        <select name="projects[]" class="select2 sm-style"
                                            data-minimum-results-for-search="-1" multiple>
                                            @foreach ($projects as $project)
                                                @if (!in_array($project->id, $user->projects->pluck('id')->toArray()))
                                                    <option value="{{ $project->uid }}">{{ $project->title }}
                                                    </option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </div>

                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn--dark btn--md"
                                        data-bs-dismiss="modal">@lang('Cancel')</button>
                                    <button type="submit" class="btn btn--base btn--md">@lang('Submit')</button>
                                </div>
                            </form>
                        </div>
                        <div id="createProjectSection" class="d-none">
                            <form action="{{ route('user.project.save') }}" method="POST"
                                enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="user_ids[]" value="{{ $user->uid }}">

                                <div class="form-group">
                                    <label for="project_title" class="form--label">@lang('Title')</label>
                                    <input id="project_title" class="form--control md-style" type="text"
                                        name="title" required>
                                </div>

                                <div class="form-group">
                                    <label for="project_icon" class="form--label">@lang('Icon')</label>
                                    <input id="project_icon" type="file" accept=".jpg, .jpeg, .png"
                                        class="form--control md-style" name="icon">
                                    <small class="text--base d-block">
                                        <i class="las la-info-circle"></i> @lang('Icon will be resized to')
                                        {{ getFilesize('project') }}px
                                    </small>
                                </div>

                                <div class="form-group">
                                    <label for="project_description" class="form--label">@lang('Description')</label>
                                    <textarea id="project_description" class="form--control md-style project-description-input" type="text"
                                        name="description" data-limit="255"></textarea>
                                    <small class="form-text text-muted text-end mt-1">
                                        <span class="description-char-remaining">255</span> @lang('characters remaining')
                                    </small>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn--dark btn--md"
                                        data-bs-dismiss="modal">@lang('Cancel')</button>
                                    <button type="submit" class="btn btn--base btn--md">@lang('Submit')</button>
                                </div>
                            </form>
                        </div>
                    @else
                        <div class="email-verification-wrapper text-center">
                            <div class="icon-area">
                                <i class="fa-solid fa-envelope-circle-check"></i>
                            </div>
                            <div class="content-area">
                                <h4>@lang('Email Verification Required')</h4>
                                <p class="mb-4">
                                    @lang('A confirmed email address is required of the member before you can assign a project him.')
                                </p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <x-confirmation-modal />
@endsection
@push('style-lib')
    <link rel="stylesheet" href="{{ asset(activeTemplate(true) . 'css/lightbox.min.css') }}">
@endpush

@push('script-lib')
    <script src="{{ asset(activeTemplate(true) . 'js/echarts.js') }}"></script>
    <script src="{{ asset(activeTemplate(true) . 'js/chart.js') }}"></script>
    <script src="{{ asset(activeTemplate(true) . 'js/lightbox.min.js') }}"></script>
@endpush

@push('style')
    <style>
        @media screen and (max-width: 991px) {
            .project_timing_container {
                height: 400px !important;
            }
        }

        .line-chart-skeleton-line {
            border-radius: 0px;
        }

        .line-chart-skeleton {
            border-bottom: 1px dotted hsl(var(--black)/0.1)
        }

        .line-chart-skeleton-line {
            width: 2%;
        }

        @media (max-width: 991px) {
            .member-view {
                flex-direction: column
            }
        }

        @media (max-width: 991px) {

            .card-body:has(.screenshot-item) div[class*="col"]:nth-child(5),
            .card-body:has(.screenshot-item) div[class*="col"]:nth-child(6) {
                display: block !important;
            }
        }

        .project_timing_container {
            height: 300px;
        }

        .member-top {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
        }

        @media screen and (max-width: 575px) {
            .member-top {
                flex-wrap: wrap;
            }
        }

        @media screen and (max-width: 767px) {
            .member-top .datepicker-wrapper {
                width: auto;
            }
        }

        .member-top__title:has(>span) {
            display: flex;
            align-items: flex-end;
            gap: 6px;
        }

        .member-view__info {
            min-width: fit-content;
            flex: 1 1 100%;
        }

        .member-view__info-title {
            white-space: nowrap;
        }

        .member-view__info-desc {
            display: -webkit-box;
            -webkit-line-clamp: 1;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .member-top__title span {
            font-size: 0.75em;
            font-weight: 500;
            margin-bottom: 2px;
            font-family: var(--body-font);
            color: hsl(var(--body-color));
        }

        .member-top .datepicker-inner {
            max-width: 280px;
            padding: 10.5px 16px;
            border: 1px solid hsl(var(--black)/0.1);
        }

        .member-top .datepicker-inner .icon {
            right: 8px;
        }

        .member-top .datepicker-inner .form--control.date-range {
            max-width: 100%;
        }

        .task-list {
            margin-inline: -16px;
        }

        .task-list-item {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 12px 24px;
            border-radius: 12px;
        }

        @media screen and (max-width: 575px) {
            .task-list-item {
                padding: 12px 16px;
            }
        }

        .task-list-item:hover,
        .task-list-item:focus {
            background-color: hsl(var(--dark)/0.02);
            border-color: hsl(var(--dark)/0.02) !important;
        }

        .task-list-item:not(:last-child) {
            margin-bottom: 0px;
        }

        .task-list-item__thumb {
            --size: 40px;
            width: var(--size);
            height: var(--size);
            border-radius: 6px;
            flex-shrink: 0;
            font-size: calc(var(--size) * 0.5)
        }

        img.task-list-item__thumb {
            display: block;
            object-fit: cover;
        }

        div.task-list-item__thumb {
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--color-text);
            background-color: var(--color-bg);
        }

        .task-list-item__title {
            font-size: 0.875rem;
            font-weight: 600;
            color: hsl(var(--heading-color)/0.8);
            display: -webkit-box;
            -webkit-line-clamp: 1;
            -webkit-box-orient: vertical;
            overflow: hidden;
            line-height: 110%;
            margin-bottom: 4px;
        }

        .task-list-item__task {
            font-size: 0.8125rem;
            flex-shrink: 0;
            color: hsl(var(--heading-color));
            font-weight: 400;
            display: block;
            display: -webkit-box;
            -webkit-line-clamp: 1;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .task-list-item__duration {
            font-size: 0.75rem;
            font-weight: 500;
            color: hsl(var(--black)/0.8);
            white-space: nowrap;
        }

        .task-list-item__content {
            flex-grow: 1;
        }

        .task-list-item__content-wrapper {
            flex-grow: 1;
        }

        .task-list-item__content-top {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 8px;
        }

        .task-list-item__content-bottom {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .task-list-item .progress {
            flex-grow: 1;
            height: 6px;
        }


        .email-verification-wrapper .icon-area i {
            font-size: 70px;
            width: 100px;
            color: hsl(var(--base));
            margin-bottom: 20px;
        }

        .email-verification-wrapper .content-area h4 {
            font-size: 30px;
            margin-bottom: 22px;
        }

        .member__modal__list {
            background-color: hsl(var(--white));
            position: sticky;
            top: 0px;
            z-index: 1;
        }

        .add-tab-nav.custom--tab-bar.nav-tabs {
            border-bottom: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px;
            background: #f1f1f1;
            border-radius: 999px;
        }

        .add-tab-nav.custom--tab-bar .nav-link {
            border: none;
            border-radius: 999px;
            background: transparent;
            color: #9ca3af;
            padding: 0.5rem 1.4rem;
            font-weight: 500;
            font-size: 0.9rem;
            position: relative;
            transition:
                background-color 0.18s ease,
                color 0.18s ease,
                box-shadow 0.18s ease,
                transform 0.12s ease;
            outline: none;
        }

        .add-tab-nav.custom--tab-bar .nav-link:hover {
            background: rgba(148, 163, 184, 0.16);
        }

        .add-tab-nav.custom--tab-bar .nav-link:hover {
            color: #000000 !important;
        }

        .add-tab-nav.custom--tab-bar .nav-link.active:hover {
            color: #f9fafb !important;
        }

        .add-tab-nav.custom--tab-bar .nav-link.active {
            color: #f9fafb;
            background: hsl(var(--base));
            transform: translateY(-1px);
        }

        .add-tab-nav.custom--tab-bar .nav-link.active::after {
            content: '';
            position: absolute;
            inset: -2px;
            border-radius: inherit;
            pointer-events: none;
            opacity: 0.7;
        }

        @media (max-width: 575.98px) {
            .add__email_row {
                border-top: 1px solid hsl(var(--black)/.04);
                margin-top: 15px;
            }

            .add-tab-nav.custom--tab-bar.nav-tabs {
                width: 100%;
                justify-content: space-between;
            }

            .add-tab-nav.custom--tab-bar .nav-link {
                flex: 1 1 0;
                text-align: center;
                padding-inline: 0.4rem;
            }
        }

        .activity-info {
            height: 100%;
            display: flex;
            flex-wrap: wrap;
        }

        .activity-info-item {
            flex: 1 1 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 16px;
            padding: 16px;
        }


        @media screen and (max-width: 1599px) {
            .activity-info-item {
                gap: 8px;
                padding: 16px 8px;
            }
        }

        @media screen and (max-width: 1399px) {
            .activity-info-item {
                flex-direction: column;
            }
        }

        .activity-info-item:not(:has(.skeleton-box)):hover,
        .activity-info-item:not(:has(.skeleton-box)):focus {
            background-color: hsl(var(--dark)/0.02);
            border-color: hsl(var(--dark)/0.02) !important;
        }

        .activity-info-item__icon {
            --size: 56px;
            width: var(--size);
            height: var(--size);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            border-radius: 12px;
        }

        @media screen and (max-width: 1599px) {
            .activity-info-item__icon {
                --size: 40px;
            }
        }

        .activity-info-item__icon svg {
            width: 50%;
            height: 50%;
        }

        .activity-info-item__content {
            flex-grow: 1;
        }

        @media screen and (max-width: 1399px) {
            .activity-info-item__content {
                width: 100%;
                text-align: center;
            }

            .activity-info-item__content:has(.skeleton-box) {
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
            }
        }

        @media screen and (max-width: 991px) {
            .activity-info-item__content {
                flex-grow: unset;
            }
        }

        .activity-info-item__label {
            color: hsl(var(--body-color));
            font-size: 0.875rem;
            font-weight: 500;
            margin-bottom: 0px;
            line-height: 100%;
            display: -webkit-box;
            -webkit-line-clamp: 1;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .activity-info-item__label.skeleton-box {
            width: 80px;
            height: 10px;
            border-radius: 6px;
        }

        @media screen and (max-width: 1599px) {
            .activity-info-item__label {
                font-size: 0.75rem;
            }
        }

        .activity-info-item__title {
            font-size: 1.25rem;
            line-height: 100%;
            margin-bottom: 8px;
        }

        .activity-info-item__title.skeleton-box {
            width: 60px;
            height: 20px;
            border-radius: 4px;
        }


        @media screen and (max-width: 1599px) {
            .activity-info-item__title {
                font-size: 1.125rem;
            }
        }

        .activity-info-item:nth-child(1),
        .activity-info-item:nth-child(3) {
            border-right: 1px solid #e1e3ea69;
        }

        .activity-info-item:nth-child(1),
        .activity-info-item:nth-child(2) {
            border-bottom: 1px solid #e1e3ea69;
        }

        .activity-info-item:nth-child(1) .activity-info-item__icon {
            color: hsl(var(--success));
            background-color: hsl(var(--success)/0.05);
        }

        .activity-info-item:nth-child(2) .activity-info-item__icon {
            color: hsl(var(--primary));
            background-color: hsl(var(--primary)/0.05);
        }

        .activity-info-item:nth-child(3) .activity-info-item__icon {
            color: hsl(var(--base));
            background-color: hsl(var(--base)/0.05);
        }

        .activity-info-item:nth-child(4) .activity-info-item__icon {
            color: hsl(var(--info));
            background-color: hsl(var(--info)/0.05);
        }

        .project-show-list {
            display: flex;
            flex-wrap: wrap;
            gap: 24px;
        }

        .member-form-top-action {
            text-align: left !important;
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            margin-top: 16px;
        }

        .project-show-item {
            padding: 10px;
            border-radius: 6px;
            border: 1px solid hsl(var(--black)/0.1);
            position: relative;
            display: block;
        }

        @media (max-width: 767px) {
            .project-show-item {
                flex-grow: 1;
            }
        }

        .project-show-item .project__title {
            color: hsl(var(--heading-color)) !important;
            font-weight: 500;
        }

        .project-show-item .project-thumb {
            --size: 24px;
        }

        .project-show-item>div {
            gap: 8px !important;
        }

        .project-close-btn {
            --size: 20px;
            position: absolute;
            height: var(--size);
            width: var(--size);
            border-radius: 50%;
            color: hsl(var(--white));
            border: 2px solid hsl(var(--white));
            background-color: #ff6467;
            top: calc(var(--size) * -0.5);
            right: calc(var(--size) * -0.5);
            display: grid;
            place-content: center;
            cursor: pointer;
            font-size: calc(var(--size) * 0.5);
        }

        .widget-card-main .widget-card {
            border-radius: 8px;
            background-color: hsl(var(--white));
            border: 0;
            -webkit-box-shadow: 0px 3px 5px hsl(var(--black)/0.05);
            box-shadow: 0px 3px 5px hsl(var(--black)/0.05);
        }

        @media (max-width: 1599px) {
            .widget-card-main .widget-card__body {
                padding: 16px;
            }
        }

        .widget-card-main .widget-card__icon {
            color: hsl(var(--base));
        }

        .widget-card-main .widget-card__count {
            color: hsl(var(--heading-color));
        }

        .widget-card-main .widget-card__title {
            color: hsl(var(--body-color));
            font-size: 0.9125rem;
            font-weight: 500;
        }

        .widget-card-main .widget-card__wrapper {
            margin-bottom: 20px;
            justify-content: space-between;
        }

        .overview-table thead tr th {
            background-color: hsl(var(--base));
            color: hsl(var(--white));
            border-right: 1px solid hsl(var(--white));
            font-weight: 600;
            text-align: center;
            padding: 12px;
            font-size: 0.875rem;
        }

        .overview-table tbody tr td {
            word-break: break-all;
            overflow-wrap: normal;
            white-space: normal;
            border-right: 1px solid hsl(var(--border-color));
            padding: 12px;
            text-align: center;
            font-size: 0.875rem;
        }

        .overview-table tbody tr td:has(.overview-col-name) {
            background-color: hsl(var(--black) / .1);
        }

        .overview-table tbody tr td .overview-col-name {
            width: 100px;
            text-align: center;
            color: hsl(var(--heading-color));
            font-weight: 500;
            margin: 0 auto;
            display: block;
        }

        .overview-table tbody tr td:not(:has(.overview-col-name, .overview-col-avg, .overview-col-abs)) {
            writing-mode: vertical-rl;
            text-orientation: mixed;
            font-weight: 500;
            color: hsl(var(--heading-color));
            transform: rotate(180deg)
        }

        .overview-table tbody tr td:has(.bg--muted) {
            background-color: #ffecec;
            border-color: hsl(var(--white));
        }

        .overview-table tbody tr td:has(.overview-col-avg, .overview-col-abs) {
            white-space: nowrap;
            font-weight: 500;
            color: hsl(var(--heading-color));
        }

        .overview-table tbody tr td:has(.overview-col-abs) {
            text-align: center;

        }

        .overview-table tbody tr:hover td {
            background-color: hsl(var(--black) / .02) !important;
        }

        .overview-table tbody tr:hover td:has(.overview-col-name) {
            background-color: hsl(var(--black) / .15) !important;
        }

        .overview-table tbody tr:hover td:has(.bg--muted) {
            background-color: #ffd9d9 !important;
        }

        .datepicker-month {
            font-size: 16px;
            font-weight: 600;
            color: hsl(var(--heading-color));
        }
    </style>
@endpush

@push('script')
    <script>
        (function($) {
            "use strict";
            $(document).on('change', '#track-able', function() {
                var $el = $(this);
                var action = $el.data('action');
                var intended = $el.is(':checked');
                if (!action) return;

                $el.prop('disabled', true);

                $.ajax({
                    url: action,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(resp) {
                        if (resp && resp.success) {
                            var STATUS_YES = "{{ Status::YES }}";
                            var enabled = resp.tracking_status == STATUS_YES;
                            $el.prop('checked', enabled);
                            notify('success', resp.message);
                        } else {
                            $el.prop('checked', !intended);
                            var msg = (resp && resp.message) ? resp.message :
                                'Failed to update tracking status';
                            notify('error', msg);
                        }
                    },
                    error: function(xhr) {
                        $el.prop('checked', !intended);
                        var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON
                            .message : 'Something went wrong';
                        notify('error', msg);
                    },
                    complete: function() {
                        $el.prop('disabled', false);
                    }
                });
            });

            $(document).on('change', '#member-connect', function() {
                var $el = $(this);
                var action = $el.data('action');
                var intended = $el.is(':checked');

                if (!action) return;

                $el.prop('disabled', true);

                $.ajax({
                    url: action,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(resp) {
                        if (resp && resp.success) {
                            var yesValue = "{{ Status::USER_ACTIVE }}";

                            var status = resp.member_status;

                            if (status == `{{ Status::YES }}`) {
                                $('#track-able').prop('disabled', false);
                            } else {
                                $('#track-able').prop('disabled', true).prop('checked', false);
                            }

                            var enabled = status == yesValue;
                            $el.prop('checked', enabled);
                            notify('success', resp.message);
                        } else {
                            $el.prop('checked', !intended);
                            var msg = resp?.message || 'Failed to update status';
                            notify('error', msg);
                        }
                    },
                    error: function(xhr) {
                        $el.prop('checked', !intended);
                        var msg = xhr.responseJSON?.message || 'Something went wrong';
                        notify('error', msg);
                    },
                    complete: function() {
                        $el.prop('disabled', false);
                    }
                });
            });

            let editPhoneModal = $('#editPhoneModal');

            $('.editPhoneBtn').on('click', function() {
                let phone = $(this).data('phone');
                editPhoneModal.find('input[name=phone]').val(phone);
                editPhoneModal.modal('show');
            });

            let editRoleModal = $('#editRoleModal');

            $('.editRoleBtn').on('click', function() {
                let role = $(this).data('role');
                editRoleModal.find('[name=role]').val(role).trigger('change');
                editRoleModal.modal('show');
            });

            let projectModal = $('#projectModal');
            let assignedProjectSection = $('#assignedProjectSection');
            let assignProjectSection = $('#assignProjectSection');
            let createProjectSection = $('#createProjectSection');
            let assignedProjectsTab = $('.assignedProjects');
            let assignProjectTab = $('.assignProject');
            let createProjectTab = $('.createAndAssign');
            const defaultDescriptionLimit = 255;

            function initProjectSelect2() {
                projectModal.find('.select2').each(function() {
                    const $select = $(this);

                    if (!$select.parent().hasClass('select2-wrapper')) {
                        $select.wrap('<div class="select2-wrapper"></div>');
                    }

                    const config = {
                        dropdownParent: $select.closest('.select2-wrapper'),
                    };

                    if ($select.data('select2')) {
                        $select.select2('destroy');
                    }

                    $select.select2(config);
                });
            }

            function updateDescriptionCounter($textarea) {
                const limit = parseInt($textarea.data('limit'), 10) || defaultDescriptionLimit;
                let value = $textarea.val() || '';

                if (value.length > limit) {
                    value = value.substring(0, limit);
                    $textarea.val(value);
                }

                $textarea.closest('.form-group').find('.description-char-remaining').text(limit - value.length);
            }

            function toggleProjectTab(tab = 'assign') {
                assignedProjectsTab.removeClass('active');
                assignProjectTab.removeClass('active');
                createProjectTab.removeClass('active');
                assignedProjectSection.addClass('d-none');
                assignProjectSection.addClass('d-none');
                createProjectSection.addClass('d-none');
                projectModal.find('.modal-dialog').removeClass('modal-dialog-scrollable');

                if (tab === 'create') {
                    createProjectTab.addClass('active');
                    createProjectSection.removeClass('d-none');
                    initProjectSelect2();
                    createProjectSection.find('.project-description-input').each(function() {
                        updateDescriptionCounter($(this));
                    });
                    projectModal.find('.modal-title').text('Create Project');
                } else if (tab == 'assigned') {
                    assignedProjectsTab.addClass('active');
                    assignedProjectSection.removeClass('d-none');
                    projectModal.find('.modal-title').text('Assigned Project');
                    projectModal.find('.modal-dialog').addClass('modal-dialog-scrollable');
                } else {
                    assignProjectTab.addClass('active');
                    assignProjectSection.removeClass('d-none');
                    projectModal.find('.modal-title').text('Assign New Project');
                }
            }

            projectModal.on('shown.bs.modal', function() {
                toggleProjectTab('assigned');
                initProjectSelect2();
            });

            projectModal.on('input', '.project-description-input', function() {
                updateDescriptionCounter($(this));
            });

            assignedProjectsTab.on('click', function() {
                toggleProjectTab('assigned');
            });

            assignProjectTab.on('click', function() {
                toggleProjectTab('assign');
            });


            createProjectTab.on('click', function() {
                toggleProjectTab('create');
            });

            $('.newProjectBtn').on('click', function() {
                toggleProjectTab('assigned');
                projectModal.modal('show');
            });

            initLazy();
            $(document).on("click", "[data-lightbox]", function() {
                let img = $(this).find("img.lazy");
                if (img.attr("data-src")) {
                    img.attr("src", img.attr("data-src"));
                    img.removeAttr("data-src");
                }
            });

            function renderTimeTrackingChart(values, labels) {
                renderBarChart({
                    elementId: "timeTrackingChart",
                    data: values || [],
                    colors: ["#ff6a00"],
                    xAxisData: labels || [],
                    isTime: true,
                    showLabels: false,
                    showTooltip: true,
                    showYaxis: false
                });
            }

            function renderActivityChart(values, labels) {
                renderDotLineChart({
                    elementId: 'TrackerVolumeChart',
                    data: values || [],
                    colors: ['#FF6000'],
                    xAxisData: labels || [],
                    unitLabel: '%',
                });
            }

            let legendBottom = "0px";
            updateLegendPosition(window.innerWidth);

            function updateLegendPosition(width) {
                if (width <= 991) {
                    legendBottom = "-8px";
                }
            }

            window.addEventListener('resize', () => {
                updateLegendPosition(window.innerWidth);
            });

            function renderTopProjectChart(datas) {
                console.log(legendBottom);

                renderPieChart({
                    elementId: "topTrackedProject",
                    data: datas,
                    labelSuffix: "Projects",
                    showValueName: 'hours',
                    legendBottom: legendBottom
                });

            }

            function renderSkeleton(show = true) {
                let topUsedApps = $('#topUsedApps');
                let topTrackedTask = $('#topTrackedTask');
                let timeTrackingChart = $('#timeTrackingChart');
                let screenshotsArea = $('#screenshotsArea');


                let TrackerVolumeChart = $('#TrackerVolumeChart');
                let topTrackedProject = $('#topTrackedProject');
                let totalTimeTracked = $('#totalTimeTracked');
                let averageActivity = $('#averageActivity');
                let totalProjects = $('#totalProjects');
                let performanceLevel = $('#performanceLevel');

                if (show) {
                    topUsedApps.html(`<div class="project-timer">
                        <ul class="project-timer-list">
                            @for ($i = 0; $i < 7; $i++)
                                <li class="project-timer-item style--two">
                                    <div class="project-timer-item-top flex-between">
                                        <span class="title skeleton-box"></span>
                                        <span class="duration skeleton-box"></span>
                                    </div>
                                    <div class="project-timer-item-bottom skeleton-box"></div>
                                </li>
                            @endfor
                        </ul>
                    </div>`);

                    topTrackedTask.html(`<div class="project-timer">
                        <ul class="project-timer-list">
                            @for ($i = 0; $i < 7; $i++)
                                <li class="project-timer-item style--two">
                                    <div class="project-timer-item-top flex-between">
                                        <span class="title skeleton-box"></span>
                                        <span class="duration skeleton-box"></span>
                                    </div>
                                    <div class="project-timer-item-bottom skeleton-box"></div>
                                </li>
                            @endfor
                        </ul>
                    </div>`);

                    timeTrackingChart.html(`
                    <div class="line-chart-skeleton">
                        @for ($i = 0; $i < 18; $i++)
                            <div class="line-chart-skeleton-line skeleton-box"></div>
                        @endfor
                    </div>
                    `);
                    $('.activity-info-skeleton-area').removeClass('d-none');
                    $('.activity-info-area').addClass('d-none');
                } else {
                    timeTrackingChart.find('.line-chart-skeleton').remove();
                    $('.activity-info-skeleton-area').addClass('d-none');
                    $('.activity-info-area').removeClass('d-none');
                }
            }

            function loadMemberSummary(date = "") {
                renderSkeleton();
                $.ajax({
                        url: "{{ route('user.member.summary') }}",
                        type: 'POST',
                        data: {
                            date,
                            _token: "{{ csrf_token() }}",
                            user_id: "{{ encrypt($user->id) }}"
                        }
                    })
                    .done(function(response) {
                        let data = response.data;
                        $('#topUsedApps').html(data.topUsedApps);
                        $('#topTrackedTask').html(data.topTasks);
                        $('#totalTimeTracked').text(data.totalWorkTime);
                        $('#averageActivity').text(data.activityPercent);
                        $('#totalProjects').text(data.totalProjects);
                        $('#performanceLevel').text(data.rank);
                        renderTimeTrackingChart(data.timingValues, data.labels);
                        renderActivityChart(data.activityValues, data.labels);
                        renderTopProjectChart(data.topProjects);
                    })
                    .always(function() {
                        renderSkeleton(false);
                    });
            }

            loadMemberSummary("{{ $dateRange ?? '' }}");

            $('#summaryFilterDate').on('date-filter:change', function(e, payload) {
                const date = payload?.value || '';
                loadMemberSummary(date);
            });

            $('.project-close-btn').on('click', function(e) {
                e.preventDefault();
                projectModal.modal('hide');

            });

            $('#confirmationModal').on('hidden.bs.modal', function() {
                projectModal.modal('show');
            });


        })(jQuery);
    </script>
@endpush

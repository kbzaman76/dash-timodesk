@extends('Template::layouts.master')
@section('content')
    <div class="row justify-content-center">
        <div class="col-xxl-10">
            <div class="widget-card-main mb-4">
                <div class="row g-3 g-md-4">
                    <div class="col-xxl-3 col-sm-6">
                        <div class="widget-card">
                            <div class="widget-card__body">
                                <div class="widget-card__wrapper">
                                    <div class="widget-card__icon">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" class="injected-svg" data-src="https://cdn.hugeicons.com/icons/loading-01-solid-standard.svg?v=1.0.1" xmlns:xlink="http://www.w3.org/1999/xlink" role="img" color="currentColor">
                                            <path fill-rule="evenodd" clip-rule="evenodd"
                                                d="M18 1.25C19.5188 1.25 20.75 2.48122 20.75 4V4.23047C20.75 5.5687 20.1855 6.84489 19.1953 7.74512L15.7441 10.8818C15.4292 11.1681 15.25 11.5744 15.25 12C15.25 12.4256 15.4292 12.8319 15.7441 13.1182L19.1953 16.2549C20.1855 17.1551 20.75 18.4313 20.75 19.7695V20C20.75 21.5188 19.5188 22.75 18 22.75H6C4.48122 22.75 3.25 21.5188 3.25 20V19.7695C3.25 18.4313 3.81451 17.1551 4.80469 16.2549L8.25586 13.1182C8.57077 12.8319 8.75 12.4256 8.75 12C8.75 11.5744 8.57077 11.1681 8.25586 10.8818L4.80469 7.74512C3.81451 6.84489 3.25 5.5687 3.25 4.23047V4C3.25 2.48122 4.48122 1.25 6 1.25H18ZM11.8154 17.2559C11.3751 17.2853 10.9939 17.4406 10.6162 17.6562C10.2526 17.864 9.83914 18.1603 9.34863 18.5107C9.0423 18.7296 8.69345 18.9598 8.48633 19.3145C8.45654 19.3655 8.42873 19.4188 8.4043 19.4727C8.27683 19.7541 8.25355 20.0594 8.25 20.3516V20.7959H15.75V20.3516C15.7464 20.0594 15.7232 19.7541 15.5957 19.4727C15.5713 19.4188 15.5435 19.3655 15.5137 19.3145C15.3065 18.9598 14.9577 18.7296 14.6514 18.5107L14.6221 18.4902C14.1443 18.1489 13.7402 17.8598 13.3838 17.6562C13.0061 17.4406 12.6249 17.2853 12.1846 17.2559C12.0617 17.2477 11.9383 17.2477 11.8154 17.2559Z"
                                                fill="currentColor"></path>
                                        </svg>
                                    </div>
                                    <p class="widget-card__count sm-size">
                                        {{ formatSecondsToHoursMinutes($totalTracks) }} hrs</p>
                                </div>
                                <p class="widget-card__title">Total Logged Hours</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-xxl-3 col-sm-6">
                        <div class="widget-card">
                            <div class="widget-card__body">
                                <div class="widget-card__wrapper">
                                    <div class="widget-card__icon">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" class="injected-svg" data-src="https://cdn.hugeicons.com/icons/timer-02-solid-standard.svg?v=1.0.1" xmlns:xlink="http://www.w3.org/1999/xlink" role="img" color="currentColor">
                                            <path
                                                d="M10.25 20.75C10.8023 20.75 11.25 21.1977 11.25 21.75C11.25 22.3023 10.8023 22.75 10.25 22.75H3.25C2.69772 22.75 2.25 22.3023 2.25 21.75C2.25 21.1977 2.69772 20.75 3.25 20.75H10.25ZM12.5 4.25C14.7033 4.25 16.7256 5.02159 18.3145 6.30762L19.1143 5.50879C19.4945 5.12864 20.111 5.12858 20.4912 5.50879C20.8714 5.889 20.8714 6.50549 20.4912 6.88574L19.6914 7.68457C20.978 9.27354 21.75 11.2962 21.75 13.5C21.75 18.6086 17.6086 22.75 12.5 22.75C12.4216 22.75 12.3435 22.7461 12.2656 22.7441C12.4139 22.444 12.5 22.1074 12.5 21.75C12.5 20.5464 11.5548 19.5664 10.3662 19.5059C10.4507 19.2693 10.5 19.0156 10.5 18.75C10.5 17.5464 9.55477 16.5664 8.36621 16.5059C8.45072 16.2693 8.5 16.0156 8.5 15.75C8.5 14.5074 7.49264 13.5 6.25 13.5H3.25C3.25 8.39137 7.39137 4.25 12.5 4.25ZM8.25 17.75C8.80228 17.75 9.25 18.1977 9.25 18.75C9.25 19.3023 8.80228 19.75 8.25 19.75H3.25C2.69772 19.75 2.25 19.3023 2.25 18.75C2.25 18.1977 2.69772 17.75 3.25 17.75H8.25ZM6.25 14.75C6.80228 14.75 7.25 15.1977 7.25 15.75C7.25 16.3023 6.80228 16.75 6.25 16.75H3.25C2.69772 16.75 2.25 16.3023 2.25 15.75C2.25 15.1977 2.69772 14.75 3.25 14.75H6.25ZM16.707 9.29297C16.3409 8.92685 15.7619 8.90426 15.3691 9.22461L15.293 9.29297L11.793 12.793C11.4026 13.1835 11.4025 13.8165 11.793 14.207C12.1835 14.5974 12.8165 14.5974 13.207 14.207L16.707 10.707L16.7754 10.6309C17.0957 10.2381 17.073 9.65909 16.707 9.29297ZM15 1.25C15.5523 1.25 16 1.69772 16 2.25C16 2.80228 15.5523 3.25 15 3.25H10C9.44772 3.25 9 2.80228 9 2.25C9 1.69772 9.44772 1.25 10 1.25H15Z"
                                                fill="currentColor"></path>
                                        </svg>
                                    </div>
                                    <p class="widget-card__count sm-size">{{ formatSecondsToHoursMinutes($avgDailySeconds) }} hrs</p>
                                </div>
                                <p class="widget-card__title">Average Daily Hours</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-xxl-3 col-sm-6">
                        <div class="widget-card">
                            <div class="widget-card__body">
                                <div class="widget-card__wrapper">
                                    <div class="widget-card__icon">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" class="injected-svg" data-src="https://cdn.hugeicons.com/icons/structure-check-solid-sharp.svg?v=1.0.1" xmlns:xlink="http://www.w3.org/1999/xlink" role="img" color="currentColor">
                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M6.00161 3.99955C6.00185 3.44744 6.4495 3 7.00161 3H12.0003V5H8.00116L8.00026 7.00045L6.00026 6.99955L6.00161 3.99955ZM8.00009 19L8.00026 17.0001L6.00026 16.9999L6 19.9999C5.99998 20.2651 6.10532 20.5195 6.29286 20.7071C6.4804 20.8946 6.73477 21 7 21H12.0003V19H8.00009Z" fill="currentColor"></path>
                                            <path d="M14.25 16C14.25 15.5858 14.5858 15.25 15 15.25H22C22.4142 15.25 22.75 15.5858 22.75 16V22C22.75 22.4142 22.4142 22.75 22 22.75H15C14.5858 22.75 14.25 22.4142 14.25 22V16Z" fill="currentColor"></path>
                                            <path d="M14.25 2C14.25 1.58579 14.5858 1.25 15 1.25H22C22.4142 1.25 22.75 1.58579 22.75 2V8C22.75 8.41421 22.4142 8.75 22 8.75H15C14.5858 8.75 14.25 8.41421 14.25 8V2Z" fill="currentColor"></path>
                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M7 6.25C3.82436 6.25 1.25 8.82436 1.25 12C1.25 15.1756 3.82436 17.75 7 17.75C10.1756 17.75 12.75 15.1756 12.75 12C12.75 8.82436 10.1756 6.25 7 6.25ZM6.5608 14.5438L9.77186 10.6347L8.22641 9.36523L6.32636 11.6783L5.48067 10.9368L4.16211 12.4406L6.5608 14.5438Z" fill="currentColor"></path>
                                        </svg>
                                    </div>
                                    <p class="widget-card__count sm-size">{{ $user->projects_count }} Projects</p>
                                </div>
                                <p class="widget-card__title">Assigned Projects</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-xxl-3 col-sm-6">
                        <div class="widget-card">
                            <div class="widget-card__body">
                                <div class="widget-card__wrapper">
                                    <div class="widget-card__icon">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" class="injected-svg" data-src="https://cdn.hugeicons.com/icons/task-02-solid-rounded.svg?v=1.0.1" xmlns:xlink="http://www.w3.org/1999/xlink" role="img" color="currentColor">
                                            <path
                                                d="M16.25 1.27628C16.6686 1.29306 17.0485 1.32098 17.3916 1.3671C18.2917 1.48811 19.0494 1.7476 19.6514 2.34952C20.2533 2.95143 20.5128 3.70918 20.6338 4.60928C20.7501 5.4742 20.75 6.57337 20.75 7.93643L20.75 16.0653C20.75 17.4284 20.7501 18.5276 20.6338 19.3925C20.5128 20.2926 20.2533 21.0503 19.6514 21.6523C19.0494 22.2542 18.2917 22.5137 17.3916 22.6347C16.5267 22.7509 15.4275 22.7509 14.0645 22.7509L9.93555 22.7509C8.57249 22.7509 7.47331 22.7509 6.6084 22.6347C5.70829 22.5137 4.95055 22.2542 4.34863 21.6523C3.74672 21.0503 3.48723 20.2926 3.36621 19.3925C3.24995 18.5276 3.24997 17.4284 3.25 16.0653L3.25 7.93643C3.24997 6.57337 3.24995 5.4742 3.36621 4.60928C3.48723 3.70918 3.74672 2.95143 4.34863 2.34952C4.95055 1.7476 5.70829 1.48811 6.6084 1.3671C6.95146 1.32098 7.33138 1.29306 7.75 1.27628V2.00089C7.75 2.68679 7.74833 3.27692 7.81152 3.74698C7.87764 4.23853 8.027 4.70952 8.40918 5.09171C8.79136 5.47389 9.26236 5.62325 9.75391 5.68936C10.224 5.75256 10.8141 5.75089 11.5 5.75089L12.5 5.75089C13.1859 5.75089 13.776 5.75256 14.2461 5.68936C14.7376 5.62325 15.2086 5.47389 15.5908 5.09171C15.973 4.70952 16.1224 4.23853 16.1885 3.74698C16.2517 3.27692 16.25 2.68679 16.25 2.00089V1.27628ZM8 14.2509C7.58579 14.2509 7.25 14.5867 7.25 15.0009C7.25 15.4151 7.58579 15.7509 8 15.7509H12C12.4142 15.7509 12.75 15.4151 12.75 15.0009C12.75 14.5867 12.4142 14.2509 12 14.2509H8ZM8 10.2509C7.58579 10.2509 7.25 10.5867 7.25 11.0009C7.25 11.4151 7.58579 11.7509 8 11.7509L16 11.7509C16.4142 11.7509 16.75 11.4151 16.75 11.0009C16.75 10.5867 16.4142 10.2509 16 10.2509L8 10.2509ZM14.75 1.25186V2.00089C14.75 2.72911 14.7488 3.19992 14.7021 3.54678C14.6584 3.87209 14.5874 3.97407 14.5303 4.03116C14.4732 4.08825 14.3712 4.1593 14.0459 4.20303C13.699 4.24965 13.2282 4.25089 12.5 4.25089H11.5C10.7718 4.25089 10.301 4.24965 9.9541 4.20303C9.6288 4.1593 9.52682 4.08825 9.46973 4.03116C9.41263 3.97406 9.34159 3.87208 9.29785 3.54678C9.25123 3.19992 9.25 2.72911 9.25 2.00089V1.25186C9.47068 1.25126 9.69915 1.25088 9.93555 1.25089L14.0645 1.25089C14.3009 1.25088 14.5293 1.25126 14.75 1.25186Z"
                                                fill="currentColor"></path>
                                        </svg>
                                    </div>
                                    <p class="widget-card__count sm-size">{{ $user->tasks_count }} Tasks</p>
                                </div>
                                <p class="widget-card__title">Assigned Tasks</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card custom--card mb-4">
                <div class="card-body">
                    <div class="member-form-top">
                        <div class="member-view">
                            <img class="member-view__img" src="{{ $user->image_url }}" alt="member-view" />
                            <div class="member-form-align">
                                <h5 class="member-view__name mb-3">{{ toTitle($user->fullname) }}</h5>
                                <div class="member-view__content">
                                    <div class="member-view__info-wrapper">
                                        <div class="member-view__info">
                                            <div class="member-view__info-title d-flex align-items-center justify-content-between gap-2">
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="icon">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" color="currentColor">
                                                            <path d="M7 8.5L9.94202 10.2394C11.6572 11.2535 12.3428 11.2535 14.058 10.2394L17 8.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                                            <path
                                                                d="M2.01576 13.4756C2.08114 16.5411 2.11382 18.0739 3.24495 19.2093C4.37608 20.3448 5.95033 20.3843 9.09883 20.4634C11.0393 20.5122 12.9607 20.5122 14.9012 20.4634C18.0497 20.3843 19.6239 20.3448 20.755 19.2093C21.8862 18.0739 21.9189 16.5411 21.9842 13.4756C22.0053 12.4899 22.0053 11.51 21.9842 10.5244C21.9189 7.45883 21.8862 5.92606 20.755 4.79063C19.6239 3.6552 18.0497 3.61565 14.9012 3.53654C12.9607 3.48778 11.0393 3.48778 9.09882 3.53653C5.95033 3.61563 4.37608 3.65518 3.24495 4.79062C2.11382 5.92605 2.08113 7.45882 2.01576 10.5243C1.99474 11.51 1.99474 12.4899 2.01576 13.4756Z"
                                                                stroke="currentColor" stroke-width="2" stroke-linejoin="round">
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
                                        <div class="member-view__info">
                                            <div class="member-view__info-title">
                                                <div class="flex-align gap-2">
                                                    <span class="icon">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" color="currentColor">
                                                            <path d="M16.5 2H7.5C6.39543 2 5.5 2.89543 5.5 4V20C5.5 21.1046 6.39543 22 7.5 22H16.5C17.6046 22 18.5 21.1046 18.5 20V4C18.5 2.89543 17.6046 2 16.5 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                                            <path d="M12 19H12.01" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                                        </svg>
                                                    </span>
                                                    <span class="text">Phone</span>
                                                </div>
                                                <button type="button" class="member-view__info-edit editPhoneBtn" data-phone="{{ $user->mobile }}">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" color="currentColor">
                                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M18.7116 3.40901C17.833 2.53033 16.4083 2.53033 15.5296 3.40901L13.4997 5.43906L18.5604 10.4997L20.5903 8.46965C21.469 7.59098 21.469 6.16637 20.5903 5.28769L18.7116 3.40901ZM17.4997 11.5604L12.4391 6.49975L3.40899 15.5303C2.98705 15.9523 2.75 16.5246 2.75 17.1213V20.5C2.75 20.9142 3.08579 21.25 3.5 21.25H6.87868C7.47542 21.25 8.04773 21.0129 8.46969 20.591L17.4997 11.5604Z"
                                                            fill="currentColor"></path>
                                                    </svg>
                                                </button>
                                            </div>
                                            <div class="member-view__info-desc">{{ $user->mobile ?? 'N/A' }}</div>
                                        </div>
                                        <div class="member-view__info">
                                            <div class="member-view__info-title">
                                                <div class="flex-align gap-2">
                                                    <span class="icon">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" color="currentColor">
                                                            <path d="M11.5 3.99973L16.4998 3.99923C17.6044 3.99911 18.5 4.89458 18.5 5.99922V9.08714C18.5 9.31462 18.3156 9.49902 18.0881 9.49902C18.0056 9.49902 17.9251 9.47426 17.8568 9.42793L16.3462 8.40255" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                                            <path d="M12 20H7C5.89543 20 5 19.1046 5 18V14.9109C5 14.684 5.18399 14.5 5.41095 14.5C5.494 14.5 5.57511 14.5252 5.64358 14.5722L7.15385 15.6093" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
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
                                                    <button type="button" class="member-view__info-edit " data-role="{{ $user->role }}">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" color="currentColor">
                                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M18.7116 3.40901C17.833 2.53033 16.4083 2.53033 15.5296 3.40901L13.4997 5.43906L18.5604 10.4997L20.5903 8.46965C21.469 7.59098 21.469 6.16637 20.5903 5.28769L18.7116 3.40901ZM17.4997 11.5604L12.4391 6.49975L3.40899 15.5303C2.98705 15.9523 2.75 16.5246 2.75 17.1213V20.5C2.75 20.9142 3.08579 21.25 3.5 21.25H6.87868C7.47542 21.25 8.04773 21.0129 8.46969 20.591L17.4997 11.5604Z"
                                                                fill="currentColor"></path>
                                                        </svg>
                                                    </button>
                                                @endif
                                            </div>
                                            <div class="member-view__info-desc">{{ $user->getRole() ?? 'N/A' }}</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="member-form-top-action">
                                    <div class="form--switch">
                                        @if ($user->status != Status::USER_PENDING && $user->ev == Status::YES)
                                            <input type="checkbox" id="track-able" class="form-check-input" data-action="{{ route('user.member.tracking.status', $user->id) }}" @checked($user->tracking_status) @disabled($user->status == Status::USER_BAN) />
                                            <label for="track-able" class="form-check-label fs-15 fw-medium" @disabled($user->status == Status::USER_BAN)>Able to track
                                                time</label>
                                        @else
                                            <div>
                                                <input type="checkbox" id="track-able" class="form-check-input" disabled />
                                                <label for="track-able" class="form-check-label fs-15 fw-medium" disabled>
                                                    Able to track time
                                                </label>
                                                <p>
                                                    <small class="text--primary">
                                                        <em><i class="las la-info-circle"></i> You'll be able to enable
                                                            tracking for the member after he/she verify the email.</em>
                                                    </small>
                                                </p>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="form--switch">
                                        @if ($user->status != Status::USER_PENDING)
                                            <input type="checkbox" id="member-connect" class="form-check-input" data-action="{{ route('user.member.status', $user->id) }}" @checked($user->status == Status::USER_ACTIVE) @disabled(isEditDisabled($user) ? true : false) />
                                            <label for="member-connect" class="form-check-label fs-15 fw-medium" @disabled(isEditDisabled($user) ? true : false)>Member Status</label>
                                        @else
                                            <div>
                                                <input type="checkbox" id="disalbe-member" class="form-check-input" disabled />
                                                <label for="disalbe-member" class="form-check-label fs-15 fw-medium" disabled>Member Status</label>
                                                <p>
                                                    <small class="text--primary">
                                                        <em><i class="las la-info-circle"></i> You’ll be able to enable member after the member is approved.</em>
                                                    </small>
                                                </p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card custom--card">
                <div class="card-header flex-between gap-3">
                    <h5 class="card-title">
                        Assigned Projects
                    </h5>
                    <button type="button" class="btn btn--sm btn--base newProjectBtn">
                        <span class="icon"><i class="las la-plus"></i></span>
                        <span class="text">New Project</span>
                    </button>
                </div>
                <div class="card-body">
                    <div class="project-show-list">
                        @forelse ($user->projects as $userProject)
                            <span class="project-show-item">
                                <x-user.project-thumb :project="$userProject" />
                                <span class="project-close-btn confirmationBtn" data-question="Are you sure to remove this project?" data-action="{{ route('user.member.project.remove', [$user->uid, $userProject->id]) }}" data-mode="remove">
                                    <i class="las la-times"></i>
                                </span>
                            </span>
                        @empty
                            <x-user.no-data title="No projects found" />
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="modal fade custom--modal" id="editPhoneModal" tabindex="-1" aria-labelledby="NameEditModalLabel" aria-hidden="true">
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
                            <input type="text" name="phone" class="form--control md-style" maxlength="40" required />
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn--dark btn--md" data-bs-dismiss="modal">@lang('Cancel')</button>
                        <button type="submit" class="btn btn--base btn--md">@lang('Submit')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade custom--modal" id="editRoleModal" tabindex="-1" aria-labelledby="NameEditModalLabel" aria-hidden="true">
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
                            <select name="role" class="form--control select2 sm-style" data-minimum-results-for-search="-1">
                                @if (auth()->user()->role == Status::ORGANIZER)
                                    <option value="{{ Status::ORGANIZER }}">Organizer</option>
                                @endif
                                <option value="{{ Status::MANAGER }}">MANAGER</option>
                                <option value="{{ Status::STAFF }}">Staff</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn--dark btn--md" data-bs-dismiss="modal">@lang('Cancel')</button>
                        <button type="submit" class="btn btn--base btn--md">@lang('Submit')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade custom--modal" id="projectModal" tabindex="-1" aria-labelledby="NameEditModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        @lang('New Project')
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <form action="{{ route('user.member.project.add', $user->uid) }}" method="post" id="profileForm">
                    <div class="modal-body">
                        @csrf
                        <div class="form-group">
                            <label class="form--label">@lang('Project')</label>

                            <div class="select2-wrapper">
                                <select name="projects[]" class="select2 sm-style" data-minimum-results-for-search="-1" multiple>
                                    @foreach ($projects as $project)
                                        @if (!in_array($project->id, $user->projects->pluck('id')->toArray()))
                                            <option value="{{ $project->id }}">{{ $project->title }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>

                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn--dark btn--md" data-bs-dismiss="modal">@lang('Cancel')</button>
                        <button type="submit" class="btn btn--base btn--md">@lang('Submit')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <x-confirmation-modal />
@endsection

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

            $('.newProjectBtn').on('click', function() {
                projectModal.modal('show');
            });



        })(jQuery);
    </script>
@endpush

@push('style')
    <style>
        .project-show-list {
            display: flex;
            flex-wrap: wrap;
            gap: 24px;
        }

        .member-form-top-action {
            text-align: right;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .project-show-item {
            padding: 6px 12px;
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
            --size: 32px;
        }

        .project-close-btn {
            position: absolute;
            height: 22px;
            width: 22px;
            border-radius: 50%;
            background-color: hsl(var(--danger) / .1);
            color: hsl(var(--danger));
            top: -10px;
            right: -10px;
            display: grid;
            place-content: center;
            cursor: pointer;
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

<div class="sidebar-menu" id="sidebar__menuWrapper">
    <button class="sidebar-menu__close">
        <i class="las la-times"></i>
    </button>
    <div class="sidebar-menu-top">
        <a href="{{ route('user.home') }}" class="sidebar-menu-logo">
            <img src="{{ siteLogo() }}" alt="@lang('logo')" />
        </a>

        <ul class="sidebar-menu-list">
            <li class="sidebar-menu-list__item {{ menuActive('user.home') }}">
                <a href="{{ route('user.home') }}" class="sidebar-menu-list__link">
                    <span class="icon">
                        <x-icons.dashboard />
                    </span>
                    <span class="text">@lang('Dashboard')</span>
                </a>
            </li>
            <li class="sidebar-menu-list__item {{ menuActive('user.project.*') }}">
                <a href="{{ route('user.project.list') }}" class="sidebar-menu-list__link">
                    <span class="icon">
                        <x-icons.project />
                    </span>
                    <span class="text">@lang('Projects')</span>
                </a>
            </li>
            @role('manager|organizer')
                <li class="sidebar-menu-list__item {{ menuActive('user.member*') }}">
                    <a href="{{ route('user.member.list') }}" class="sidebar-menu-list__link">
                        <span class="icon">
                            <x-icons.members />
                        </span>
                        <span class="text">@lang('Members')</span>
                        @if ($pendingMemberCount)
                            <span class="menu-alert"></span>
                        @endif
                    </a>
                </li>
            @endrole


            <li class="sidebar-menu-list__title mt-4">@lang('Analytics')</li>
            <li class="sidebar-menu-list__item {{ menuActive('user.activity.screenshot.*') }}">
                <a href="{{ route('user.activity.screenshot.index') }}" class="sidebar-menu-list__link">
                    <span class="icon">
                        <x-icons.screenshot />
                    </span>
                    <span class="text">@lang('Screenshots')</span>
                </a>
            </li>

            <li class="sidebar-menu-list__item {{ menuActive('user.time.calender') }}">
                <a href="{{ route('user.time.calender') }}" class="sidebar-menu-list__link">
                    <span class="icon">
                        <x-icons.monthly-calender />
                    </span>
                    <span class="text">@lang('Time Calender')</span>
                </a>
            </li>

            <li class="sidebar-menu-list__item {{ menuActive('user.time.weekly.worklog') }}">
                <a href="{{ route('user.time.weekly.worklog') }}" class="sidebar-menu-list__link">
                    <span class="icon">
                        <x-icons.worklog />
                    </span>
                    <span class="text">@lang('Weekly Worklog')</span>
                </a>
            </li>

            <li class="sidebar-menu-list__item {{ menuActive('user.report.time.activity.index') }}">
                <a href="{{ route('user.report.time.activity.index') }}" class="sidebar-menu-list__link">
                    <span class="icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" class="lucide lucide-calendar-clock-icon lucide-calendar-clock">
                            <path d="M16 14v2.2l1.6 1" />
                            <path d="M16 2v4" />
                            <path d="M21 7.5V6a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h3.5" />
                            <path d="M3 10h5" />
                            <path d="M8 2v4" />
                            <circle cx="16" cy="16" r="6" />
                        </svg>
                    </span>
                    <span class="text">@lang('Time & Activity')</span>
                </a>
            </li>

            <li class="sidebar-menu-list__item {{ menuActive('user.report.time.analytics') }}">
                <a href="{{ route('user.report.time.analytics') }}" class="sidebar-menu-list__link">
                    <span class="icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" class="lucide lucide-calendar-days-icon lucide-calendar-days">
                            <path d="M8 2v4" />
                            <path d="M16 2v4" />
                            <rect width="18" height="18" x="3" y="4" rx="2" />
                            <path d="M3 10h18" />
                            <path d="M8 14h.01" />
                            <path d="M12 14h.01" />
                            <path d="M16 14h.01" />
                            <path d="M8 18h.01" />
                            <path d="M12 18h.01" />
                            <path d="M16 18h.01" />
                        </svg>
                    </span>
                    <span class="text">@lang('Time Analytics')</span>
                </a>
            </li>


            <li class="sidebar-menu-list__item {{ menuActive('user.report.app.usage') }}">
                <a href="{{ route('user.report.app.usage') }}" class="sidebar-menu-list__link">
                    <span class="icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" class="lucide lucide-monitor-cloud-icon lucide-monitor-cloud">
                            <path d="M11 13a3 3 0 1 1 2.83-4H14a2 2 0 0 1 0 4z" />
                            <path d="M12 17v4" />
                            <path d="M8 21h8" />
                            <rect x="2" y="3" width="20" height="14" rx="2" />
                        </svg>

                    </span>
                    <span class="text">@lang('App Usage')</span>

                </a>
            </li>

            <li class="sidebar-menu-list__item {{ menuActive('user.report.app.analytics') }}">
                <a href="{{ route('user.report.app.analytics') }}" class="sidebar-menu-list__link">
                    <span class="icon">
                        <x-icons.pie-chart />
                    </span>
                    <span class="text">@lang('App Analytics')</span>
                </a>
            </li>

            <li class="sidebar-menu-list__item {{ menuActive('user.report.project.timing') }}">
                <a href="{{ route('user.report.project.timing') }}" class="sidebar-menu-list__link">
                    <span class="icon">
                        <x-icons.hourglass />
                    </span>
                    <span class="text">@lang('Project Timing')</span>
                </a>
            </li>

            @role('manager|organizer')
                <li class="sidebar-menu-list__item {{ menuActive('user.report.monthly.time.sheet') }}">
                    <a href="{{ route('user.report.monthly.time.sheet') }}" class="sidebar-menu-list__link">
                        <span class="icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-calendar-days-icon lucide-calendar-days">
                                <path d="M8 2v4" />
                                <path d="M16 2v4" />
                                <rect width="18" height="18" x="3" y="4" rx="2" />
                                <path d="M3 10h18" />
                                <path d="M8 14h.01" />
                                <path d="M12 14h.01" />
                                <path d="M16 14h.01" />
                                <path d="M8 18h.01" />
                                <path d="M12 18h.01" />
                                <path d="M16 18h.01" />
                            </svg>
                        </span>
                        <span class="text">@lang('Monthly Timesheet')</span>
                    </a>
                </li>
            @endrole

            @role('manager|organizer')
                <li class="sidebar-menu-list__title mt-4">@lang('Performance')</li>
                <li class="sidebar-menu-list__item {{ menuActive('user.performer.top') }}">
                    <a href="{{ route('user.performer.top') }}" class="sidebar-menu-list__link">
                        <span class="icon">
                            <x-icons.medal />
                        </span>
                        <span class="text">@lang('Top Performers')</span>
                    </a>
                </li>

                <li class="sidebar-menu-list__item {{ menuActive('user.performer.low') }}">
                    <a href="{{ route('user.performer.low') }}" class="sidebar-menu-list__link">
                        <span class="icon">
                            <x-icons.trade-down />
                        </span>
                        <span class="text">@lang('Low Performers')</span>
                    </a>
                </li>

                <li class="sidebar-menu-list__item {{ menuActive('user.performer.leaderboard') }}">
                    <a href="{{ route('user.performer.leaderboard') }}" class="sidebar-menu-list__link">
                        <span class="icon">
                            <x-icons.chart01 />
                        </span>
                        <span class="text">@lang('Productivity Leaders')</span>
                    </a>
                </li>
            @endrole


            @role('organizer')
                <li class="sidebar-menu-list__title mt-4">@lang('Billing')</li>
                {{-- <li class="sidebar-menu-list__item {{ menuActive('user.billing.overview') }}">
                    <a href="{{ route('user.billing.overview') }}" class="sidebar-menu-list__link">
                        <span class="icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-calendar-days-icon lucide-calendar-days">
                                <path d="M8 2v4" />
                                <path d="M16 2v4" />
                                <rect width="18" height="18" x="3" y="4" rx="2" />
                                <path d="M3 10h18" />
                                <path d="M8 14h.01" />
                                <path d="M12 14h.01" />
                                <path d="M16 14h.01" />
                                <path d="M8 18h.01" />
                                <path d="M12 18h.01" />
                                <path d="M16 18h.01" />
                            </svg>
                        </span>
                        <span class="text">@lang('Overview')</span>
                    </a>
                </li> --}}
                <li class="sidebar-menu-list__item {{ menuActive('user.invoice*') }}">
                    <a href="{{ route('user.invoice.list') }}" class="sidebar-menu-list__link">
                        <span class="icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-book-check-icon lucide-book-check">
                                <path
                                    d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H19a1 1 0 0 1 1 1v18a1 1 0 0 1-1 1H6.5a1 1 0 0 1 0-5H20" />
                                <path d="m9 9.5 2 2 4-4" />
                            </svg>
                        </span>
                        <span class="text">@lang('Invoices')</span>
                    </a>
                </li>
                <li class="sidebar-menu-list__item {{ menuActive('user.deposit.history') }}">
                    <a href="{{ route('user.deposit.history') }}" class="sidebar-menu-list__link">
                        <span class="icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-folder-clock-icon lucide-folder-clock">
                                <path d="M16 14v2.2l1.6 1" />
                                <path
                                    d="M7 20H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h3.9a2 2 0 0 1 1.69.9l.81 1.2a2 2 0 0 0 1.67.9H20a2 2 0 0 1 2 2" />
                                <circle cx="16" cy="16" r="6" />
                            </svg>
                        </span>
                        <span class="text">@lang('Deposits')</span>
                    </a>
                </li>
                <li class="sidebar-menu-list__item {{ menuActive('user.transactions') }}">
                    <a href="{{ route('user.transactions') }}" class="sidebar-menu-list__link">
                        <span class="icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round"
                                class="lucide lucide-badge-dollar-sign-icon lucide-badge-dollar-sign">
                                <path
                                    d="M3.85 8.62a4 4 0 0 1 4.78-4.77 4 4 0 0 1 6.74 0 4 4 0 0 1 4.78 4.78 4 4 0 0 1 0 6.74 4 4 0 0 1-4.77 4.78 4 4 0 0 1-6.75 0 4 4 0 0 1-4.78-4.77 4 4 0 0 1 0-6.76Z" />
                                <path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8" />
                                <path d="M12 18V6" />
                            </svg>
                        </span>
                        <span class="text">@lang('Transactions')</span>
                    </a>
                </li>
            @endrole

            {{-- setting route --}}
            <li class="sidebar-menu-list__title mt-4">@lang('SETTINGS')</li>
            @role('organizer')
                <li class="sidebar-menu-list__item {{ menuActive('user.setting.storage.list') }}">
                    <a href="{{ route('user.setting.storage.list') }}" class="sidebar-menu-list__link">
                        <span class="icon">
                            <x-icons.storage />
                        </span>
                        <span class="text">@lang('Storage')</span>
                    </a>
                </li>
            @endrole

            @role('organizer')
                <li class="sidebar-menu-list__item {{ menuActive('user.account.setting*') }}">
                    <a href="{{ route('user.account.setting.organization') }}" class="sidebar-menu-list__link">
                        <span class="icon">
                            <x-icons.account-setting />
                        </span>
                        <span class="text">@lang('Account')</span>
                    </a>
                </li>
            @else
                <li class="sidebar-menu-list__item {{ menuActive('user.account.setting*') }}">
                    <a href="{{ route('user.account.setting.profile') }}" class="sidebar-menu-list__link">
                        <span class="icon">
                            <x-icons.account-setting />
                        </span>
                        <span class="text">@lang('Account')</span>
                    </a>
                </li>
            @endrole
        </ul>
    </div>
    <div class="sidebar-bottom">
        <a href="{{ route('user.logout') }}" class="log-out-btn">
            <span class="icon">
                <x-icons.sign-out />
            </span>
            @lang('Log Out')
        </a>
    </div>
</div>

@push('script')
    <script>
        if($('li').hasClass('active')){
            $('#sidebar__menuWrapper').animate({
                scrollTop: eval($(".active").offset().top - 320)
            },500);
        }
    </script>
@endpush
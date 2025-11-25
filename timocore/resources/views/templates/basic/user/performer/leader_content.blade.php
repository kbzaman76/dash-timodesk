 <div class="row gy-4">
     @foreach ($topMembers as $member)
         <div class="col-xxl-3 col-xl-4 col-md-4 col-sm-6">
             <div class="leader-boarder-card">
                 <div class="leader-boarder-card-header">
                     <h5 class="mb-0">
                         {{ \Carbon\Carbon::createFromFormat('m', $member->month)->format('F') }}
                     </h5>
                    @if ($member->user) 
                         <div class="dropdown action-dropdown">
                             <button class="btn btn--sm btn--secondary justify-content-center" type="button"
                                 data-bs-toggle="dropdown" aria-expanded="false">
                                 <i class="fas fa-ellipsis-v"></i>
                             </button>
                             <ul class="dropdown-menu">
                                 <li class="dropdown-item">
                                     <a class="dropdown-link" href="{{ route('user.performer.leaderboard.mail.send', ['month' => $member->month, 'year' => $member->year]) }}">Send Mail</a>
                                 </li>
                                 <li class="dropdown-item">
                                     <a class="dropdown-link" href="{{ route('user.performer.leaderboard.download', ['month' => $member->month, 'year' => $member->year]) }}">Download</a>
                                 </li>
                             </ul>
                         </div>
                    @endif
                 </div>

                 @if ($member->user)
                     <div class="leader-boarder-card__profile">
                         <img src="{{ $member->user->image_url }}" class="user-image">
                         <p>{{ toTitle($member->user->fullname) }}</p>
                     </div>
                     <div class="leader-boarder-card__list">
                         <div class="leader-boarder-card__item">
                             <strong>
                                 {{ formatSecondsToHoursMinutes($member->totalSeconds) }}
                             </strong>
                             <span>@lang('Time')</span>
                         </div>
                         <div class="leader-boarder-card__item">
                             <strong>{{ formatSecondsToHoursMinutes($member->totalWorkingDays ? $member->totalSeconds / $member->totalWorkingDays : 0) }}</strong>
                             <span>@lang('Avg Time')</span>
                         </div>
                         <div class="leader-boarder-card__item">
                             <strong>{{ (int) ($member->totalActivity / max($member->totalSeconds, 1)) }}%</strong>
                             <span>@lang('Activity')</span>
                         </div>
                     </div>
                 @else
                     <div class="leader-boarder-emty text-center">
                         <x-user.no-data title="No Data Found" />
                     </div>
                 @endif
             </div>
         </div>
     @endforeach
 </div>

 @if (!blank($yearlyTop))
     <h6 class="mt-5">@lang('Top Employees (Most Monthly Wins)')</h6>

     <div class="row pt-4 gy-4">
         @foreach ($yearlyTop as $member)
             <div class="col-xxl-2 col-xl-3 col-md-3 col-sm-6">
                 <div class="leader_wins leader_wins-one">
                     <div class="wins-number">
                         <span>{{ $member->topMonths }}</span>
                         <svg width="35" height="35" viewBox="0 0 35 35" fill="none"
                             xmlns="http://www.w3.org/2000/svg">
                             <path
                                 d="M20.172 3.03196L23.105 8.94646C23.505 9.76978 24.5715 10.5595 25.4715 10.7107L30.7875 11.6012C34.1871 12.1725 34.9871 14.6593 32.5373 17.1125L28.4045 21.2795C27.7045 21.9852 27.3213 23.3462 27.5378 24.3208L28.7211 29.4792C29.6543 33.5622 27.5045 35.1417 23.9216 33.0077L18.9388 30.0337C18.039 29.496 16.5558 29.496 15.6391 30.0337L10.6564 33.0077C7.09014 35.1417 4.92373 33.5453 5.85696 29.4792L7.04016 24.3208C7.25679 23.3462 6.87351 21.9852 6.17358 21.2795L2.04071 17.1125C-0.392358 14.6593 0.390892 12.1725 3.79051 11.6012L9.10659 10.7107C9.98983 10.5595 11.0564 9.76978 11.4563 8.94646L14.3893 3.03196C15.9891 -0.177321 18.5888 -0.177321 20.172 3.03196Z"
                                 stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                 stroke-linejoin="round" />
                         </svg>
                     </div>
                     <div class="leader_wins-img">
                         <img src="{{ $member->user->image_url }}" class="user-image">
                         <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20"
                             color="currentColor" fill="none">
                             <path d="M5 20H19" stroke="currentColor" stroke-width="1.9" stroke-linecap="round"
                                 stroke-linejoin="round"></path>
                             <path
                                 d="M7.99998 12C6.89541 12 5.99998 11.1046 5.99998 10C5.99998 9.78893 6.03267 9.5855 6.09328 9.39449L3.53967 7.22321C3.17186 6.91046 2.61752 6.9284 2.27193 7.26423C2.02704 7.5022 1.93883 7.85285 2.04312 8.17377L4.54281 15.6353C4.81592 16.4505 5.57947 17 6.43922 17H17.5608C18.4205 17 19.1841 16.4505 19.4572 15.6353L21.9569 8.17377C22.0612 7.85285 21.973 7.5022 21.7281 7.26423C21.3825 6.9284 20.8281 6.91046 20.4603 7.22321L17.9067 9.39452C17.9673 9.58552 18 9.78894 18 10C18 11.1046 17.1045 12 16 12C14.8954 12 14 11.1046 14 10C14 9.36285 14.2979 8.79529 14.7621 8.42904L12.6923 3.46154C12.5758 3.18205 12.3028 3 12 3C11.6972 3 11.4241 3.18205 11.3077 3.46154L9.23788 8.42904C9.70204 8.79529 9.99998 9.36285 9.99998 10C9.99998 11.1046 9.10455 12 7.99998 12Z"
                                 stroke="currentColor" stroke-width="1.9" stroke-linecap="round"
                                 stroke-linejoin="round">
                             </path>
                         </svg>
                     </div>
                     <h6>{{ toTitle($member->user->fullname) ?? '' }}</h6>
                 </div>
             </div>
         @endforeach
     </div>
 @endif

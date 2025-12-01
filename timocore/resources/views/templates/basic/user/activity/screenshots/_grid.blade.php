<div class="screenshot-wrapper">
    @forelse ($slices as $slice)
        <div class="screenshot__title mb-3 mt-2 d-flex flex-wrap gap-4">
            <h5 class="fw-semibold">{{ showDateTime($slice['start'], 'h A') }} - {{ showDateTime($slice['end'], 'h A') }}
            </h5>
            <p>({{ formatSecondsToMinuteSeconds($slice['total_times'], false) }} minutes)</p>
        </div>
        <div class="screenshot-wrapper-block">
            <div class="row g-3">
                @foreach ($slice['blocks'] as $block)
                    <div class="col-xxl-2 col-xl-4 col-lg-4 col-md-4 col-sm-6 col-xsm-6 custom__col">
                        <div class="screenshot-item">
                            <button
                                class="screenshot-item-thumb {{ $block['ss_count'] && $block['has_tracks'] ? 'loadSliceScreenshot' : '' }}"
                                data-start="{{ $block['start'] }}" data-date="{{ $block['date'] }}">
                                @if ($block['screenshot'] && $block['has_tracks'])
                                    <div class="overlay">
                                        <span>@lang('View Images')</span>
                                    </div>
                                    <img class="fit-image lazy" data-src="{{ $block['screenshot']->url }}"
                                        src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw=="
                                        alt="Screenshot">
                                @elseif($block['total_times'] > 0)
                                    <span class="no-activity-ss">@lang('No Screenshot')</span>
                                @else
                                    <span class="no-activity-ss">@lang('No Activity')</span>
                                @endif
                            </button>

                            <span class="screenshot-item-duration">
                                {{ showDateTime($block['start'], 'h:i A') }} -
                                {{ showDateTime($block['end'], 'h:i A') }}
                            </span>
                            <div class="screenshot-item-footer d-flex justify-content-between align-items-center">
                                <small class="shots-count">
                                    @if ($block['ss_count'] > 0 && $block['has_tracks'])
                                        {{ $block['ss_count'] }} {{ Str::plural('screenshots', $block['ss_count']) }}
                                    @else
                                        No screenshot
                                    @endif
                                </small>
                                <small>{{ formatSecondsToHoursMinuteSeconds($block['total_times']) }}</small>
                            </div>
                            <div class="screenshot-item-footer">
                                <div class="progress flex-grow-1">
                                    <div class="progress-bar {{ getActivityClass($block['activity']) }}"
                                        style="width: {{ $block['activity'] }}%"></div>
                                </div>
                                <span class="screenshot-item-activity"> {{ $block['activity'] }}% </span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @empty
        <div class="empty-invitation-wrapper text-center">
            <div class="empty-invitation-card">
                <img src="{{ asset('assets/images/empty/screenshots.webp') }}" alt="@lang('No screenshots illustration')"
                    class="empty-invitation-card__img">

                @if (@$member->fullname)
                    <h3 class="empty-invitation-card__title">No Screenshots of <span
                            class="text--base">{{ ucwords($member->fullname) }}</span></h3>
                    <p class="empty-invitation-card__text">
                        @lang('Try choosing a different date or member to explore activities.')
                    </p>
                @else
                    <h3 class="empty-invitation-card__title">No Screenshot Found</h3>
                    <p class="empty-invitation-card__text">
                        @lang('Try choosing a different date to explore activities.')
                    </p>
                @endif


                <button type="button" class="btn btn--dark btn--md js-prev-day">
                    <x-icons.prev />
                    @lang('Previous Day')
                </button>
                <button type="button" class="btn btn--base btn--md js-next-day">
                    @lang('Next Day')
                    <x-icons.next />
                </button>
            </div>
        </div>
    @endforelse
</div>

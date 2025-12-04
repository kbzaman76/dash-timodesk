<div class="screenshot-wrapper">
    @forelse ($slices as $slice)

        <div class="mb-3 mt-2 d-flex flex-wrap gap-4">
            <h5 class="fw-semibold">
                {{ showDateTime($slice['start'], 'h A') }} - {{ showDateTime($slice['end'], 'h A') }}
            </h5>
            <p>({{ formatSecondsToHoursMinuteSeconds($slice['total_times']) }})</p>
        </div>

        <div class="screenshot-wrapper-block">
            <div class="row g-3">
                @forelse ($slice['screenshots'] as $screenshot)
                    <div class="col-xxl-2 col-xl-4 col-lg-4 col-md-4 col-sm-6 col-xsm-6 custom__col">
                        <div class="screenshot-item">
                            @if ($screenshot?->url)
                                <a class="screenshot-item-thumb" href="{{ $screenshot->url }}" data-lightbox="hour-{{ $slice['start'] }}" data-title="{{ 'Project: ' . $screenshot->project->title . ' | Taken at: ' .  showDateTime($screenshot->taken_at,'h:i A') }}">
                                    <div class="overlay">
                                        <span class="text--base">@lang('View Image')</span>
                                    </div>
                                    <img class="fit-image lazy" data-src="{{ $screenshot->url }}" src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==" alt="Screenshot">
                                </a>
                            @else
                                <img class="fit-image lazy" src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==" alt="Screenshot">
                            @endif

                            <span class="screenshot-item-duration">
                                @if ($screenshot?->taken_at)
                                    {{ showDateTime($screenshot->taken_at,'h:i A') }}
                                @else
                                    N/A
                                @endif
                            </span>
                            <span class="project-name screenshot-item-title">
                                @if ($screenshot?->project)
                                    {{ $screenshot->project->title }}
                                @else
                                    N/A
                                @endif
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="col-xxl-2 col-md-4 col-sm-6 col-xsm-6">
                        <div class="screenshot-item">
                            <figure class="screenshot-item-thumb">
                                <img class="fit-image lazy" src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==" alt="Screenshot">
                            </figure>
                            <span class="screenshot-item-duration">N/A</span>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    @empty
        <div class="empty-invitation-wrapper text-center">
            <div class="empty-invitation-card">
                <img src="{{ asset('assets/images/empty/screenshots.webp') }}" alt="@lang('No screenshots illustration')" class="empty-invitation-card__img">
                @if (@$member->fullname && $member->organization_id == organizationId())
                    <h3 class="empty-invitation-card__title">No Screenshots of <span class="text--base">{{ ucwords(@$member->fullname) }}</span></h3>
                    <p class="empty-invitation-card__text">
                        @lang('Try choosing a different date, member, or capture mode to explore more activity.')
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

@push('style')
    <style>
        .empty-invitation-wrapper {
            display: flex;
            justify-content: center;
            padding: 80px 15px;
        }

        .empty-invitation-card {
            max-width: 520px;
            padding: 48px 32px;
        }

        .empty-invitation-card__img {
            max-width: 260px;
            width: 100%;
            margin: 0 auto 24px;
        }

        .empty-invitation-card__title {
            font-weight: 700;
            color: hsl(var(--heading-color));
            margin-bottom: 12px;
        }

        .empty-invitation-card__text {
            color: hsl(var(--body-color));
            margin-bottom: 24px;
        }
    </style>
@endpush

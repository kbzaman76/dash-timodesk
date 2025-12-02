@php
    $pendingSteps = collect($steps ?? [])
        ->where('completed', false)
        ->count();
@endphp

<div class="onboarding-card">
    <div class="">
        <div class="onboarding-hero">
            <div>
                <p class="onboarding-eyebrow">@lang('Welcome, :name', ['name' => toTitle(auth()->user()->fullname)])</p>
                <h3 class="onboarding-title">
                    @lang('Let’s set up your workspace')
                </h3>
                <p class="onboarding-description">
                    @if ($pendingSteps > 0)
                        @lang('Complete the steps below to unlock your dashboard experience.')
                    @else
                        @lang('Great! Your workspace is ready.')
                    @endif
                </p>
            </div>
            <div class="onboarding-progress">
                <span class="onboarding-progress-label">@lang('Steps left')</span>
                <span class="onboarding-progress-count">{{ $pendingSteps }}</span>
            </div>
        </div>

        <div class="onsteps-content">
            @foreach (collect($steps)->sortByDesc('completed') as $step)
                <div class="onsteps-content-item {{ $step['completed'] ? 'active' : '' }}">
                    <div class="step-check "></div>
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 w-100">
                        <div class="step-content">
                            <h5>{{ $step['title'] }}</h5>
                            <p>{{ $step['description'] }}</p>
                        </div>
                        @if (!$step['completed'])
                            @if (
                                $step['key'] == 'verify-email' &&
                                    auth()->user()->ver_code_send_at &&
                                    auth()->user()->ver_code_send_at->addMinutes(2)->gt(now()))
                                @php
                                    $targetTime = auth()->user()->ver_code_send_at->addMinutes(2)->timestamp;
                                    $delay = max(0, $targetTime - time());
                                @endphp

                                <div>
                                    <a href="{{ $step['action_url'] }}" class="btn btn--sm btn--base verificationBtn {{ $delay? 'disabled' : '' }}">
                                        {{ $step['action_label'] }}
                                    </a>
                                    @if($delay)
                                        <small class="d-block">
                                            Retry after <span class="countdown">{{ $delay }}</span> seconds
                                        </small>
                                    @endif
                                </div>
                            @else
                                <a href="{{ $step['action_url'] }}" @if(isset($step['new_tab']) && $step['new_tab']) target="_blank" @endif class="btn btn--sm btn--base">
                                    {{ $step['action_label'] }}
                                </a>
                            @endif
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <div class="text-end mt-3">
            <a href="{{ route('user.onboarding.skip') }}" class="btn btn-outline--base btn--sm">Skip
                <i class="las la-arrow-right"></i> 
            </a>
        </div>

    </div>
</div>
@push('style')
    <style>
        .onsteps-content {
            display: grid;
            gap: 50px;
            border-top: 1px solid #00000021;
            padding-top: 30px;
        }

        .onsteps-content-item {
            display: flex;
            align-content: center;
            width: 100%;
            gap: 20px;
            position: relative;

            &::before {
                content: '';
                position: absolute;
                height: 100%;
                border: 1px dashed #0000006e;
                left: 16px;
                bottom: -43px;
            }

            &:last-child {
                &::before {
                    display: none;
                }
            }

            &.active {
                &::before {
                    border-color: #FF6A00;
                }

                .step-check {
                    background: #FF6A00;
                    border-color: #FF6A00;

                    &::before {
                        display: block;

                    }
                }
            }
        }

        .step-check {
            --step-wh: 35px;
            min-width: var(--step-wh);
            height: var(--step-wh);
            border: 1px dashed #000;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;

            &::before {
                content: '\f00c';
                font-family: "Font Awesome 6 free";
                font-weight: 900;
                color: #fff;
                font-size: 20px;
                display: none;

            }
        }

        .step-content {
            .step-content__title {
                margin-bottom: 8px;
            }
        }



        .onboarding-card {
            border: 0;
            /* padding: 1rem; */
        }

        .onboarding-hero {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .onboarding-eyebrow {
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.08em;
            color: hsl(var(--base));
            margin-bottom: 0.35rem;
        }

        .onboarding-title {
            font-size: 1.8rem;
            margin-bottom: 0.35rem;

            @media (max-width: 575.98px) {
                font-size: 1.6rem;
            }
        }

        .onboarding-description {
            color: rgba(15, 10, 48, 0.7);
            margin-bottom: 0;
        }

        .onboarding-progress {
            background: hsl(var(--base) / 0.1);
            border-radius: 12px;
            padding: 1rem 1.5rem;
            text-align: center;
            min-width: 150px;

            @media (max-width: 700px) {
                display: flex;
                align-content: center;
                gap: 10px;
            }
        }

        .onboarding-progress-label {
            display: block;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: hsl(var(--base));
            margin-bottom: 0.25rem;

            @media (max-width: 700px) {
                margin-bottom: 0;
                font-weight: 500;
            }

        }

        .onboarding-progress-count {
            font-size: 2rem;
            font-weight: 700;
            color: hsl(var(--base));

            @media (max-width: 700px) {
                font-size: 1.4rem;
                line-height: .8;
                font-weight: 600;
            }
        }
    </style>
@endpush

@if ($delay ?? false)
    @push('script')
        <script>
            (function($) {
                "use strict";
                let timeLeft = {{ $delay }};
                let countdownEelement = $(".countdown");
                function startCountdown() {
                    let timer = setInterval(function() {

                        countdownEelement.text(timeLeft);

                        if (timeLeft <= 0) {
                            clearInterval(timer);
                            $('.verificationBtn').removeClass('disabled');
                            countdownEelement.closest('small').remove();
                        }

                        timeLeft--;
                    }, 1000);
                }

                startCountdown();
            })(jQuery);
        </script>
    @endpush
@endif

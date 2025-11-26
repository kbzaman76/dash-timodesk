@extends('Template::layouts.frontend')
@section('content')
    <div class="account-section">
        <div class="account-left">
            <div class="account-left-content">
                <p class="account-left-content-subtitle">
                    @lang('Track Smarter. Work Better. Grow Faster.')
                </p>
                <h2 class="account-left-content-title">
                    @lang('Monitor team productivity, optimize workflows, and empower everyone.')
                </h2>
                <ul class="account-left-content-list">
                    <li class="item">✦ Free {{ Status::FREE_TRIAL_DURATION }}-Days Trial</li>
                    <li class="item">✦ Fast Sign up — No Credit Card Required</li>
                    <li class="item">✦ Advanced Time Tracking</li>
                    <li class="item">✦ Automatic Screenshots</li>
                    <li class="item">✦ Project & Task-Level Insights</li>
                    <li class="item">✦ Powerful Productivity Analytics</li>
                </ul>
            </div>
            <div class="account-left-slider">
                <div class="testimonial-item">
                    <p class="testimonial-item__desc">
                        <i class="las la-clock"></i>
                        @lang('Time you track is time you win — turn scattered hours into measurable progress and build momentum every day.')
                    </p>
                </div>
                <div class="testimonial-item">
                    <p class="testimonial-item__desc">
                        <i class="las la-bolt"></i>
                        @lang('Turn activity into insights, and insights into impact — spot trends, remove bottlenecks, and focus effort where it counts most.')
                    </p>
                </div>
                <div class="testimonial-item">
                    <p class="testimonial-item__desc">
                        <i class="las la-chart-line"></i>
                        @lang('Know where hours go and grow what matters — accurate time data powers better billing, planning, and performance.')
                    </p>
                </div>
                <div class="testimonial-item">
                    <p class="testimonial-item__desc">
                        <i class="las la-target"></i>
                        @lang('Focus, track, improve — day after day; small consistent measurements create big, compounding results for your team.')
                    </p>
                </div>
                <div class="testimonial-item">
                    <p class="testimonial-item__desc">
                        <i class="las la-hourglass-half"></i>
                        @lang('Small habits, big results — start the clock, capture your effort in real time, and celebrate wins you can prove.')
                    </p>
                </div>
            </div>

            <div class="account-left__thumb">
                <img class="fit-image" src="{{ getImage(activeTemplate(true) . '/images/thumbs/owner.png') }}"
                    alt="@lang('Image')" />
            </div>
        </div>
        <div class="account-content position-relative">
            <form action="{{ route('user.register') }}" method="POST"
                class="verify-gcaptcha disableSubmission account-form">
                @csrf
                <div class="account-top">
                    <a href="https://timodesk.com" class="account-heading__logo register__logo">
                        <img src="{{ siteLogo('dark') }}" alt="@lang('logo')" />
                    </a>
                    <p class="account-content__link register__already">
                        @lang('Already Have an account?')
                        <a href="{{ route('user.login') }}">@lang('Sign in') <i class="las la-arrow-right"></i></a>
                    </p>
                </div>
                <div class="account-form-container">
                    <div class="account-heading">
                        <h3 class="account-heading__title text-center">@lang('Create Your Account')</h3>
                    </div>
                    
                    @if ($referrer)
                        <div class="alert alert--base alert--custom mb-3">
                            <span class="alert__icon">
                                <i class="fa-solid fa-exclamation"></i>
                            </span>
                            <div class="alert__content">
                                <p class="alert__desc"> @lang("You've referred by") <span
                                        class="text--base">{{ $referrer->name }}</span>
                                </p>
                            </div>
                        </div>
                    @endif
                    <div class="account-form-content">
                        @if (session()->get('reference') != null)
                            <div class="form-group">
                                <label for="referenceBy" class="form--label">@lang('Reference by')</label>
                                <input type="text" name="referBy" id="referenceBy" class="form--control"
                                    value="{{ session()->get('reference') }}" readonly>
                            </div>
                        @endif
                        <div class="form-group">
                            <label for="fullname" class="form--label">@lang('Name')</label>
                            <input id="fullname" class="form--control" name="fullname" value="{{ old('fullname') }}"
                                type="text" required maxlength="40" />
                        </div>
                        <div class="form-group">
                            <label for="email" class="form--label">@lang('Email')</label>
                            <input id="email" class="form--control checkUser" name="email" value="{{ old('email') }}"
                                type="email" required />
                        </div>
                        <div class="form-group">
                            <label for="password" class="form--label">@lang('Password')</label>
                            <div class="position-relative">
                                <input id="password" type="password" name="password"
                                    class="form--control @if (gs('secure_password')) secure-password @endif"
                                    required />
                                <span class="password-show-hide toggle-password" id="#password">@lang('Show')</span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="password_confirmation" class="form--label">@lang('Confirm Password')</label>
                            <div class="position-relative">
                                <input id="password_confirmation" type="password" name="password_confirmation"
                                    class="form--control" required />
                                <span class="password-show-hide toggle-password"
                                    id="#password_confirmation">@lang('Show')</span>
                            </div>
                        </div>

                        <x-captcha :path="'Template::partials'" isLabel="true" />

                        @if (gs('agree'))
                            <div class="form--check">
                                <input class="form-check-input" type="checkbox" name="agree" id="remember"
                                    @checked(old('agree')) required />
                                <label class="form-check-label" for="remember">
                                    @lang('I agree with')
                                    <a href="https://timodesk.com/privacy-policy" target="_blank">@lang('Privacy Policy')</a>,
                                    <a href="https://timodesk.com/terms-of-service" target="_blank">@lang('Terms and Conditions')</a>
                                    @lang('and')
                                    <a href="https://timodesk.com/data-policy" target="_blank">@lang('Data Policy')</a>
                                </label>
                            </div>
                        @endif
                    </div>
                    <button type="submit" class="btn btn--base w-100 mb-4">
                        @lang('Sign Up')
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade modal--custom" id="existModalCenter" tabindex="-1" role="dialog"
        aria-labelledby="existModalCenterTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="existModalLongTitle">@lang('You are with us')</h5>
                    <span type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="las la-times"></i>
                    </span>
                </div>
                <div class="modal-body">
                    <h6 class="text-center">@lang('You already have an account please Login ')</h6>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-dark btn-sm"
                        data-bs-dismiss="modal">@lang('Close')</button>
                    <a href="{{ route('user.login') }}" class="btn btn--base btn-sm">@lang('Login')</a>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('style-lib')
    <link rel="stylesheet" href="{{ asset(activeTemplate(true) . 'css/slick.css') }}">
@endpush
@push('script-lib')
    <script src="{{ asset(activeTemplate(true) . 'js/slick.min.js') }}"></script>
@endpush


@push('script')
    <script>
        (function($) {
            "use strict";
            $('.account-form').on('submit', function(e) {
                e.preventDefault();

                $('.input-error').remove();
                $('.error-border').removeClass('error-border');

                let hasError = false;

                let email = $('#email');
                let pass = $('#password');
                let passConfirm = $('#password_confirmation');

                let emailValue = email.val().trim();
                let emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

                if (!emailRegex.test(emailValue)) {
                    showError(email, 'Please enter a valid email.');
                    hasError = true;
                }

                if (pass.val().length < 6) {
                    showError(pass, 'Password must be at least 6 characters.');
                    hasError = true;
                }

                if (pass.val() !== passConfirm.val()) {
                    showError(passConfirm, 'Passwords do not match.');
                    hasError = true;
                }

                if (!hasError) {
                    this.submit();
                }
            });

            $('input').on('keyup', function() {
                $(this).removeClass('error-border');
                $(this).next('.input-error').remove();
            });

            // Show error function
            function showError(input, message) {
                input.addClass('error-border');
                input.focus();
                input.after(`<small class="input-error text-danger">${message}</small>`);
            }


        })(jQuery);
    </script>
@endpush

@push('style')
    <style>
        .social-login-btn {
            border: 1px solid #cbc4c4;
        }

        .account-form .form-check-label {
            font-size: 0.875rem !important;
        }
    </style>
@endpush

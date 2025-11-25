@extends('Template::layouts.frontend')
@section('content')
    @push('backurl')
        <p class="account-content__link">
            @lang('Already have an account?')
            <a href="{{ route('user.register') }}">@lang('Sign in') <i class="las la-arrow-right"></i></a>
        </p>
    @endpush

    @if ($organization->is_suspend == Status::YES)
        <div class="account-page-form">
            <div class="account-heading text-center">
                <h3 class="account-heading__title">@lang('Organization Suspended')</h3>
                <p class="account-heading__desc">@lang('Your organization has been suspended. Please contact your organization administrator.')</p>
            </div>
        </div>
    @else
        <form action="{{ route('user.join.register') }}" method="POST" class="verify-gcaptcha disableSubmission account-page-form">
            @csrf
            <div class="account-heading text-center">
                @if ($organization->logo_url)
                    <span class="account-heading__logo org_logo mx-auto">
                        <img src="{{ $organization->logo_url }}" alt="@lang('logo')" />
                    </span>
                @endif
                <p class="account-heading__desc">
                    <span class="text--dark fw-bold">{{ __($organization->name) }}</span> @lang('has invited you to join their workspace let’s get started together!')
                </p>
            </div>
            <div class="account-page-form-content">
                <input type="hidden" name="invitation_id" class="form--control" value="{{ $invitation->id ?? null }}" readonly>
                <input type="hidden" name="invitation_code" class="form--control" value="{{ $organization->invitation_code ?? null }}" readonly>
                <input type="hidden" name="type" class="form--control" value="{{ $type }}" readonly>

                <div class="group-wrapper">
                    <span class="group-wrapper-icon">
                        <x-icons.email-dark />
                    </span>
                    <input type="email" class="form--control checkUser" name="email" value="{{ $invitation->email ?? null }}" @readonly($type == 'email') placeholder="@lang('Enter your email')" required>
                </div>

                <div class="group-wrapper">
                    <span class="group-wrapper-icon">
                        <x-icons.user-dark />
                    </span>
                    <input type="text" class="form--control" name="fullname" maxlength="40" placeholder="@lang('Enter your name')" value="{{ old('fullname') }}" required>
                </div>

                <div class="group-wrapper">
                    <span class="group-wrapper-icon">
                        <x-icons.lock-dark />
                    </span>
                    <div class="position-relative">
                        <input id="password" type="password" name="password" class="form--control @if (gs('secure_password')) secure-password @endif" placeholder="@lang('Create your password')" required />
                        <span class="password-show-hide toggle-password" id="#password">@lang('Show')</span>
                    </div>
                </div>
                <div class="group-wrapper">
                    <span class="group-wrapper-icon">
                        <x-icons.lock-dark />
                    </span>
                    <div class="position-relative">
                        <input id="password_confirmation" type="password" name="password_confirmation" class="form--control" placeholder="@lang('Confirm your password')" required />
                        <span class="password-show-hide toggle-password" id="#password_confirmation">@lang('Show')</span>
                    </div>
                </div>

                <x-captcha :path="'Template::partials'" />

                <button type="submit" class="btn btn--base w-100 mt-4">
                    @lang('Join team now')
                </button>
            </div>
        </form>
    @endif

@endsection

@if (gs('secure_password'))
    @push('script-lib')
        <script src="{{ asset('assets/global/js/secure_password.js') }}"></script>
    @endpush
@endif

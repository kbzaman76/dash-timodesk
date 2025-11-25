@extends('Template::layouts.frontend')
@section('content')
    @push('backurl')
        <p class="account-content__link">
            @lang('Remembered your password?')
            <a href="{{ route('user.login') }}">@lang('Sign in') <i class="las la-arrow-right"></i></a>
        </p>
    @endpush
    <form method="POST" action="{{ route('user.password.update') }}" class="account-page-form">
        @csrf
        <div class="account-heading text-center">
            <h3 class="account-heading__title mb-2">@lang('Reset Password')</h3>
            <p class="account-heading__desc">@lang('Your account has been successfully verified. You may now set a new password. Please choose a strong password and keep it confidential.')</p>
        </div>
        <input type="hidden" name="email" value="{{ $email }}">
        <input type="hidden" name="token" value="{{ $token }}">

        <div class="group-wrapper">
            <span class="group-wrapper-icon">
                <x-icons.lock-dark />
            </span>

            <div class="position-relative">
                <input id="password" type="password"
                    class="form--control @if (gs('secure_password')) secure-password @endif"
                    placeholder="@lang('Create new password')" name="password" required />
                <span class="password-show-hide toggle-password" id="#password">@lang('Show')</span>
            </div>
        </div>

        <div class="group-wrapper mb-0">
            <span class="group-wrapper-icon">
                <x-icons.lock-dark />
            </span>

            <div class="position-relative">
                <input id="password_confirmation" type="password" class="form--control" placeholder="@lang('Confirm password')"
                    name="password_confirmation" required />
                <span class="password-show-hide toggle-password" id="#password_confirmation">@lang('Show')</span>
            </div>
        </div>
        <button type="submit" class="btn btn--base w-100 mt-4 mt-md-5"> @lang('Submit')</button>
    </form>
@endsection

@if (gs('secure_password'))
    @push('script-lib')
        <script src="{{ asset('assets/global/js/secure_password.js') }}"></script>
    @endpush
@endif

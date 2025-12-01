@extends('Template::layouts.frontend')
@section('content')
    @push('backurl')
        <p class="account-content__link">
            @lang('Remembered your password?')
            <a href="{{ route('user.login') }}">@lang('Sign in') <i class="las la-arrow-right"></i></a>
        </p>
    @endpush
    <form method="POST" action="{{ route('user.password.email') }}" class="verify-gcaptcha account-page-form">
        <input type="hidden" value="{{ csrf_token() }}" name="_token" >
        <div class="account-heading text-center">
            <h3 class="account-heading__title mb-2">@lang('Forgot Password?')</h3>
            <p class="account-heading__desc">@lang('To recover your account, please enter your email address so we can locate your account.')</p>
        </div>
        <div class="account-page-form-content">
            <div class="group-wrapper">
                <span class="group-wrapper-icon">
                    <x-icons.email-dark />
                </span>
                <input type="email" class="form--control" name="value" value="{{ old('value') }}"
                    placeholder="@lang('Enter your email')" required>
            </div>

            <x-captcha :path="'Template::partials'" :marginBottom=false />

            <button class="btn btn--base w-100 mt-4 mt-md-4">@lang('Submit')</button>
        </div>
    </form>
@endsection

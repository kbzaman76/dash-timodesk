@extends('Template::layouts.frontend')

@section('content')
    @push('backurl')
        <p class="account-content__link">
            @lang('Don\'t have any account?')
            <a href="{{ route('user.register') }}">@lang('Sign up') <i class="las la-arrow-right"></i></a>
        </p>
    @endpush

    <form method="POST" action="{{ route('user.login.submit') }}" class="verify-gcaptcha account-page-form">
        @csrf
        <div class="account-heading text-center">
            <h3 class="account-heading__title">@lang('Welcome Back')👋🏻</h3>
            <p class="account-heading__desc">@lang('Sign in to access your account')</p>
        </div>

        <div class="account-page-form-content">
            <div class="group-wrapper">
                <span class="group-wrapper-icon">
                    <x-icons.email-dark />
                </span>
                <input type="text" class="form--control" name="email" value="{{ old('email') }}"
                    placeholder="@lang('Enter email')" required />
            </div>
            <div class="group-wrapper">
                <span class="group-wrapper-icon">
                    <x-icons.lock-dark />
                </span>

                <div class="position-relative">
                    <input id="password" type="password" class="form--control" placeholder="@lang('Enter password')"
                        name="password" required />
                    <span class="password-show-hide toggle-password" id="#password">@lang('Show')</span>
                </div>
            </div>

            <x-captcha :path="'Template::partials'" />

            <div class="flex-between">
                <div class="form--check">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember"
                        @checked(old('remember') ? true : false) />
                    <label class="form-check-label" for="remember">@lang('Remember Me')</label>
                </div>
                <a href="{{ route('user.password.request') }}" class="text--base fw-semibold">@lang('Forgot Password?')</a>
            </div>

            <button class="btn btn--base w-100 mt-4 mt-md-5">@lang('Sign In')</button>
        </div>
    </form>
@endsection

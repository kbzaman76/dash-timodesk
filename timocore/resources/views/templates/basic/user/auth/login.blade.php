@extends('Template::layouts.frontend')

@section('content')
    @push('backurl')
        <p class="account-content__link">
            @lang('Don\'t have any account?')
            <a href="{{ route('user.register') }}">@lang('Sign up') <i class="las la-arrow-right"></i></a>
        </p>
    @endpush

    <form method="POST" action="{{ route('user.login.submit') }}" class="verify-gcaptcha account-page-form">
        <input type="hidden" value="{{ csrf_token() }}" name="_token">
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
                    placeholder="@lang('Enter email')" required>
            </div>
            <div class="group-wrapper">
                <span class="group-wrapper-icon">
                    <x-icons.lock-dark />
                </span>

                <div class="position-relative">
                    <input id="password" type="password" class="form--control" placeholder="@lang('Enter password')"
                        name="password" required>
                    <span class="password-show-hide toggle-password" id="#password">@lang('Show')</span>
                </div>
            </div>

            <x-captcha :path="'Template::partials'" />

            <div class="flex-between">
                <div class="form--check">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember"
                        @checked(old('remember') ? true : false)>
                    <label class="form-check-label" for="remember">@lang('Remember Me')</label>
                </div>
                <a href="{{ route('user.password.request') }}" class="text--base fw-semibold">@lang('Forgot Password?')</a>
            </div>

            <button class="btn btn--base w-100 mt-4 mt-md-5">@lang('Sign In')</button>
        </div>
    </form>
@endsection

@push('seo')
    <meta name="description"
        content="TimoDesk is the best employee time tracking software app. You can remotely track and analyze the performance of your team of any size and location.">

    <link rel="shortcut icon" href="https://timodesk.com/assets/images/logo/favicon.png" type="image/x-icon">
    <link rel="canonical" href="https://dash.timodesk.com">


    <link rel="apple-touch-icon" href="https://timodesk.com/assets/images/logo/logo.png">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta name="apple-mobile-web-app-title" content="Login | TimoDesk - Time Tracking Software For Global Teams">

    <meta property="og:type" content="website">
    <meta property="og:title" content="Login | TimoDesk - Time Tracking Software For Global Teams">
    <meta property="og:description"
        content="TimoDesk is the best employee time tracking software app. You can remotely track and analyze the performance of your team of any size and location.">
    <meta property="og:image" content="https://timodesk.com/assets/images/seo.png">

    <meta property="og:image:type" content="image/png">
    <meta property="og:image:width" content="1180">
    <meta property="og:image:height" content="600">
    <meta property="og:url" content="https://dash.timodesk.com">

    <meta name="twitter:card" content="summary_large_image">
@endpush

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
@push('seo')
    <meta name="description"
        content="TimoDesk is the best employee time tracking software app. You can remotely track and analyze the performance of your team of any size and location.">

    <link rel="shortcut icon" href="https://timodesk.com/assets/images/logo/favicon.png" type="image/x-icon">
    <link rel="canonical" href="https://dash.timodesk.com/password/reset">


    <link rel="apple-touch-icon" href="https://timodesk.com/assets/images/logo/logo.png">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta name="apple-mobile-web-app-title" content="Account Recovery | TimoDesk - Time Tracking Software For Global Teams">

    <meta property="og:type" content="website">
    <meta property="og:title" content="Account Recovery | TimoDesk - Time Tracking Software For Global Teams">
    <meta property="og:description"
        content="TimoDesk is the best employee time tracking software app. You can remotely track and analyze the performance of your team of any size and location.">
    <meta property="og:image" content="https://timodesk.com/assets/images/seo.png">

    <meta property="og:image:type" content="image/png">
    <meta property="og:image:width" content="1180">
    <meta property="og:image:height" content="600">
    <meta property="og:url" content="https://dash.timodesk.com/password/reset">

    <meta name="twitter:card" content="summary_large_image">
@endpush
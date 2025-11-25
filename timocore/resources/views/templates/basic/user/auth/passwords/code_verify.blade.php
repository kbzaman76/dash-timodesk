@extends('Template::layouts.frontend')
@section('content')
    @push('backurl')
        <p class="account-content__link">
            @lang('Remembered your password?')
            <a href="{{ route('user.login') }}">@lang('Sign in') <i class="las la-arrow-right"></i></a>
        </p>
    @endpush
    <form action="{{ route('user.password.verify.code') }}" method="POST" class="submit-form account-page-form">
        @csrf
        <div class="account-heading text-center">
            <h3 class="account-heading__title mb-2">@lang('Verify Email Address')</h3>
            <p class="account-heading__desc">@lang('A 6 digit verification code has been sent to your registered email address:') {{ showEmailAddress($email) }}</p>
        </div>
        <input type="hidden" name="email" value="{{ $email }}">

        @include('Template::partials.frontend_verification_code')



        <button class="btn btn--base w-100 mt-4 mt-md-5">@lang('Submit')</button>

        <div class="form-group mt-4 mb-0 text-center">
            @lang('Please check including your Junk/Spam Folder. if not found, you can')
            <a href="{{ route('user.password.request') }}">@lang('Try to send again')</a>.
        </div>
    </form>
@endsection

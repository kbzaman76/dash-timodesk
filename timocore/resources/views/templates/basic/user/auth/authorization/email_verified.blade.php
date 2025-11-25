@extends('Template::layouts.frontend')

@section('content')
    <div class="account-page-form">
        <div class="account-heading text-center mb-0">
            <x-icons.email-check />

            <h3 class="account-heading__title pt-4 mb-3">@lang('Email Verified')</h3>
            <p class="account-heading__desc">
                @lang('Your email has been successfully verified. You can now continue using your account without any restrictions.')
            </p>

            <a href="{{ route('user.home') }}" class="btn btn--base mt-4 h-auto btn--md">@lang('Return to Dashboard')</a>
        </div>

    </div>
@endsection

@extends('Template::layouts.app')
@section('main')
    <div class="suspended-page bg-img" data-background-image="{{ asset('assets/images/suspended-bg.png') }}">
        <div class="suspended-content">
            <img src="{{ asset('assets/images/suspended.png') }}" alt="">
            <h2>@lang('Access Denied')</h2>
            <p>@lang('You don’t have the required permissions to view this page. Please contact your organization administrator.')</p>
            <div class="d-flex flex-wrap gap-3 justify-content-center align-item-center">
                <a href="{{ route('user.logout') }}" class="btn btn--base">
                   <i class="las la-sign-out-alt"></i> @lang('Logout')
                </a>
            </div>
        </div>
    </div>
@endsection
 
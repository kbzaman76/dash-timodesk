@extends('Template::layouts.master')

@section('content')
    <div class="row justify-content-center">
        <div class="col-xxl-10">
            <div class="card custom--card">
                <div class="card-body">
                    <div class="account-setting">
                        <div class="account-setting__header">
                            @include('Template::user.account_setting.org_header')
                        </div>
                        <div class="account-setting__body">
                            <form method="post">
                                @csrf
                                <div class="form-group">
                                    <label for="current_password" class="form--label">@lang('Current Password')</label>
                                    <div class="position-relative">
                                        <input id="current_password" type="password" name="current_password"
                                            class="form--control" required />
                                        <span class="password-show-hide toggle-password"
                                            id="#current_password">@lang('Show')</span>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="password" class="form--label">@lang('Password')</label>
                                    <div class="position-relative">
                                        <input id="password" type="password" name="password"
                                            class="form--control @if (gs('secure_password')) secure-password @endif"
                                            required />
                                        <span class="password-show-hide toggle-password"
                                            id="#password">@lang('Show')</span>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="password_confirmation" class="form--label">@lang('Confirm Password')</label>
                                    <div class="position-relative">
                                        <input id="password_confirmation" type="password" name="password_confirmation"
                                            class="form--control" required />
                                        <span class="password-show-hide toggle-password" id="#password_confirmation">@lang('Show')</span>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn--base w-100">
                                    @lang('Save')
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@if (gs('secure_password'))
    @push('script-lib')
        <script src="{{ asset('assets/global/js/secure_password.js') }}"></script>
    @endpush
@endif

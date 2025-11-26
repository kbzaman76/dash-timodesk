@extends('errors.layout')
@push('title')
    {{ gs()->siteName($pageTitle ?? '403 | Access Denied') }}
@endpush

@section('content')
    <div class="row justify-content-center align-items-center">
        <div class="col-lg-12 text-center">
            <a href="https://timodesk.com" class="main-logo">
                <img src="https://timodesk.com/cdn/emailassets/logo.png" class="logo">
            </a>
            <div class="error-number">
                403
            </div>
            <h2 class="title text--base"> @lang('Access Denied')</h2>
            <p class="description">@lang('This area is restricted and you don’t have the necessary privileges.')</p>
            <a href="{{ route('user.home') }}" class="btn btn--base">
                <span class="icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                        class="injected-svg"
                        data-src="https://cdn.hugeicons.com/icons/link-backward-solid-standard.svg?v=1.0.1"
                        xmlns:xlink="http://www.w3.org/1999/xlink" role="img" color="white">
                        <path
                            d="M10.787 3.30711C10.5068 3.19103 10.1842 3.25519 9.96967 3.46969L2.46967 10.9697C2.17678 11.2626 2.17678 11.7375 2.46967 12.0304L9.96967 19.5304C10.1842 19.7449 10.5068 19.809 10.787 19.6929C11.0673 19.5768 11.25 19.3034 11.25 19V15.2763C13.9406 15.4651 16.1463 16.6429 17.7342 17.8475C18.5984 18.5032 19.2693 19.1592 19.7227 19.6497C19.949 19.8946 20.2803 20.3017 20.3932 20.4409C20.5844 20.7033 20.9232 20.8137 21.2319 20.7133C21.5409 20.6128 21.75 20.3249 21.75 20V18.5C21.75 12.6465 17.0716 7.88572 11.25 7.75287V4.00002C11.25 3.69668 11.0673 3.4232 10.787 3.30711Z"
                            fill="white"></path>
                    </svg>
                </span>
                <span class="text"> @lang('Go Back')</span>
            </a>
        </div>
    </div>
@endsection

@extends('Template::layouts.app')
@section('main')
    <main>
        @if (!request()->routeIs('user.register'))
            <div class="account-page bg-img"
                data-background-image="{{ getImage(activeTemplate(true) . '/images/thumbs/account.png') }}">
                <div class="account-page-header">
                    <a href="https://timodesk.com" class="account-page-header-logo">
                        <img src="{{ siteLogo('dark') }}" alt="@lang('Logo')" >
                    </a>

                    @stack('backurl')

                </div>
                <div class="account-page-body">

                    @yield('content')

                </div>

                @if (request()->routeIs('user.join'))
                    @include('Template::partials.footer')
                @endif

            </div>
        @else
            @yield('content')
        @endif
    </main>
@endsection

@extends('Template::layouts.app')
@section('main')
    <div class="root">
        @include('Template::partials.sidebar')

        <div class="app-body">
            @include('Template::partials.header')

            @stack('breadcrumb')

            <div class="app-body-wrapper">
                @yield('content')
            </div>
        </div>
    </div>
@endsection

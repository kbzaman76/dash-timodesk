@extends('Template::layouts.frontend')
@section('content')
    <h1>Maintenance mode</h1>
@endsection

@push('style')
<style>
    header{
        display:none;
    }
    footer{
        display:none;
    }
    .breadcrumb{
        display:none;
    }
    body{
        background-color:white;
        display: flex;
        align-items: center;
        height: 100vh;
        justify-content: center;
    }
</style>
@endpush

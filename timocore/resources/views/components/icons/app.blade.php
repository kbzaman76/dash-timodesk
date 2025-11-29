@props(['name'])

@php
    $appIcon = getApps($name);
@endphp
<img src="{{ asset('assets/images/apps/'.$appIcon.'.png') }}" alt="{{ $appIcon }}" />

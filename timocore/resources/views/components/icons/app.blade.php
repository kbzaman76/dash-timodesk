@props(['name'])

@php
    $appIcon = getApps($name);
@endphp

<x-dynamic-component :component="'icons.apps.' . $appIcon" />

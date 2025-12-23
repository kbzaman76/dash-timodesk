@props(['name'])

@php
    $image = getAppModifiers()->where('group_name', $name)->first()->image ?? null;
    if($image) {
        $imageLink = getImage(getFilePath('apps') . '/' . $image);
    } else {
        $imageLink = asset('assets/images/apps/'.getApps($name).'.png');
    }
@endphp
<img src="{{ $imageLink }}" alt="{{ $name }}" />


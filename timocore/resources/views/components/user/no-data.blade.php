@props(['title' => 'No Data Found', 'image' => 'no-data.webp'])

<div class="no-data">
    <img class="no-data__thumb" src="{{ asset('assets/images/empty/'. $image) }}" />
    <div class="no-data__content">
        <h6 class="no-data__title">{{ __($title) }}</h6>
    </div>
</div>
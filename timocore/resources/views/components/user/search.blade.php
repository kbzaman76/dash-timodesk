@props([
    'name' => 'search',
    'placeholder' => 'Search ...',
])

<form class="input-group filter-search">
    <input name="{{ $name }}" value="{{ request()->$name ?? '' }}" type="text" class="form--control form-control md-style" placeholder="{{ __($placeholder) }}" />
    <button type="submit" class="input-group-text bg-white">
        <x-icons.search />
    </button>
</form>

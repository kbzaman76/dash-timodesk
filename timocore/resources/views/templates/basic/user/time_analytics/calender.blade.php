<div class="calendar-wrapper">
    @foreach ($weekdays as $wd)
        <div class="calendar-head">{{ htmlspecialchars($wd) }}</div>
    @endforeach

    @foreach ($cells as $index => $cell)
        @php
            $classes = ['calendar-col'];
            if (!$cell['isThisMonth']) {
                $classes[] = 'selected-month';
            }
            if ($cell['isToday']) {
                $classes[] = 'selected-day';
            }
        @endphp
        <div class="{{ implode(' ', $classes) }}">
            <div class="calendar-count-wrapper">
                <span class="calendar-count">{{ $cell['day'] }}</span>
            </div>
            <h4 class="calendar-time-count">{{ $cell['total'] }}</h4>
            <button class="btn btn--md js-projects w-100 {{ $cell['isToday'] ? 'btn--base' : 'btn--white' }}"
                data-date="{{ $cell['ymd'] }}" data-total="{{ $cell['total'] }}" data-projects='@json($projectsByDay[$cell['ymd']] ?? [])' @disabled(!$cell['isThisMonth'] || !$cell['projects'])>
                {{ $cell['projects'] }} Projects
            </button>
        </div>
    @endforeach
</div>

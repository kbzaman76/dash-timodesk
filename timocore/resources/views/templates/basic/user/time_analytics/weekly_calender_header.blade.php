<div class="table-filter-left">
    <div class="datepicker-arrow">
        <button id="btn-prev" class="datepicker-arrow-btn" type="button" data-date="{{ $prevWeek ?? '' }}">
            <i class="fa-solid fa-arrow-left-long"></i>
        </button>
        <button id="btn-next" class="datepicker-arrow-btn" type="button" data-date="{{ $nextWeek ?? '' }}" @disabled($nextWeek > date('Y-m-d')) data-current="{{ date('Y-m-d') }}">
            <i class="fa-solid fa-arrow-right-long"></i>
        </button>
    </div>
    <h6 id="week-label" class="mb-0" data-date="{{ isset($weekStart) ? $weekStart->toDateString() : '' }}">
        {{ $weekLabel ?? '' }}
    </h6>
</div>

@role('manager|organizer')
<div class="table-filter-right">
    <div class="select2-wrapper">
        <select class="img-select2 select2" name="member">
            <option value="0" data-src="{{ asset('assets/images/avatar.png') }}">@lang('All Members')</option>
            @foreach ($members as $member)
                <option
                    value="{{ $member->uid }}" data-src="{{ $member->image_url }}"
                    @selected($member->uid == $memberId)>{{ toTitle($member->fullname) }}</option>
            @endforeach
        </select>
    </div>
</div>
@endrole

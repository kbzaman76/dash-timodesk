@props(['user'])

@php
$tag = 'a';
if(auth()->user()->isStaff()) $tag = 'span';
@endphp

<{{ $tag }}  @role('manager|organizer') href="{{ route('user.member.details', $user->uid) }}" @endrole class="text--dark activity-table-user px-0 user-table__cell @role('staff') staff @endrole">
    @if ($user->image_url) 
        <span class="icon">
            <img src="{{ $user->image_url }}" alt="{{ $user->fullname }}" class="fit-image" />
        </span>
    @else
        <span class="empty-cell">{{ $user->fullname[0] ?? 'U' }}</span>
    @endif
    <span class="name">{{ toTitle($user->fullname) }}</span>
</{{ $tag }}>
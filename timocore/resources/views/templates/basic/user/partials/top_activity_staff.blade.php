<div class="staff-activity">
    @if(!blank($staffs))
        <div class="staff-activity-content">
            @foreach($staffs as $staff)
                <div class="staff-activity-item">
                    <div class="staff-activity-item-thumb">
                        <img src="{{ $staff->image_url }}"
                            alt="{{ $staff->name }}">
                    </div>
                    <div class="staff-activity-item-content">
                        <p class="name">{{ toTitle($staff->name) }}</p>
                        <p class="average">{{ number_format($staff->avg, 2) }}%</p>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="staff-activity-chart">
            <div id="ViewStaffActivity" class="chart-container lg-style" data-labels='@json(collect($staffs ?? [])->pluck('name'))'
                data-values='@json(collect($staffs ?? [])->pluck('avg')->map(fn($avg) => round((float) $avg, 2)))'>
            </div>
        </div>
    @else
            <li class="project-timer-item empty__project">
                <div class="project-timer-item-top no-performer">
                    <img src="{{ emptyImage('top_activity_staff') }}" />
                    <h6 class="project-empty-title">No Activity Found</h6>
                </div>
            </li>
    @endif
</div>

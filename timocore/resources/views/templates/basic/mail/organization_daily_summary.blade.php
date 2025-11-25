<table>
    <tr>
        <td>
            @lang('Hours Worked')
            {{ formatSecondsToHoursMinutes($totalWorked) }}
        </td>
        <td>
            @lang('Activity')
            {{ $activityPercent }}%
        </td>
        <td>
            @lang('Total Project')
            {{ $totalProject }}
        </td>
    </tr>
</table>

<h4>@lang('Top Member')</h4>
<table>
    <thead>
        <tr>
            <th>@lang('Member Name')</th>
            <th>@lang('Hours Worked')</th>
            <th>@lang('Activity')</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($topMembers as $topMember)
            <tr>
                <td>{{ toTitle($topMember->user->fullname) ?? 'N/A' }}</td>
                <td>{{ formatSecondsToHoursMinutes($topMember->totalSeconds) }}</td>
                <td>{{ $topMember->totalSeconds > 0 ? (int) (($topMember->totalActivity / $topMember->totalSeconds) * 100) : 0 }}%</td>
            </tr>
        @endforeach

    </tbody>
</table>

<h4>@lang('Top Project')</h4>
<table>
    <thead>
        <tr>
            <th>@lang('Project Name')</th>
            <th>@lang('Hours Worked')</th>
            <th>@lang('Activity')</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($topProjects as $topProject)
            <tr>
                <td>{{ $topProject->project->title ?? 'N/A' }}</td>
                <td>{{ formatSecondsToHoursMinutes($topProject->totalSeconds) }}</td>
                <td>{{ $topProject->totalSeconds > 0 ? (int) (($topProject->totalActivity / $topProject->totalSeconds) * 100) : 0 }}%</td>
            </tr>
        @endforeach

    </tbody>
</table>

<h4>@lang('Top Tasks')</h4>
<table>
    <thead>
        <tr>
            <th>@lang('Task Name')</th>
            <th>@lang('Hours Worked')</th>
            <th>@lang('Activity')</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($topTasks as $topTask)
            <tr>
                <td>{{ $topTask->task->title ?? 'N/A' }}</td>
                <td>{{ formatSecondsToHoursMinutes($topTask->totalSeconds) }}</td>
                <td>{{ $topTask->totalSeconds > 0 ? (int) ($topTask->totalActivity / $topTask->totalSeconds) * 100 : 0 }}%</td>
            </tr>
        @endforeach

    </tbody>
</table>

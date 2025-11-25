<table class="table table-striped">
    <thead>
        <tr>
            <th>@lang('Project')</th>
            @foreach ($days as $d)
                <th>{{ $d['day'] }} {{ $d['dow'] }}</th>
            @endforeach
            <th>@lang('Total Hours')</th>
        </tr>
    </thead>
    <tbody>
        @forelse($rows as $row)
            <tr>
                <td>
                    <x-user.project-thumb :project="$row['project']" :show_desc="true" />
                </td>
                @foreach ($row['cells'] as $cell)
                    <td>{{ $cell['display'] }}</td>
                @endforeach
                <td>
                    <span class="worklog-lead">{{ $row['total']['display'] }}</span>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="100%" class="text-center">
                    <x-user.no-data title="No weekly worklog found" />
                </td>
            </tr>
        @endforelse

        @if (!blank($rows))
            <tr class="weekly-table-result">
                <td>@lang('Total Hours')</td>
                @foreach ($footer['byDay'] as $cell)
                    <td>{{ $cell['display'] }}</td>
                @endforeach
                <td>
                    <strong class="lead fw-bold">{{ $footer['grand']['display'] }}</strong>
                </td>
            </tr>
        @endif
    </tbody>
</table>

@push('style')
    <style>
        .weekly-table-result td {
            font-weight: 700 !important;
            color: hsl(var(--heading-color)) !important;
        }

        .btn--secondary.active {
            background-color: hsl(var(--black) / .1) !important;
        }
    </style>
@endpush

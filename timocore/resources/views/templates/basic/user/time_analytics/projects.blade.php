<div class="table-responsive">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>@lang('Project')</th>
                <th class="text-end">@lang('Time')</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $it)
                <tr>
                    <td>{{ $it['project'] }}</td>
                    <td class="text-end">{{ $it['display'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="100%" class="text-center">
                        <x-user.no-data />
                    </td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <th class="text-start">@lang('Total')</th>
                <th class="text-end">{{ $totalDisplay ?? '0:00' }}</th>
            </tr>
        </tfoot>
    </table>
</div>


<div class="table-wrapper">
    <div class="table-scroller">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th width="60">Rank</th>
                    <th class="text-start">@lang('Member')</th>
                    <th>@lang('Activity Percentage')</th>
                    <th>@lang('Average Time')</th>
                    <th>@lang('Total Time')</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($performers as $performer)
                    @php
                        $user = $performer->user;
                    @endphp
                    <tr>
                        <td>
                            {{ ($performers->currentPage() - 1) * $performers->perPage() + $loop->iteration }}
                        </td>
                        <td class="text-start">
                            <x-user.table-cell :user="$user" />
                        </td>
                        <td>
                            {{ (int) $performer->avgActivity }}%
                        </td>
                        <td>
                            {{ formatSeconds($performer->totalSeconds / $performer->totalDates) }}
                        </td>
                        <td>
                            <strong>{{ formatSeconds($performer->totalSeconds ?? 0) }}</strong>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="100%" class="py-4">
                            <x-user.no-data title="No performer found" />
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@if ($performers->hasPages())
    <div class="pagination-wrapper">
        {{ paginateLinks($performers) }}
    </div>
@endif

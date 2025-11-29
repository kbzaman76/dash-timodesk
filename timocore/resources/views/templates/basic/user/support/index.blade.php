@extends('Template::layouts.master')
@section('content')
    <div class="table-wrapper w-100">
        <div class="table-filter">
            <div class="input-group filter-search">
                <x-user.search />
            </div>
            <div class="table-filter-right">
                <div class="btn-group">
                    <a href="{{ route('ticket.open') }}" class="btn btn--md btn--secondary">
                        <span class="icon">
                            <x-icons.plus />
                        </span>
                        @lang('New Ticket')
                    </a>
                </div>
            </div>
        </div>
        <div class="table-scroller">
            <table class="table table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>@lang('Subject')</th>
                        <th>@lang('Department')</th>
                        <th>@lang('Status')</th>
                        <th>@lang('Priority')</th>
                        <th>@lang('Last Reply')</th>
                        <th>@lang('Action')</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($supports as $support)
                        <tr>
                            <td> <a href="{{ route('ticket.view', $support->ticket) }}" class="fw-bold">
                                    [@lang('Ticket')#{{ $support->ticket }}] {{ __($support->subject) }} </a></td>
                            <td>{{ $support->department }}</td>
                            <td>
                                @php echo $support->statusBadge; @endphp
                            </td>
                            <td>
                                @php echo $support->priorityBadge; @endphp
                            </td>
                            <td>{{ diffForHumans($support->last_reply) }} </td>

                            <td>
                                <a href="{{ route('ticket.view', $support->ticket) }}" class="btn btn--sm btn-outline--base">
                                    Details
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="100%">
                                <x-user.no-data title="No Support Ticket Found" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($supports->hasPages())
        <div class="pagination-wrapper">
            {{ paginateLinks($supports) }}
        </div>
    @endif
@endsection

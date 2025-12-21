@extends('admin.layouts.app')

@section('panel')
    <div class="row">
        <div class="col-md-12 text-end mb-3">
            <button class="btn btn-outline--danger btn-sm d-none closeTickets confirmationBtn"
                data-question="@lang('Are you sure to close selected tickets?')" data-action="{{ route('admin.ticket.bulk.close') }}">
                <i class="las la-times"></i>
                Close Selected Tickets
                <span class="selectedCount"></span>
            </button>
        </div>
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive--sm table-responsive">
                        <table class="table table--light">
                            <thead>
                                <tr>
                                    @if (!request()->routeIs('admin.ticket.closed'))
                                        <th>
                                            <input class="checkAll" type="checkbox">
                                        </th>
                                    @endif
                                    <th class="text-start">@lang('Subject')</th>
                                    <th>@lang('Department')</th>
                                    <th>@lang('Submitted By')</th>
                                    <th>@lang('Status')</th>
                                    <th>@lang('Priority')</th>
                                    <th>@lang('Last Reply')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($items as $item)
                                    <tr>
                                        @if (!request()->routeIs('admin.ticket.closed'))
                                            <td>
                                                @if ($item->status == Status::TICKET_CLOSE)
                                                    <input type="checkbox" disabled>
                                                @else
                                                    <input class="childCheckBox" name="checkbox_id"
                                                        data-id="{{ @$item->id }}" type="checkbox"
                                                        @disabled($item->status == Status::TICKET_CLOSE)>
                                                @endif
                                            </td>
                                        @endif
                                        <td class="text-start">
                                            <a href="{{ route('admin.ticket.view', $item->id) }}" class="fw-bold">
                                                [@lang('Ticket')#{{ $item->ticket }}] {{ strLimit($item->subject, 30) }}
                                            </a>
                                        </td>
                                        <td>{{ $item->department }}</td>
                                        <td>
                                            @if ($item->organization)
                                                <a href="{{ route('admin.organization.detail', $item->organization_id) }}">
                                                    {{ $item->organization?->name }}
                                                </a>
                                            @else
                                                <p class="fw-bold"> {{ $item->name }}</p>
                                            @endif
                                        </td>
                                        <td>
                                            @php echo $item->statusBadge; @endphp
                                        </td>
                                        <td>
                                            @php echo $item->priorityBadge; @endphp
                                        </td>

                                        <td>
                                            {{ diffForHumans($item->last_reply) }}
                                        </td>

                                        <td>
                                            <a href="{{ route('admin.ticket.view', $item->id) }}"
                                                class="btn btn-sm btn-outline--primary ms-1">
                                                <i class="las la-desktop"></i> @lang('Details')
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="text-muted text-center" colspan="100%">{{ __($emptyMessage) }}</td>
                                    </tr>
                                @endforelse

                            </tbody>
                        </table><!-- table end -->
                    </div>
                </div>
                @if ($items->hasPages())
                    <div class="card-footer py-4">
                        {{ paginateLinks($items) }}
                    </div>
                @endif
            </div><!-- card end -->
        </div>
    </div>

    <x-confirmation-modal />
@endsection

@push('breadcrumb-plugins')
    <form class="d-flex flex-wrap gap-2" id="searchform">
        <select name="department" class="form-control select2 department" data-minimum-results-for-search="-1">
            <option value="">All Department</option>
            <option value="General" @selected(request('department') == 'General')>@lang('General')</option>
            <option value="Sales" @selected(request('department') == 'Sales')>@lang('Sales')</option>
            <option value="Technical" @selected(request('department') == 'Technical')>@lang('Technical')</option>
            <option value="Billing" @selected(request('department') == 'Billing')>@lang('Billing')</option>
        </select>
        <div class="input-group w-auto flex-fill">
            <input type="search" name="search" class="form-control bg--white" placeholder="Search ..."
                value="{{ request()->search }}">
            <button class="btn btn--primary" type="submit"><i class="la la-search"></i></button>
        </div>
    </form>
@endpush

@push('style')
    <style>
        .select2-container {
            width: 160px !important;
        }
    </style>
@endpush

@push('script')
    <script>
        (function($) {
            "use strict";

            $(document).on('change', '.department', function() {
                const selected = this.value;
                $('#searchform').submit();
            });

            $(".childCheckBox").on('change', function(e) {
                let totalLength = $(".childCheckBox").length;
                let checkedLength = $(".childCheckBox:checked").length;
                if (totalLength == checkedLength) {
                    $('.checkAll').prop('checked', true);
                } else {
                    $('.checkAll').prop('checked', false);
                }
                if (checkedLength) {
                    $('.closeTickets').removeClass('d-none')
                } else {
                    $('.closeTickets').addClass('d-none')
                }
                $('.selectedCount').text(`(${checkedLength})`);
                updateFrom();
            });

            $('.checkAll').on('change', function() {
                if ($('.checkAll:checked').length) {
                    $('.childCheckBox').prop('checked', true);
                } else {
                    $('.childCheckBox').prop('checked', false);
                }
                $(".childCheckBox").change();
                updateFrom();
            });

            const confirmationModal = $('#confirmationModal');

            function updateFrom() {
                confirmationModal.find('input[name=ids\\[\\]]').remove();
                let ids = [];

                $('.childCheckBox:checked').each(function() {
                    ids.push($(this).data('id'));
                });

                ids.forEach(function(id) {
                    confirmationModal.find('.modal-body').append(
                        `<input type="hidden" name="ids[]" value="${id}">`
                    );
                });
            }


        })(jQuery);
    </script>
@endpush

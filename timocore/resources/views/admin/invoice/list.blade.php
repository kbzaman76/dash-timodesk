@extends('admin.layouts.app')
@section('panel')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive--md  table-responsive">
                        <table class="table table--light style--two">
                            <thead>
                                <tr>
                                    <th>@lang('Invoice')</th>
                                    <th>@lang('Organization')</th>
                                    <th>@lang('Created At')</th>
                                    <th>@lang('Amount')</th>
                                    <th>@lang('Status')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($invoices as $invoice)
                                    <tr>
                                        <td><strong>#{{ $invoice->invoice_number }}</strong></td>
                                        <td>
                                            <a href="{{ route('admin.organization.detail', $invoice->organization_id) }}">
                                                {{ __($invoice?->organization?->name) }}
                                            </a>
                                        </td>
                                        <td>{{ showDateTime($invoice->created_at) }}</td>
                                        <td>{{ showAmount($invoice->amount) }}</td>
                                        <td>
                                            @php
                                                echo $invoice->statusBadge;
                                            @endphp
                                        </td>
                                        <td>
                                            <div class="button--group">
                                                <button type="button" class="btn btn-sm btn-outline--primary detailsBtn"
                                                    data-invoice="{{ $invoice->id }}">
                                                    <i class="las la-desktop"></i> @lang('Details')
                                                </button>
                                            </div>
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
                @if ($invoices->hasPages())
                    <div class="card-footer py-4">
                        {{ paginateLinks($invoices) }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div id="detailsModal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Invoice Details</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <div class="modal-body"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn--dark btn--sm" data-bs-dismiss="modal">@lang('Close')</button>
                </div>
            </div>
        </div>
    </div>
@endsection


@push('breadcrumb-plugins')
    <x-search-form placeholder="Organization, Invoice number" />
@endpush

@push('style')
    <style>
        .invoice-details {
            display: flex;
            flex-direction: column;
            gap: 24px;
        }

        .invoice-details__header {
            width: 100%;
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
        }

        .invoice-details-info {}

        .invoice-details-info__item {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .invoice-details-info__item:not(:last-child) {
            margin-bottom: 6px;
        }

        .invoice-details-info__item .label {
            width: 150px;
            flex-shrink: 0;
            font-size: 0.875rem;
            font-weight: 500;
            color: rgb(0, 0, 0, 0.8);
            display: inline-flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
        }

        .invoice-details-info__item .label::after {
            content: ':';
        }

        .invoice-details-to .label {
            flex-shrink: 0;
            font-size: 0.875rem;
            font-weight: 500;
            color: rgb(0, 0, 0, 0.8);
            display: inline-flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
        }

        .invoice-details__table {
            width: 100%;
        }

        .invoice-details__table thead tr th {
            font-size: 0.875rem;
            font-weight: 500;
            padding: 8px 12px;
            background-color: rgb(0, 0, 0, 0.05);
            white-space: nowrap;
        }

        .invoice-details__table thead tr th:first-child {
            border-top-left-radius: 8px;
            border-bottom-left-radius: 8px;
        }

        .invoice-details__table thead tr th:last-child {
            border-top-right-radius: 8px;
            border-bottom-right-radius: 8px;
        }

        .invoice-details__table tbody tr td {
            font-size: 0.875rem;
            font-weight: 500;
            padding: 8px 12px;
        }

        .invoice-details__table tbody tr:not(:last-child) td {
            border-bottom: 1px solid rgb(0, 0, 0, 0.05)
        }

        .invoice-details__table .total {
            max-width: 200px;
            font-size: 1rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-left: auto;

        }

        .invoice-details__table .total .label {
            color: rgb(0, 0, 0, 0.6)
        }

        .invoice-details__table .total .value {
            color: black;
        }
    </style>
@endpush
@push('script')
    <script>
        "use strict";
        let detailsModal = $('#detailsModal');

        $('.detailsBtn').on('click', function() {
            // detailsModal.modal('show');
            var id = $(this).data('invoice');
            $.ajax({
                url: "{{ route('admin.invoice.details') }}",
                method: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    id: id
                },
                dataType: 'json',
                success: function(response) {
                    detailsModal.find('.modal-body').html(response.data.html);
                    detailsModal.modal('show');
                },
                error: function(xhr, status, error) {
                    notify('error', error);
                }
            })
        });
    </script>
@endpush

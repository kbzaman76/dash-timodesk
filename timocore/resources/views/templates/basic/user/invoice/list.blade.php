@extends('Template::layouts.master')
@section('content')
    @if (myOrganization()?->is_suspend)
        <div class="alert alert--danger mb-4 d-block">
            <p class="fw-bold">
                Your organization is currently <strong class="text--danger">suspended due to unpaid billing</strong>.
            </p>

            <p>
                Access to certain features has been temporarily restricted.
                To continue using all services, please update your billing and complete the pending payment.
            </p>

            <p>
                Once your payment is confirmed, your organization will be automatically reactivated.
            </p>

            <p class="fw-bold mb-0">
                Please make a payment to restore full access.
            </p>
        </div>
    @endif


    <div class="table-wrapper w-100">
        <div class="table-scroller">
            <table class="table table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>@lang('Created At')</th>
                        <th>@lang('Invoice ID')</th>
                        <th>@lang('Amount')</th>
                        <th>@lang('Status')</th>
                        <th>@lang('Action')</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $invoice)
                        <tr>
                            <td>{{ showDateTime($invoice->created_at) }}</td>
                            <td><strong>#{{ $invoice->invoice_number }}</strong></td>
                            <td>{{ showAmount($invoice->amount) }}</td>
                            <td class="badge-centered">
                                @php
                                    echo $invoice->statusBadge;
                                @endphp
                            </td>
                            <td>
                                <div class="button--group">
                                    <button class="btn btn--sm btn-outline--base payNowBtn" data-id="{{ $invoice->id }}"
                                        data-amount="{{ showAmount($invoice->amount) }}"
                                        data-amount-raw="{{ $invoice->amount }}" @disabled($invoice->status)>
                                        @lang('Pay Now')
                                    </button>
                                    <a href="{{ route('user.invoice.download', $invoice->invoice_number) }}" target="_blank"
                                        class="btn btn--sm btn-outline--base" style="text-decoration: none">
                                        @lang('Download')
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="text-muted text-center" colspan="100%">
                                <x-user.no-data title="No invoice found" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($invoices->hasPages())
        <div class="pagination-wrapper">
            {{ paginateLinks($invoices) }}
        </div>
    @endif

    <div class="modal custom--modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModal"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Invoice Payment')</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <form action="{{ route('user.invoice.pay') }}" method="POST">
                    @csrf
                    <input type="hidden" name="invoice_id">
                    <div class="modal-body">
                        <div class="invoice-summary mb-4">
                            <p class="text-muted mb-1">@lang('Invoice Amount')</p>
                            <h4 class="invoice-amount mb-0">{{ showAmount(0) }}</h4>
                        </div>
                        <div class="payment-option-grid">
                            <label class="payment-option-card">
                                <input type="radio" name="payment_via" value="main_balance">
                                <span class="payment-option-icon">
                                    <img src="{{ asset('assets/images/wallet.webp') }}" alt="wallet">
                                </span>
                                <div class="payment-option-content">
                                    <span class="payment-option-title">@lang('Wallet Payment')</span>
                                    <span class="payment-option-desc">
                                        @lang('Available Balance'): {{ showAmount(myOrganization()->balance) }}
                                    </span>
                                </div>
                            </label>
                            <label class="payment-option-card selected">
                                <input type="radio" name="payment_via" value="card" checked>
                                <span class="payment-option-icon">
                                    <img src="{{ asset('assets/images/card.webp') }}" alt="bkash">
                                </span>
                                <div class="payment-option-content">
                                    <span class="payment-option-title">@lang('Card')</span>
                                    <span class="payment-option-desc">@lang('Pay securely with your debit or credit card.')</span>
                                </div>
                            </label>
                            <label class="payment-option-card">
                                <input type="radio" name="payment_via" value="bkash">
                                <span class="payment-option-icon">
                                    <img src="{{ asset('assets/images/bkash.webp') }}" alt="bkash">
                                </span>
                                <div class="payment-option-content">
                                    <span class="payment-option-title">@lang('bKash')</span>
                                    <span class="payment-option-desc">@lang('Use your bKash wallet for faster checkout.')</span>
                                </div>
                            </label>
                        </div>
                        @if (!empty($bkashGateway))
                            <div class="payment-info bkash-info d-none" data-rate="{{ $bkashGateway->rate }}"
                                data-currency="{{ strtoupper($bkashGateway->currency) }}"
                                data-base-currency="{{ strtoupper(gs('cur_text')) }}" data-rate-label="@lang('Conversion rate')"
                                data-payable-label="@lang('Payable amount via bKash')">
                                <p class="payment-info-rate mb-1"></p>
                                <p class="payment-info-amount fw-semibold mb-0 text--base"></p>
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn--base btn--md w-100">@lang('Submit')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        (function($) {
            "use strict";

            let modal = $('#paymentModal');
            const amountEl = modal.find('.invoice-amount');
            const bkashInfo = modal.find('.bkash-info');
            const bkashRate = parseFloat(bkashInfo.data('rate')) || 0;
            const bkashCurrency = bkashInfo.data('currency') || '';
            const baseCurrency = bkashInfo.data('base-currency') || '';
            const rateLabel = bkashInfo.data('rate-label') || 'Conversion rate';
            const payableLabel = bkashInfo.data('payable-label') || 'Estimated payable via bKash';
            let currentInvoiceAmount = 0;

            $('.payNowBtn').on('click', function() {
                modal.find('input[name=invoice_id]').val($(this).data('id'));
                amountEl.text($(this).data('amount') ?? '{{ showAmount(0) }}');
                currentInvoiceAmount = parseFloat($(this).data('amountRaw')) || 0;
                const defaultMethod = modal.find('input[name=payment_via][value="card"]');
                defaultMethod.prop('checked', true);
                defaultMethod.trigger('change');
                modal.modal('show');
            });

            modal.on('change', 'input[name=payment_via]', function() {
                modal.find('.payment-option-card').removeClass('selected');
                $(this).closest('.payment-option-card').addClass('selected');
                toggleBkashInfo($(this).val() === 'bkash');
            });

            function toggleBkashInfo(showInfo) {
                if (!bkashInfo.length || bkashRate <= 0) {
                    return;
                }

                if (!showInfo) {
                    bkashInfo.addClass('d-none');
                    return;
                }

                const finalAmount = (currentInvoiceAmount * bkashRate) || 0;
                bkashInfo.removeClass('d-none');
                bkashInfo.find('.payment-info-rate').text(
                    `${rateLabel}: 1 ${baseCurrency} = ${formatAmount(bkashRate)} ${bkashCurrency}`);
                bkashInfo.find('.payment-info-amount').text(
                    `${payableLabel}: ${formatAmount(finalAmount)} ${bkashCurrency}`);
            }

            function formatAmount(value) {
                return parseFloat(value || 0).toLocaleString(undefined, {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }

        })(jQuery);
    </script>
@endpush

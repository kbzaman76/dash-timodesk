@php
    $bkashGateway =
        $bkashGateway ??
        \App\Models\GatewayCurrency::whereHas('method', function ($gate) {
            $gate->where('status', \App\Constants\Status::ENABLE);
        })
            ->where('method_code', 126)
            ->first();
    $showAddCreditModal = session('show_add_credit_modal');
@endphp
<div class="app-header">
    <div class="app-header-left">
        <button class="app-header-btn" type="button">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                color="currentColor">
                <path d="M4 5L20 5" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round"></path>
                <path d="M4 12L20 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round"></path>
                <path d="M4 19L20 19" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round"></path>
            </svg>
        </button>
        <h5 class="app-header-title d-none d-sm-block">{{ __($pageTitle) }}</h5>
    </div>
    <div class="app-header-right">

        @if (request()->routeIs('user.billing.overview') ||
                request()->routeIs('user.deposit.history') ||
                request()->routeIs('user.transactions') ||
                request()->routeIs('user.invoice.list'))
            <p class="header-credit-title">Available Credit</p>
            <p class="btn btn--sm btn--white balance-text">{{ showAmount(myOrganization()->balance) }}</p>
            <button class="btn btn--base btn--sm addCreditBtn" type="button"
                data-bkash-rate="{{ optional($bkashGateway)->rate }}"
                data-bkash-currency="{{ strtoupper(optional($bkashGateway)->currency ?? '') }}">
                <span class="icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                        fill="none" color="currentColor">
                        <path
                            d="M13.8975 2.25008C14.7372 2.25008 15.3474 2.24239 15.8721 2.37508L16.1201 2.44637C17.3402 2.83895 18.2806 3.78175 18.6172 4.96786L18.6641 5.15731C18.7376 5.51697 18.7475 5.92435 18.749 6.42391C19.4938 6.56418 20.2403 6.94254 20.7607 7.46297C21.2855 8.04546 21.5203 8.76471 21.6338 9.60848C21.6976 10.0834 21.7243 10.629 21.7373 11.2501H19C18.6354 11.2501 18.1995 11.2728 17.918 11.3272C17.19 11.5223 16.611 12.0672 16.3701 12.7745C16.2871 13.0846 16.25 13.5835 16.25 14.0001C16.25 14.4167 16.2871 14.9155 16.3701 15.2257C16.6109 15.933 17.19 16.4778 17.918 16.6729C18.1995 16.7274 18.6354 16.7501 19 16.7501H21.7373C21.7243 17.3712 21.6976 17.9168 21.6338 18.3917C21.5203 19.2355 21.2855 19.9547 20.7607 20.5372C20.1588 21.1391 19.2917 21.5129 18.3916 21.6339C17.5267 21.7501 16.4275 21.7501 15.0645 21.7501H8.93555C7.57249 21.7501 6.47331 21.7501 5.6084 21.6339C4.76457 21.5204 3.93115 21.1763 3.34863 20.6515C2.74678 20.0495 2.48722 19.2918 2.36621 18.3917C2.24997 17.5268 2.24997 16.4275 2.25 15.0645V5.00008C2.25012 3.48142 3.55407 2.25009 5.16211 2.25008H13.8975ZM21.75 15.2501H19C18.6148 15.2501 18.3721 15.2364 18.3721 15.2364C18.1575 15.25 17.7754 14.9499 17.7754 14.6944C17.7754 14.6206 17.75 14.5131 17.75 14.0001C17.75 13.4869 17.7563 13.3771 17.7754 13.3057C17.7755 13.1014 18.129 12.7638 18.3721 12.7638C18.457 12.7539 18.6148 12.7501 19 12.7501H21.75V15.2501ZM5.36133 4.08212C4.71534 4.08238 4.19172 4.60606 4.19141 5.25204C4.19141 5.89827 4.71515 6.42267 5.36133 6.42294H16.7979C16.7979 6.10667 16.7981 5.94828 16.7803 5.81551C16.66 4.92277 15.9572 4.2199 15.0645 4.09969C14.9317 4.08185 14.7733 4.08212 14.457 4.08212H5.36133Z"
                            fill="currentColor"></path>
                    </svg>
                </span>
                <span class="credit-text-btn text-white">@lang('Add Credit')</span>
            </button>
        @endif

        <div class="dropdown app-dropdown">
            <button type="button" class="app-dropdown-icon" data-bs-toggle="dropdown" aria-expanded="false"
                data-bs-auto-close="outside">
                <i class="las la-bell @if ($userNotificationCount > 0) ring-animation @endif"></i>
                @if ($userNotificationCount > 0)
                    <span class="count calc-size ">
                        {{ $userNotificationCount <= 9 ? $userNotificationCount : '9+' }}
                    </span>
                @endif
            </button>
            <div class="dropdown-menu notification-menu">
                <div class="notification-menu-top">
                    <h5 class="title">@lang('Notifications')</h5>
                </div>

                <ul class="notification-menu-nav">
                    @foreach ($userNotifications as $notification)
                        <li class="notification-menu-item">
                            <a href="{{ route('user.notification.read', $notification->id) }}"
                                class="notification-menu-link">
                                <span class="notification-menu-thumb">
                                    <img src="{{ $notification->sender?->image_url }}" alt="@lang('image')" />
                                </span>
                                <span class="notification-menu-content">
                                    <h6 class="name">{{ toTitle($notification->sender?->fullname ?? null) }}</h6>
                                    <div class="d-flex align-items-center gap-2">
                                        <p class="desc flex-grow-1">{{ __($notification->title) }}</p>
                                        <span class="time">{{ diffForHumans($notification->created_at) }}</span>
                                    </div>
                                </span>
                            </a>
                        </li>
                    @endforeach
                    @if ($userNotifications->isEmpty())
                        <x-user.no-data :title="__('No Notification Found')" />
                    @endif
                </ul>
                <div class="dropdown-menu__footer">
                    <a href="{{ route('user.notifications') }}" class="view-all-message">
                        @lang('View All Notifications')
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
        <div class="dropdown app-dropdown">
            <button type="button" class="app-dropdown-icon" data-bs-toggle="dropdown" aria-expanded="false"
                data-bs-auto-close="outside">
                <i class="las la-user"></i>
            </button>
            <div class="dropdown-menu menu-profile">
                <div class="menu-profile-top">
                    <div class="menu-profile-content">
                        <p class="menu-profile-name">{{ toTitle(auth()->user()->fullname) }}</p>
                        <p class="menu-profile-email">{{ auth()->user()->email }}</p>
                    </div>
                    <div class="menu-profile-image">
                        <img src="{{ auth()->user()->image_url }}" alt="@lang('Image')" />
                    </div>
                </div>
                <ul class="menu-profile-nav">
                    <li class="menu-profile-nav-item">
                        <a href="{{ route('user.account.setting.profile') }}" class="menu-profile-nav-link">
                            <span class="icon">
                                <i class="las la-user-alt"></i>
                            </span>
                            @lang('Profile Setting')
                        </a>
                    </li>
                    <li class="menu-profile-nav-item">
                        <a href="{{ route('user.account.setting.change.password') }}" class="menu-profile-nav-link">
                            <span class="icon">
                                <i class="las la-lock-open"></i>
                            </span>
                            @lang('Change Password')
                        </a>
                    </li>
                    <li class="menu-profile-nav-item log-out">
                        <a href="{{ route('user.logout') }}" class="menu-profile-nav-link">
                            <span class="icon">
                                <i class="fas fa-sign-out"></i>
                            </span>
                            @lang('Log Out')
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

@if (request()->routeIs('user.billing.overview') ||
        request()->routeIs('user.deposit.history') ||
        request()->routeIs('user.transactions') ||
        request()->routeIs('user.invoice.list'))
    <div class="modal custom--modal fade" id="addCreditModal" tabindex="-1" aria-labelledby="addCreditModalLabel"
        aria-hidden="true" data-show="{{ $showAddCreditModal ? '1' : '0' }}"
        data-old-method="{{ $showAddCreditModal ? old('payment_via', 'card') : 'card' }}"
        data-old-amount="{{ $showAddCreditModal ? old('amount', '') : '' }}">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addCreditModalLabel">@lang('Add Credit')</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <form action="{{ route('user.deposit.quick') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group mb-4">
                            <label class="form--label" for="add_credit_amount">@lang('Enter Amount')</label>
                            <div class="input-group">
                                <span class="input-group-text">{{ gs('cur_sym') }}</span>
                                <input type="number" class="form-control form--control add-credit-amount"
                                    name="amount" id="add_credit_amount" placeholder="@lang('00.00')"
                                    autocomplete="off" min="10" step="any" required>
                            </div>
                        </div>
                        <div class="payment-option-grid">
                            <label class="payment-option-card selected">
                                <input type="radio" name="payment_via" value="card" checked>
                                <span class="payment-option-icon">
                                    <img src="{{ asset('assets/images/card.webp') }}" alt="bKash"
                                        class="img-fluid">
                                </span>
                                <div class="payment-option-content">
                                    <span class="payment-option-title">@lang('Card')</span>
                                    <span class="payment-option-desc">@lang('Pay securely with your debit or credit card.')</span>
                                </div>
                            </label>
                            <label class="payment-option-card">
                                <input type="radio" name="payment_via" value="bkash">
                                <span class="payment-option-icon">
                                    <img src="{{ asset('assets/images/bkash.webp') }}" alt="bKash"
                                        class="img-fluid">
                                </span>
                                <div class="payment-option-content">
                                    <span class="payment-option-title">@lang('bKash')</span>
                                    <span class="payment-option-desc">
                                        @lang('Use your bKash wallet for faster checkout.')
                                    </span>
                                </div>
                            </label>
                        </div>
                        <p class="wallet-disabled-note text-danger small mt-2 d-none">
                            @lang('Wallet payment cannot be used to add credit.')
                        </p>
                        <div class="payment-info credit-bkash-info d-none mt-3"
                            data-rate="{{ $bkashGateway->rate ?? 0 }}"
                            data-currency="{{ strtoupper($bkashGateway->currency ?? '') }}"
                            data-base="{{ strtoupper(gs('cur_text')) }}" data-rate-label="@lang('Conversion rate')"
                            data-payable-label="@lang('Payable amount via bKash')">
                            <p class="credit-rate mb-1"></p>
                            <p class="credit-amount fw-semibold mb-0 text--base"></p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn--base btn--md w-100" id="addCreditSubmit">
                            @lang('Continue')
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif

@push('script')
    <script>
        (function($) {
            "use strict";

            const addCreditModal = $('#addCreditModal');
            if (!addCreditModal.length) {
                return;
            }

            const amountInput = addCreditModal.find('.add-credit-amount');
            const methodRadios = addCreditModal.find('input[name="payment_via"]');
            const submitBtn = addCreditModal.find('#addCreditSubmit');
            const walletNotice = addCreditModal.find('.wallet-disabled-note');
            const conversionInfo = addCreditModal.find('.credit-bkash-info');

            let bkashRate = parseFloat(conversionInfo.data('rate')) || 0;
            let bkashCurrency = conversionInfo.data('currency') || '';
            const baseCurrency = conversionInfo.data('base') || '';
            const rateLabel = conversionInfo.data('rate-label') || 'Conversion rate';
            const payableLabel = conversionInfo.data('payable-label') || 'Estimated payable';

            $('.addCreditBtn').on('click', function() {
                const btnRate = parseFloat($(this).data('bkash-rate'));
                const btnCurrency = $(this).data('bkash-currency');
                if (!isNaN(btnRate)) {
                    bkashRate = btnRate;
                    conversionInfo.attr('data-rate', bkashRate);
                }
                if (btnCurrency) {
                    bkashCurrency = btnCurrency;
                    conversionInfo.attr('data-currency', bkashCurrency);
                }

                amountInput.val('');
                methodRadios.prop('checked', false);
                const defaultMethod = methodRadios.filter('[value="card"]');
                defaultMethod.prop('checked', true).trigger('change');
                walletNotice.addClass('d-none');
                conversionInfo.addClass('d-none');
                addCreditModal.modal('show');
            });

            addCreditModal.on('change', 'input[name="payment_via"]', function() {
                methodRadios.closest('.payment-option-card').removeClass('selected');
                $(this).closest('.payment-option-card').addClass('selected');

                if ($(this).val() === 'bkash') {
                    toggleBkashInfo(true);
                } else {
                    toggleBkashInfo(false);
                }

                if ($(this).val() === 'main_balance') {
                    submitBtn.prop('disabled', true);
                    walletNotice.removeClass('d-none');
                } else {
                    submitBtn.prop('disabled', false);
                    walletNotice.addClass('d-none');
                }
            });

            amountInput.on('input', function() {
                updateBkashInfo();
            });

            function toggleBkashInfo(showInfo) {
                if (!conversionInfo.length || bkashRate <= 0) {
                    conversionInfo.addClass('d-none');
                    return;
                }

                if (showInfo && parseFloat(amountInput.val()) > 0) {
                    conversionInfo.removeClass('d-none');
                    updateBkashInfo();
                } else {
                    conversionInfo.addClass('d-none');
                }
            }

            function updateBkashInfo() {
                if (!conversionInfo.length || bkashRate <= 0) {
                    return;
                }

                const amount = parseFloat(amountInput.val());
                if (methodRadios.filter('[value="bkash"]').is(':checked') && amount > 0) {
                    conversionInfo.removeClass('d-none');
                    conversionInfo.find('.credit-rate').text(
                        `${rateLabel}: 1 ${baseCurrency} = ${formatAmount(bkashRate)} ${bkashCurrency}`);
                    conversionInfo.find('.credit-amount').text(
                        `${payableLabel}: ${formatAmount(amount * bkashRate)} ${bkashCurrency}`);
                } else {
                    conversionInfo.addClass('d-none');
                }
            }

            function formatAmount(value) {
                return parseFloat(value || 0).toLocaleString(undefined, {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }

            const shouldShow = addCreditModal.data('show') === 1 || addCreditModal.data('show') === '1';
            if (shouldShow) {
                const oldMethod = addCreditModal.data('old-method') || 'card';
                const oldAmount = addCreditModal.data('old-amount') || '';
                amountInput.val(oldAmount);
                const oldRadio = methodRadios.filter(`[value="${oldMethod}"]`);
                (oldRadio.length ? oldRadio : methodRadios.filter('[value="card"]')).prop('checked', true).trigger(
                    'change');
                addCreditModal.modal('show');
                updateBkashInfo();
            }
        })(jQuery);
    </script>
@endpush

@once
    @push('style')
        <style>
            .app-download-btn {
                  background-color: #f6f7f8;
                  --ok: 37px;
                  height: var(--ok);
                  width: var(--ok);
                  display: inline-flex;
                  color: #000000;
                  border-radius: 40px;
                  align-items: center;
                  justify-content: center;
                  padding: 0;
            }

            .invoice-summary {
                padding: 16px;
                border: 1px solid var(--border, #e5e7eb);
                border-radius: 12px;
                background: #f9fafb;
            }

            .payment-option-grid {
                display: grid;
                gap: 12px;
            }

            .payment-option-card {
                border: 1px solid var(--border, #e5e7eb);
                border-radius: 12px;
                padding: 14px 16px;
                display: flex;
                align-items: center;
                gap: 12px;
                cursor: pointer;
                transition: border-color 0.2s ease, background-color 0.2s ease, box-shadow 0.2s ease;
            }

            .payment-option-card.selected {
                border-color: hsl(var(--base));
                background: hsl(var(--base) / .08);
                box-shadow: 0 8px 20px hsl(var(--base) / .08);
            }

            .payment-option-card input {
                display: none;
            }

            .payment-option-content {
                display: flex;
                flex-direction: column;
                gap: 2px;
            }

            .payment-option-title {
                font-weight: 600;
                color: #0f172a;
            }

            .payment-option-desc {
                font-size: 0.85rem;
                color: #475569;
            }

            .payment-option-icon img {
                width: 38px;
                height: 38px;
                object-fit: contain;
            }

            .payment-info {
                margin-top: 18px;
                padding: 12px 16px;
                border-radius: 10px;
                border: 1px dashed hsl(var(--base) / .5);
                background: hsl(var(--base) / .03);
            }

            .payment-info strong {
                color: #0f172a;
            }

            .wallet-disabled-note {
                font-size: 0.85rem;
            }

            .payment-option-card.disabled-method {
                opacity: 0.6;
                cursor: not-allowed;
            }
        </style>
    @endpush
@endonce

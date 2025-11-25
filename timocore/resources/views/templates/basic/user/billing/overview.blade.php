@extends('Template::layouts.master')
@section('content')
    <div class="row g-3">
        <div class="col-xl-6">
            <div class="widget-card-main">
                <div class="row gy-4">
                    <div class="col-sm-6">
                        <a href="{{ route('user.invoice.list') }}?status={{ Status::INVOICE_UNPAID }}" class="widget-card">
                            <div class="widget-card__body">
                                <div class="widget-card__wrapper">
                                    <div class="widget-card__icon">
                                        <x-icons.calendar-v2 />
                                    </div>
                                    <p class="widget-card__count">{{ $widget['unpaid_invoices'] }}</p>
                                </div>
                                <p class="widget-card__title">@lang('Unpaid Invoices')</p>
                            </div>
                        </a>
                    </div>
                    <div class="col-sm-6">
                        <a href="{{ route('user.invoice.list') }}?status={{ Status::INVOICE_PAID }}" class="widget-card">
                            <div class="widget-card__body">
                                <div class="widget-card__wrapper">
                                    <div class="widget-card__icon">
                                        <x-icons.calendar-cross />
                                    </div>
                                    <p class="widget-card__count">{{ $widget['total_invoices'] }}</p>
                                </div>
                                <p class="widget-card__title">@lang('Total Invoices')</p>
                            </div>
                        </a>
                    </div>
                    <div class="col-sm-6">
                        <div class="widget-card">
                            <div class="widget-card__body">
                                <div class="widget-card__wrapper">
                                    <div class="widget-card__icon">
                                        <x-icons.calendar-back />
                                    </div>
                                    <p class="widget-card__count">{{ showDateTime($organization->next_invoice_date, 'Y-m-d') }}</p>
                                </div>
                                <p class="widget-card__title">@lang('Next Invoice Date')</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="widget-card">
                            <div class="widget-card__body">
                                <div class="widget-card__wrapper">
                                    <div class="widget-card__icon">
                                        <x-icons.chart />
                                    </div>
                                    {{-- Todo: static data  --}}
                                    <p class="widget-card__count">{{ 00000 }}</p>
                                </div>
                                <p class="widget-card__title">@lang('xxxxxx')</p>
                            </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card custom--card ms-xl-3 h-100">
                <div class="card-header">
                    <h5 class="card-title">Billing Information</h5>
                </div>
                <div class="card-body">
                    <ul class="billing-info-list">
                        <li>
                            <span>
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20"
                                    color="currentColor" fill="none">
                                    <path
                                        d="M13 11C13 8.79086 11.2091 7 9 7C6.79086 7 5 8.79086 5 11C5 13.2091 6.79086 15 9 15C11.2091 15 13 13.2091 13 11Z"
                                        stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round"></path>
                                    <path
                                        d="M11.0386 7.55773C11.0131 7.37547 11 7.18927 11 7C11 4.79086 12.7909 3 15 3C17.2091 3 19 4.79086 19 7C19 9.20914 17.2091 11 15 11C14.2554 11 13.5584 10.7966 12.9614 10.4423"
                                        stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round"></path>
                                    <path d="M15 21C15 17.6863 12.3137 15 9 15C5.68629 15 3 17.6863 3 21"
                                        stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round"></path>
                                    <path d="M21 17C21 13.6863 18.3137 11 15 11" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round"></path>
                                </svg>
                                @lang('Total Member'):
                            </span>
                            <span>{{ $totalMember }}</span>
                        </li>
                        <li>
                            <span>
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20"
                                    color="currentColor" fill="none">
                                    <path
                                        d="M8 22H6C4.11438 22 3.17157 22 2.58579 21.4142C2 20.8284 2 19.8856 2 18V16C2 14.1144 2 13.1716 2.58579 12.5858C3.17157 12 4.11438 12 6 12H8C9.88562 12 10.8284 12 11.4142 12.5858C12 13.1716 12 14.1144 12 16V18C12 19.8856 12 20.8284 11.4142 21.4142C10.8284 22 9.88562 22 8 22Z"
                                        stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round"></path>
                                    <path d="M6 15L8 15" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round"></path>
                                    <path
                                        d="M18 2C15.7909 2 14 3.80892 14 6.04033C14 7.31626 14.5 8.30834 15.5 9.1945C16.2049 9.81911 17.0588 10.8566 17.5714 11.6975C17.8173 12.1008 18.165 12.1008 18.4286 11.6975C18.9672 10.8733 19.7951 9.81911 20.5 9.1945C21.5 8.30834 22 7.31626 22 6.04033C22 3.80892 20.2091 2 18 2Z"
                                        stroke="currentColor" stroke-width="2" stroke-linejoin="round"></path>
                                    <path
                                        d="M18 15V18C18 19.8856 18 20.8284 17.5314 21.4142C17.0839 21.9735 16.3761 21.9988 15 21.9999"
                                        stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round"></path>
                                    <path d="M18.0078 6L17.9988 6" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round"></path>
                                </svg>
                                @lang('Tracking Member'):
                            </span>
                            <span>{{ $trackingMember }}</span>
                        </li>
                        <li>
                            <span>
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20"
                                    color="currentColor" fill="none">
                                    <path
                                        d="M21.9644 4.50615C21.9644 4.50615 22.1405 2.72142 21.7095 2.29048M21.7095 2.29048C21.276 1.85699 19.4941 2.0371 19.4941 2.0371M21.7095 2.29048L19 4.99997"
                                        stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round"></path>
                                    <path
                                        d="M21.9644 19.4938C21.9644 19.4938 22.1405 21.2785 21.7095 21.7095M21.7095 21.7095C21.276 22.143 19.4941 21.9629 19.4941 21.9629M21.7095 21.7095L19 19"
                                        stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round"></path>
                                    <path
                                        d="M2.29048 2.29047L5 4.99997M2.29048 2.29047C2.72397 1.85699 4.50593 2.0371 4.50593 2.0371M2.29048 2.29047C1.85953 2.72142 2.03561 4.50614 2.03561 4.50614"
                                        stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round"></path>
                                    <path
                                        d="M2.29048 21.7095L5 19M2.29048 21.7095C2.72397 22.143 4.50593 21.9629 4.50593 21.9629M2.29048 21.7095C1.85953 21.2786 2.03561 19.4939 2.03561 19.4939"
                                        stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round"></path>
                                    <path
                                        d="M19 12C19 15.866 15.866 19 12 19C8.13401 19 5 15.866 5 12C5 8.13401 8.13401 5 12 5C15.866 5 19 8.13401 19 12Z"
                                        stroke="currentColor" stroke-width="2"></path>
                                    <path
                                        d="M11.914 9.30127C10.8094 9.30127 10 9.94342 10 10.6887C10 11.4339 10.5219 11.8999 12 11.8999C13.6282 11.8999 14 12.6423 14 13.3875C14 14.1328 13.2883 14.7214 11.914 14.7214M11.914 9.30127C12.7848 9.30127 13.2451 9.60613 13.6086 10.0165M11.914 9.30127V8.45703M11.914 14.7214C11.0432 14.7214 10.7046 14.5494 10.225 14.1154M11.914 14.7214V15.5088"
                                        stroke="currentColor" stroke-width="2" stroke-linecap="round"></path>
                                </svg>
                                @lang('Price Per User'):
                            </span>
                            <span>{{ showAmount(gs('price_per_user')) }}</span>
                        </li> 
                        {{-- @if ($organizationDiscount) --}}
                            <li>
                                <span>
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20"
                                        height="20" color="currentColor" fill="none">
                                        <path
                                            d="M7.69171 19.6161C8.28274 19.6161 8.57825 19.6161 8.84747 19.716C8.88486 19.7298 8.92172 19.7451 8.95797 19.7617C9.21897 19.8815 9.42793 20.0904 9.84585 20.5083C10.8078 21.4702 11.2887 21.9512 11.8805 21.9955C11.96 22.0015 12.04 22.0015 12.1195 21.9955C12.7113 21.9512 13.1923 21.4702 14.1541 20.5083C14.5721 20.0904 14.781 19.8815 15.042 19.7617C15.0783 19.7451 15.1151 19.7298 15.1525 19.716C15.4218 19.6161 15.7173 19.6161 16.3083 19.6161H16.4173C17.9252 19.6161 18.6792 19.6161 19.1476 19.1476C19.6161 18.6792 19.6161 17.9252 19.6161 16.4173V16.3083C19.6161 15.7173 19.6161 15.4218 19.716 15.1525C19.7298 15.1151 19.7451 15.0783 19.7617 15.042C19.8815 14.781 20.0904 14.5721 20.5083 14.1541C21.4702 13.1923 21.9512 12.7113 21.9955 12.1195C22.0015 12.04 22.0015 11.96 21.9955 11.8805C21.9512 11.2887 21.4702 10.8078 20.5083 9.84585C20.0904 9.42793 19.8815 9.21897 19.7617 8.95797C19.7451 8.92172 19.7298 8.88486 19.716 8.84747C19.6161 8.57825 19.6161 8.28274 19.6161 7.69171V7.58269C19.6161 6.07479 19.6161 5.32083 19.1476 4.85239C18.6792 4.38394 17.9252 4.38394 16.4173 4.38394H16.3083C15.7173 4.38394 15.4218 4.38394 15.1525 4.28405C15.1151 4.27018 15.0783 4.25491 15.042 4.23828C14.781 4.11855 14.5721 3.90959 14.1541 3.49167C13.1923 2.52977 12.7113 2.04882 12.1195 2.00447C12.04 1.99851 11.96 1.99851 11.8805 2.00447C11.2887 2.04882 10.8078 2.52977 9.84585 3.49167C9.42793 3.90959 9.21897 4.11855 8.95797 4.23828C8.92172 4.25491 8.88486 4.27018 8.84747 4.28405C8.57825 4.38394 8.28274 4.38394 7.69171 4.38394H7.58269C6.07479 4.38394 5.32083 4.38394 4.85239 4.85239C4.38394 5.32083 4.38394 6.07479 4.38394 7.58269V7.69171C4.38394 8.28274 4.38394 8.57825 4.28405 8.84747C4.27018 8.88486 4.25491 8.92172 4.23828 8.95797C4.11855 9.21897 3.90959 9.42793 3.49167 9.84585C2.52977 10.8078 2.04882 11.2887 2.00447 11.8805C1.99851 11.96 1.99851 12.04 2.00447 12.1195C2.04882 12.7113 2.52977 13.1923 3.49167 14.1541C3.90959 14.5721 4.11855 14.781 4.23828 15.042C4.25491 15.0783 4.27018 15.1151 4.28405 15.1525C4.38394 15.4218 4.38394 15.7173 4.38394 16.3083V16.4173C4.38394 17.9252 4.38394 18.6792 4.85239 19.1476C5.32083 19.6161 6.07479 19.6161 7.58269 19.6161H7.69171Z"
                                            stroke="currentColor" stroke-width="2"></path>
                                        <path d="M15 9L9 15" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round"></path>
                                        <path d="M15 15H14.9892M9.01076 9H9" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round"></path>
                                    </svg>
                                    @lang('Discount') ({{ $organizationDiscount->coupon_code ?? 'tst1234' }}):
                                </span>
                                {{-- <span>{{ showAmount($organizationDiscount->discount_percent, currencyFormat: false) }}%</span> --}}
                                <span>100%</span>
                            </li>
                            <li>
                                <span>
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20"
                                        height="20" color="currentColor" fill="none">
                                        <path d="M7.72852 15.2861H12.7285M10.2271 12.7861H10.2364M10.2294 17.7861H10.2388"
                                            stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round"></path>
                                        <path
                                            d="M6.5 3.69682C9.53332 6.78172 14.5357 0.123719 17.4957 2.53998C19.1989 3.93028 18.6605 7 16.4494 9"
                                            stroke="currentColor" stroke-width="2" stroke-linecap="round"></path>
                                        <path
                                            d="M18.664 6.57831C19.6473 6.75667 19.8679 7.34313 20.1615 8.97048C20.4259 10.4361 20.5 12.1949 20.5 12.9436C20.4731 13.2195 20.3532 13.477 20.1615 13.687C18.1054 15.722 14.0251 19.565 11.9657 21.474C11.1575 22.1555 9.93819 22.1702 9.08045 21.5447C7.32407 20.0526 5.63654 18.366 3.98343 16.8429C3.3193 16.035 3.33487 14.8866 4.0585 14.1255C6.23711 11.9909 10.1793 8.33731 12.4047 6.31887C12.6278 6.1383 12.9012 6.02536 13.1942 6C13.6935 5.99988 14.5501 6.06327 15.3845 6.10896"
                                            stroke="currentColor" stroke-width="2" stroke-linecap="round"></path>
                                    </svg>
                                    @lang('Cost After Discount'):
                                </span>
                                {{-- <span>{{ showAmount(gs('price_per_user') * $trackingMember - (gs('price_per_user') * $trackingMember * $organizationDiscount->discount_percent) / 100) }}</span> --}}
                                <span>$90 USD</span>
                            </li>
                        {{-- @endif --}}
                        
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        (function($) {
            "use strict";


        })(jQuery);
    </script>
@endpush

@push('style')
    <style>
        .card-header {
           padding-bottom: 12px !important;
        }
        .billing-info-list {
            li {
                display: flex; 
                padding: 9px 0;  
                border-bottom: 1px solid #e9ecef;
                justify-content: space-between;

                &:last-child {
                    border-bottom: none;
                    padding-bottom: 0;
                }

                span {
                    font-size: 15px;
                    display: inline-flex;
                    align-items: center;
                    gap: 7px;
                    &:first-child {
                        min-width: 150px;
                    }
                    &:first-child {
                        font-weight: 500;
                    }
                    svg,
                    &:last-child {
                        font-weight: 600;
                        color: #212529;
                    }
                }

            }
        }

        @media screen and (max-width: 1599px) {

            .widget-card__count {
                font-size: 1.39rem;
            }
        }
    </style>
@endpush

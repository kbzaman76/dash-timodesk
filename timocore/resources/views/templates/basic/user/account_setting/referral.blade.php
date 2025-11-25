@extends('Template::layouts.master')
@section('content')
    <div class="row justify-content-center">
        <div class="col-xxl-10">
            <div class="card custom--card">
                <div class="card-body">
                    <div class="account-setting">
                        <div class="account-setting__header">
                            @include('Template::user.account_setting.org_header')
                        </div>
                        <div class="account-setting__body">
                            <div class="row gy-3 justify-content-between">
                                <div class="col-md-6">
                                    <form action="{{ route('user.account.setting.referral.update') }}" method="post">
                                        @csrf
                                        <div class="mb-3">
                                            <label class="form--label">@lang('Referral Code')</label>
                                            <div class="input-group input--group referral__input">
                                                <input type="text" name="referral_code"
                                                    class="form--control form-control"
                                                    value="{{ old('referral_code', $organization->referral_code) }}"
                                                    placeholder="e.g. acme-team" required>
                                                <button type="submit" class="btn btn--base pe-3 referral__btn">
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                                        width="18" height="18" color="currentColor" fill="none">
                                                        <path
                                                            d="M16.9017 6.12874C18 7.25748 18 9.07416 18 12.7075V14.2925C18 17.9258 18 19.7425 16.9017 20.8713C15.8033 22 14.0355 22 10.5 22C6.96447 22 5.1967 22 4.09835 20.8713C3 19.7425 3 17.9258 3 14.2925V12.7075C3 9.07416 3 7.25748 4.09835 6.12874C5.1967 5 6.96447 5 10.5 5C14.0355 5 15.8033 5 16.9017 6.12874Z"
                                                            stroke="currentColor" stroke-width="1.75" stroke-linecap="round"
                                                            stroke-linejoin="round" />
                                                        <path
                                                            d="M7.5 5.5V10.3693C7.5 11.3046 7.5 11.7722 7.78982 11.9396C8.35105 12.2638 9.4038 11.1823 9.90375 10.8567C10.1937 10.6678 10.3387 10.5734 10.5 10.5734C10.6613 10.5734 10.8063 10.6678 11.0962 10.8567C11.5962 11.1823 12.6489 12.2638 13.2102 11.9396C13.5 11.7722 13.5 11.3046 13.5 10.3693V5.5"
                                                            stroke="currentColor" stroke-width="1.75" stroke-linecap="round"
                                                            stroke-linejoin="round" />
                                                        <path
                                                            d="M9 2H11C15.714 2 18.0711 2 19.5355 3.46447C21 4.92893 21 7.28595 21 12V18"
                                                            stroke="currentColor" stroke-width="1.75" stroke-linecap="round"
                                                            stroke-linejoin="round" />
                                                    </svg>
                                                    @lang('Save')
                                                </button>
                                            </div>
                                            <small>
                                                <i class="las la-info-circle"></i>
                                                @lang('Only letters, numbers, dashes and underscores; 3–32 chars')
                                            </small>
                                        </div>
                                    </form>
                                </div>
                                <div class="col-md-6">
                                    <label class="form--label">@lang('Your Referral Link')</label>
                                    <div class="input-group input--group copy-input-box">
                                        @php
                                            $code = $organization->referral_code ?? '';
                                            $link = route('user.join.referral', $code);
                                        @endphp
                                        <input type="text" class="form--control form-control copyURL" id="refLink"
                                            value="{{ $link }}" readonly>
                                        <button type="button" class="text--dark pe-3" id="copyLink">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20"
                                                height="20" color="currentColor" fill="none">
                                                <path
                                                    d="M9 15C9 12.1716 9 10.7574 9.87868 9.87868C10.7574 9 12.1716 9 15 9L16 9C18.8284 9 20.2426 9 21.1213 9.87868C22 10.7574 22 12.1716 22 15V16C22 18.8284 22 20.2426 21.1213 21.1213C20.2426 22 18.8284 22 16 22H15C12.1716 22 10.7574 22 9.87868 21.1213C9 20.2426 9 18.8284 9 16L9 15Z"
                                                    stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                                    stroke-linejoin="round"></path>
                                                <path
                                                    d="M16.9999 9C16.9975 6.04291 16.9528 4.51121 16.092 3.46243C15.9258 3.25989 15.7401 3.07418 15.5376 2.90796C14.4312 2 12.7875 2 9.5 2C6.21252 2 4.56878 2 3.46243 2.90796C3.25989 3.07417 3.07418 3.25989 2.90796 3.46243C2 4.56878 2 6.21252 2 9.5C2 12.7875 2 14.4312 2.90796 15.5376C3.07417 15.7401 3.25989 15.9258 3.46243 16.092C4.51121 16.9528 6.04291 16.9975 9 16.9999"
                                                    stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                                    stroke-linejoin="round"></path>
                                            </svg>
                                            <span class="copyText">@lang('Copy')</span>
                                        </button>
                                    </div>
                                    <small>
                                        <i class="las la-info-circle"></i>
                                        Share this link so anyone can sign up on <strong>TimoDesk</strong> with your referral code.
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection


@push('script')
    <script>
        (function($) {
            'use strict';

            $('#copyLink').click(function() {
                var copyText = document.getElementsByClassName("copyURL");
                copyText = copyText[0];
                copyText.select();
                copyText.setSelectionRange(0, 99999);

                /*For mobile devices*/
                document.execCommand("copy");
                $('.copyText').text('Copied');
                setTimeout(() => {
                    $('.copyText').text('Copy');
                }, 2000);
            });
        })(jQuery);
    </script>
@endpush

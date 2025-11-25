@extends('Template::layouts.frontend')
@section('content')
    <form method="POST" action="{{ route('user.organization.submit') }}" class="verify-gcaptcha account-page-form">
        @csrf
        <div class="account-heading text-center">
            <h3 class="account-heading__title">@lang('Organization Details')</h3>
        </div>
        <div class="account-page-form-content">
            <div class="form-group">
                <label for="organization_name" class="form--label">@lang('Organization Name')</label>
                <input id="organization_name" class="form--control" name="organization_name" value="{{ old('organization_name') }}" maxlength="255" required />
            </div>
            <div class="form-group">
                <label class="form--label">@lang('Phone Number')</label>
                <input type="hidden" name="mobile_code">
                <input type="hidden" name="country_code">
                <div class="input-group">
                    <span class="input-group-text">
                        <span class="select2-wrapper">
                            <select class="select2" name="country" data-minimum-results-for-search="-1" required>
                                @foreach ($countries as $key => $country)
                                    <option data-mobile_code="{{ $country->dial_code }}" value="{{ $country->country }}" data-code="{{ $key }}">
                                        +{{ __($country->dial_code) }}
                                    </option>
                                @endforeach
                            </select>
                        </span>
                    </span>

                    <input type="number" name="mobile" value="{{ old('mobile') }}" class="form-control form--control" id="phone" required />
                </div>
            </div>
            <div class="form-group">
                <label class="form--label required">@lang('Organization Type')</label>
                <select name="organization_type" class="form--control select2" required>
                    <option value="">@lang('Select Type')</option>
                    @foreach (organizationTypes() as $type)
                        <option value="{{ $type }}" {{ old('organization_type') == $type ? 'selected' : '' }}>
                            @lang($type)
                        </option>
                    @endforeach
                </select>
                <input class="custom-hear-from form--control mt-2 d-none dynamic" name="custom_hear_from" placeholder="@lang('Please enter a value')" />
            </div>
            <div class="form-group orgDetails d-none">
                <label class="form--label required">@lang('Describe your organization')</label>
                <input type="text" class="form--control" name="organization_type_describe" value="{{ old('organization_type_details') }}">
            </div>
            <div class="form-group">
                <label class="form--label required">@lang('How Did You Hear About Us?')</label>
                <select name="hear_about_us" class="form--control select2" required>
                    <option value="">@lang('Select an Option')</option>
                    @foreach (hearAboutUsOptions() as $option)
                        <option value="{{ $option }}" {{ old('hear_about_us') == $option ? 'selected' : '' }}>
                            @lang($option)
                        </option>
                    @endforeach
                </select>
                <input class="custom-hear-from form--control mt-2 d-none dynamic" name="custom_hear_from" placeholder="@lang('Please enter a value')" />
            </div>

            <div class="form-group hearAboutUsDetails d-none">
                <label class="form--label required">@lang('Enter other source')</label>
                <input type="text" class="form--control" name="hear_about_us_source" value="{{ old('hear_about_us_details') }}">
            </div>

            <div class="form-group mb-0">
                <div class="d-flex flex-wrap justify-content-between">
                    <label class="form--label">@lang('Coupon')</label>
                    <span>@lang('Have a coupon code?')</span>
                </div>
                <div class="input-group">
                    <input type="text" class="form-control form--control" name="coupon">
                    <button type="button" class="input-group-text applyBtn">@lang('Apply')</button>
                </div>
                <span class="couponStatus"></span>
            </div>

            <button class="btn btn--base w-100 mt-3">@lang('Submit')</button>
        </div>
    </form>
@endsection



@push('script')
    <script>
        "use strict";
        (function($) {

            $('[name=organization_type]').on('change', function() {
                let organizationType = $(this).val();
                if (organizationType == 'Other') {
                    $('.orgDetails').removeClass('d-none');
                    $('[name=organization_type_describe]').attr('required', true).closest('.form-group').find(
                        'label').addClass('required');
                } else {
                    $('.orgDetails').addClass('d-none');
                    $('[name=organization_type_describe]').attr('required', false).closest('.form-group').find(
                        'label').removeClass('required');
                }
            }).change();

            $('[name=hear_about_us]').on('change', function() {
                let hearAboutUs = $(this).val();
                if (hearAboutUs == 'Other') {
                    $('.hearAboutUsDetails').removeClass('d-none');
                    $('[name=hear_about_us_source]').attr('required', true).closest('.form-group').find('label')
                        .addClass('required');
                } else {
                    $('.hearAboutUsDetails').addClass('d-none');
                    $('[name=hear_about_us_source]').attr('required', false).closest('.form-group').find(
                        'label').removeClass('required');
                }
            }).change();



            $('.select2').select2();

            $('.applyBtn').on('click', function() {
                $('.couponStatus').text('');

                let couponCode = $('input[name=coupon]').val();

                if (couponCode == '') {
                    notify('error', 'Coupon field is required');
                    return false;
                }

                let couponCheckRoute = `{{ route('user.coupon.check') }}`;
                let data = {
                    coupon: couponCode,
                    _token: '{{ csrf_token() }}'
                }

                $.post(couponCheckRoute, data,
                    function(response) {
                        $('.couponStatus').text(response.message);

                        if (response.status == 'error') {
                            $('.couponStatus').removeClass('text-success');
                            $('.couponStatus').addClass('text-danger');
                        } else {
                            $('.couponStatus').removeClass('text-danger');
                            $('.couponStatus').addClass('text-success');
                        }
                    }
                );
            });

            @if ($mobileCode)
                $(`option[data-code={{ $mobileCode }}]`).attr('selected', '');
            @endif

            setTimeout(() => {
                $('input[name=mobile_code]').val($('select[name=country] :selected').data('mobile_code'));
                $('input[name=country_code]').val($('select[name=country] :selected').data('code'));
            }, 100);

            $('select[name=country]').on('change', function() {
                $('input[name=mobile_code]').val($('select[name=country] :selected').data('mobile_code'));
                $('input[name=country_code]').val($('select[name=country] :selected').data('code'));
                var value = $('[name=mobile]').val();
                var token = '{{ csrf_token() }}';

                var data = {
                    mobile: value,
                    _token: token
                }

                checkExist(data);
            });

            $('.checkUser').on('focusout', function(e) {
                var value = $(this).val();
                var data = {
                    email: value,
                    _token: token
                }

                checkExist(data);
            });

            function checkExist(data) {
                var url = '{{ route('user.checkUser') }}';
                $.post(url, data, function(response) {
                    if (response.data != false) {
                        $('#existModalCenter').modal('show');
                    }
                });
            }


        })(jQuery);
    </script>
@endpush

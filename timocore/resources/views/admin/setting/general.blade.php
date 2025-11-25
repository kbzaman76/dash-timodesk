@extends('admin.layouts.app')
@section('panel')
    <div class="row mb-none-30">
        <div class="col-lg-12 col-md-12 mb-30">
            <div class="card">
                <div class="card-body">
                    <form method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-xl-4 col-sm-6">
                                <div class="form-group ">
                                    <label> @lang('Site Title')</label>
                                    <input class="form-control" type="text" name="site_name" required value="{{ gs('site_name') }}">
                                </div>
                            </div>
                            <div class="col-xl-4 col-sm-6">
                                <div class="form-group ">
                                    <label>@lang('Currency')</label>
                                    <input class="form-control" type="text" name="cur_text" required value="{{ gs('cur_text') }}">
                                </div>
                            </div>
                            <div class="col-xl-4 col-sm-6">
                                <div class="form-group ">
                                    <label>@lang('Currency Symbol')</label>
                                    <input class="form-control" type="text" name="cur_sym" required value="{{ gs('cur_sym') }}">
                                </div>
                            </div>
                            <div class="col-xl-4 col-sm-6">
                                <div class="form-group">
                                    <label> @lang('Record to Display Per page')</label>
                                    <select class="select2 form-control" name="paginate_number" data-minimum-results-for-search="-1">
                                        <option value="20" @selected(gs('paginate_number') == 20)>@lang('20 items per page')</option>
                                        <option value="50" @selected(gs('paginate_number') == 50)>@lang('50 items per page')</option>
                                        <option value="100" @selected(gs('paginate_number') == 100)>@lang('100 items per page')</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-xl-4 col-sm-6">
                                <div class="form-group">
                                    <label class="required"> @lang('Currency Showing Format')</label>
                                    <select class="select2 form-control" name="currency_format" data-minimum-results-for-search="-1">
                                        <option value="1" @selected(gs('currency_format') == Status::CUR_BOTH)>@lang('Show Currency Text and Symbol Both')</option>
                                        <option value="2" @selected(gs('currency_format') == Status::CUR_TEXT)>@lang('Show Currency Text Only')</option>
                                        <option value="3" @selected(gs('currency_format') == Status::CUR_SYM)>@lang('Show Currency Symbol Only')</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-xl-4 col-sm-6">
                                <div class="form-group">
                                    <label class="form-label">
                                        @lang('Screenshot Time')
                                        <i class="las la-info-circle" title="@lang('Set the interval (in seconds) at which screenshots will be automatically captured.')">
                                        </i>
                                    </label>
                                    <div class="input-group ">
                                        <input type="number" class="form-control" name="screenshot_time" value="{{ old('screenshot_time', gs('screenshot_time')) }}" required min="1">
                                        <span class="input-group-text">@lang('Seconds')</span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-xl-4 col-sm-6">
                                <div class="form-group">
                                    <label class="form-label">
                                        @lang('Idle Time')
                                        <i class="las la-info-circle" title="@lang('Specify how long (in seconds) a user can remain inactive before being marked as idle.')">
                                        </i>
                                    </label>
                                    <div class="input-group ">
                                        <input type="number" class="form-control" name="idle_time" value="{{ old('idle_time', gs('idle_time')) }}" required min="1">
                                        <span class="input-group-text">@lang('Seconds')</span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-xl-4 col-sm-6">
                                <div class="form-group ">
                                    <label>@lang('Referral Commission')</label>
                                    <div class="input-group">
                                        <input class="form-control" type="text" name="referral_commission" required value="{{ gs('referral_commission') }}">
                                        <span class="input-group-text">{{ gs('cur_text') }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-4 col-sm-6">
                                <div class="form-group ">
                                    <label>@lang('Price Per User')</label>
                                    <div class="input-group">
                                        <input class="form-control" type="number" step="any" name="price_per_user" required value="{{ getAmount(gs('price_per_user')) }}">
                                        <span class="input-group-text">{{ gs('cur_text') }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-4 col-sm-6">
                                <div class="form-group ">
                                    <label>@lang('App Version')</label>
                                    <input class="form-control" type="text" name="app_version" required value="{{ old('app_version', gs('app_version')) }}">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn--primary w-100 h-45">@lang('Submit')</button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

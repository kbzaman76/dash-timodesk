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
                                    <th>@lang('Created')</th>
                                    <th>@lang('Description')</th>
                                    <th>@lang('Coupon')</th>
                                    <th>@lang('Discount')</th>
                                    <th>@lang('Max Uses')</th>
                                    <th>@lang('Status')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($coupons as $coupon)
                                    <tr>
                                        <td>
                                            {{ showDateTime($coupon->created_at) }} <br> {{ diffForHumans($coupon->created_at) }}
                                        </td>

                                        <td>{{ __($coupon->description) }}</td>

                                        <td>{{ $coupon->code }}</td>

                                        <td>{{ showAmount($coupon->discount_percent, currencyFormat:false) }}%</td>

                                        <td>{{ $coupon->max_uses }}</td>

                                        <td>
                                            @php
                                                echo $coupon->statusBadge;
                                            @endphp
                                        </td>

                                        <td>
                                            <div class="button--group">
                                                <button class="btn btn-sm btn-outline--primary editBtn" data-coupon="{{ $coupon }}">
                                                    <i class="las la-pen"></i> @lang('Edit')
                                                </button>

                                                @if ($coupon->status == Status::DISABLE)
                                                    <button class="btn btn-sm btn-outline--success confirmationBtn" data-question="@lang('Are you sure to enable this coupon?')" data-action="{{ route('admin.coupon.status', $coupon->id) }}">
                                                        <i class="la la-eye"></i>@lang('Enable')
                                                    </button>
                                                @else
                                                    <button class="btn btn-sm btn-outline--danger confirmationBtn" data-question="@lang('Are you sure to disable this coupon?')" data-action="{{ route('admin.coupon.status', $coupon->id) }}">
                                                        <i class="la la-eye-slash"></i>@lang('Disable')
                                                    </button>
                                                @endif

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
                @if ($coupons->hasPages())
                    <div class="card-footer py-4">
                        {{ paginateLinks($coupons) }}
                    </div>
                @endif
            </div>
        </div>
    </div>


    <div id="couponModal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title modalTitle"></h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label>@lang('Description')</label>
                            <input type="text" name="description" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>@lang('Code')</label>
                            <input type="text" name="code" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>@lang('Discount')</label>
                            <div class="input-group">
                                <input type="number" step="any" name="discount_percent" class="form-control" required>
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>@lang('Max Uses')</label>
                            <input type="number" name="max_uses" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <div class="d-flex flex-wrap justify-content-between">
                                <label>@lang('Discount Months')</label>
                                 <small class="text--info">@lang('Use -1 for unlimited.')</small> 
                            </div>
                            <div class="input-group">
                                <input type="number" step="1" name="discount_months" class="form-control" required>
                                <span class="input-group-text">@lang('Months')</span>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn--primary h-45 w-100">@lang('Submit')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <x-confirmation-modal />
@endsection

@push('breadcrumb-plugins')
    <x-search-form placeholder="Name" />
    <button class="btn btn-outline--primary addBtn"><i class="las la-plus"></i>@lang('Add New')</button>
@endpush

@push('script')
    <script>
        (function($) {
            "use strict";
            let modal = $('#couponModal');
            let action = `{{ route('admin.coupon.save') }}`;

            $('.addBtn').on('click', function() {
                let image = $(this).data('image');
                modal.find('.modalTitle').text(`@lang('Add New coupon')`);
                modal.find('form').attr('action', action);
                modal.modal('show');
            });

            $('.editBtn').on('click', function() {
                let coupon = $(this).data('coupon');
                modal.find('input[name=description]').val(coupon.description);
                modal.find('input[name=code]').val(coupon.code);
                modal.find('input[name=discount_percent]').val(coupon.discount_percent);
                modal.find('input[name=max_uses]').val(coupon.max_uses);
                modal.find('input[name=discount_months]').val(coupon.discount_months);
                modal.find('.modalTitle').text(`@lang('Update coupon')`);
                modal.find('form').attr('action', action + '/' + coupon.id);
                modal.modal('show');
            });

            modal.on('hidden.bs.modal', function() {
                modal.find('form').trigger('reset');
            });


        })(jQuery);
    </script>
@endpush

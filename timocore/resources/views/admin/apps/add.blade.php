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
                                    <th>@lang('App Name')</th>
                                    <th><input class="checkAll" type="checkbox"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($apps as $app)
                                    <tr>
                                        <td><label for="{{ $app->app_name }}"
                                                class="m-0 p-0 cursor-pointer">{{ $app->app_name }}</label>
                                        </td>
                                        <td><input class="childCheckBox" name="checkbox_id" data-name="{{ $app->app_name }}"
                                                type="checkbox" id="{{ $app->app_name }}"></td>
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
            </div>
        </div>
    </div>


    <div id="addModal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">App Group Name</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <form method="POST" action="{{ route('admin.apps.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label>@lang('App Group Name')</label>
                            <input type="text" name="app_group_name" class="form-control" required>
                        </div>
                        <div class="form-group appNames"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn--primary h-45 w-100">@lang('Submit')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('breadcrumb-plugins')
    <button class="btn btn-outline--primary btn-sm d-none addselectedapp" data-bs-toggle="modal" data-bs-target="#addModal">
        <i class="las la-plus"></i>
        Group Selected Apps
        <span class="selectedCount"></span>
    </button>
    @if (request()->type == 'duplicate')
        <a class="btn bnt-sm btn-outline-primary" href="{{ route('admin.apps.create') }}">@lang('Show All Apps')</a>
    @else
        <a class="btn bnt-sm btn-outline-primary"
            href="{{ route('admin.apps.create') }}?type=duplicate">@lang('Show Duplicate Apps')</a>
    @endif
@endpush

@push('style')
    <style>
        .cursor-pointer {
            cursor: pointer;
        }

        .checkAll,
        .childCheckBox {
            width: 18px;
            height: 18px;
        }

        table.table--light.style--two tbody td {
            padding: 10px 25px !important;
        }
    </style>
@endpush

@push('script')
    <script>
        (function($) {
            "use strict";

            function updateSelectedApps() {
                let container = $('.appNames');
                container.html('');

                $('.childCheckBox:checked').each(function() {
                    let name = $(this).data('name');

                    container.append(`
                        <span class="badge badge--primary me-1 mb-1">
                            ${name}
                            <input type="hidden" name="app_names[]" value="${name}">
                        </span>
                    `);
                });
            }

            // child checkbox change
            $(document).on('change', '.childCheckBox', function() {
                let total = $(".childCheckBox").length;
                let checked = $(".childCheckBox:checked").length;

                $('.checkAll').prop('checked', total === checked);
                $('.addselectedapp').toggleClass('d-none', !checked);
                $('.selectedCount').text(`(${checked})`);
                updateSelectedApps();
            });

            // check all
            $('.checkAll').on('change', function() {
                $('.childCheckBox').prop('checked', this.checked).trigger('change');
            });

        })(jQuery);
    </script>
@endpush

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
                                    <th>@lang('App Group')</th>
                                    <th>@lang('Apps')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($apps as $app)
                                    @php
                                        $groupApps = explode('|', $app->apps);
                                    @endphp
                                    <tr>
                                        <td>
                                            <span class="d-flex align-items-center gap-2">
                                                <img src="{{ getImage(getFilePath('apps') . '/' . $app->image) }}"
                                                    class="app-image" />
                                                <span>{{ $app->group_name }}</span>
                                                <span class="badge badge--success">{{ $app->total_app_count }}</span>
                                            </span>
                                        </td>
                                        <td class="text-wrap">
                                            <div class="apps-name">
                                                @foreach ($groupApps as $groupApp)
                                                    <span class="badge badge--info">{{ $groupApp }}</span>
                                                @endforeach
                                            </div>
                                        </td>
                                        <td>
                                            <div class="button--group">
                                                <a href="{{ route('admin.apps.update.apps', $app->group_name) }}"
                                                    class="btn btn-sm btn-outline--success">
                                                    <i class="las la-sync"></i> @lang('Update App')
                                                </a>
                                                <button class="btn btn-sm btn-outline--primary editBtn"
                                                    data-apps="{{ json_encode($groupApps) }}"
                                                    data-group-name="{{ $app->group_name }}"
                                                    data-image="{{ getImage(getFilePath('apps') . '/' . $app->image) }}">
                                                    <i class="las la-pen"></i> @lang('Edit')
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
                @if ($apps->hasPages())
                    <div class="card-footer py-4">
                        {{ paginateLinks($apps) }}
                    </div>
                @endif
            </div>
        </div>
    </div>


    <div id="editModal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit App Group</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <form method="POST" action="{{ route('admin.apps.update') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label>@lang('App Group Name')</label>
                            <input type="text" name="app_group_name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>@lang('Image')</label>
                            <x-image-uploader class="w-100" type="apps" :required=false />
                        </div>
                        <label class="d-flex justify-content-between">
                            <span class="group-label">Group Apps <span class="text--danger">*</span></span>
                            <span class="addNewApp btn btn--sm btn-outline-primary py-1 mb-1" role="button">
                                <i class="las la-plus"></i> Add More App
                            </span>
                        </label>
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
    <x-search-form placeholder="Name" />
    <a class="btn btn-outline--primary" href="{{ route('admin.apps.create') }}"><i
            class="las la-plus"></i>@lang('Add New')</a>
@endpush

@push('style')
    <style>
        .apps-name {
            max-width: 1000px;
            margin-inline: auto;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .apps-name .badge {
            padding: 2px 6px;
            font-size: 075rem;
        }

        @media (max-width: 991px) {
            .apps-name {
                justify-content: flex-end;
            }
        }

        .appNames .badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 2px 2px 2px 8px;
        }

        .appNames .badge i {
            cursor: pointer;
            font-size: 1.5em;
        }

        .newAppInput {
            border: none;
            background: transparent;
            padding: 0 !important;
        }

        .app-image {
            width: 32px;
            height: 32px;
            object-fit: cover;
        }

        .group-label {
            font-size: 0.85rem;
            font-weight: 500;
            margin-bottom: 5px;
            color: black;
        }
    </style>
@endpush


@push('script')
    <script>
        (function($) {
            "use strict";

            let modal = $('#editModal');
            let container = modal.find('.appNames');
            $('.editBtn').on('click', function() {
                let groupName = $(this).data('group-name');
                let apps = $(this).data('apps');
                let image = $(this).data('image');

                modal.find('input[name="app_group_name"]').val(groupName);

                container.html('');

                apps.forEach(function(name) {
                    container.append(`
                    <span class="badge badge--info me-1 mb-1">
                        ${name}
                        <input type="hidden" name="app_names[]" value="${name}">
                        <i class="las la-times-circle text-danger removeApp"></i>
                    </span>
                `);
                });
                $('.image-upload-preview').css('background-image', `url(${image})`);
                modal.modal('show');
            });

            $('.addNewApp').on('click', function() {
                container.append(`
                    <span class="badge badge--info me-1 mb-1">
                        <input type="text" name="app_names[]" value="" placeholder="App Name" class="newAppInput" required>
                        <i class="las la-times-circle text-danger removeApp"></i>
                    </span>
                `);
            });

            $(document).on('click', '.removeApp', function() {
                $(this).closest('span.badge').remove();
            });

        })(jQuery);
    </script>
@endpush

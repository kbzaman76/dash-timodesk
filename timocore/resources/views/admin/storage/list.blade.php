@extends('admin.layouts.app')

@section('panel')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive--md  table-responsive">
                        <table class="table table--light style--two">
                            <thead class="table-dark">
                                <tr>
                                    <th>@lang('Name')</th>
                                    <th>@lang('Storage Type')</th>
                                    <th>@lang('Storage Used')</th>
                                    <th>@lang('Status')</th>
                                    <th>@lang('Verification')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($storages as $storage)
                                    <tr>
                                        <td>{{ $storage->name }}</td>
                                        <td>
                                            @php
                                                echo $storage->storageTypeBadge;
                                            @endphp
                                        </td>
                                        <td>{{ formatStorageSize($storage->screenshots_sum_size_in_bytes) }}</td>
                                        <td>
                                            @php
                                                echo $storage->statusBadge;
                                            @endphp
                                        </td>
                                        <td>
                                            @if ($storage->verified == Status::NO)
                                                <a href="{{ route('admin.storage.verify', $storage->id) }}" class="btn btn-sm btn-outline--danger rounded">
                                                    <i class="la la-check"></i>@lang('Verify Now')
                                                </a>
                                                <i class="las la-info-circle text--danger" title="{{ __($storage->error_message ?? "Click to verify your storage to enable and use the store") }}"></i>
                                            @else
                                                @php
                                                echo $storage->verifiedBadge;
                                                @endphp
                                            @endif
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-outline--primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="las la-ellipsis-v"></i> @lang('Action')
                                            </button>
                                            <div class="dropdown-menu" style="">
                                                <a href="javascript:void(0)" class="dropdown-item storageEditBtn"
                                                    data-storage="{{ $storage }}"
                                                    data-action="{{ route('admin.storage.store', $storage->id) }}">
                                                    <i class="la la-pencil"></i> @lang('Edit')
                                                </a>

                                                @if ($storage->status == Status::DISABLE)
                                                    <a href="javascript:void(0)" class="dropdown-item confirmationBtn {{ ($storage->verified) ? "" : "disabled" }}"
                                                        data-question="@lang('Are you sure to enable this storage?')"
                                                        data-action="{{ route('admin.storage.status', $storage->id) }}">
                                                        <i class="la la-eye"></i> @lang('Enable')
                                                    </a>
                                                    <a href="javascript:void(0)" class="dropdown-item confirmationBtn {{ ($storage->verified) ? "" : "disabled" }}"
                                                        data-question="@lang('Are you sure to enable this as backup storage?')"
                                                        data-action="{{ route('admin.storage.status.backup', $storage->id) }}">
                                                        <i class="la la-cloud-upload"></i> @lang('Enable as Backup')
                                                    </a>
                                                    <a href="javascript:void(0)" class="dropdown-item confirmationBtn {{ ($storage->verified) ? "" : "disabled" }}"
                                                        data-question="@lang('Are you sure to enable this as permanent file/image storage?')"
                                                        data-action="{{ route('admin.storage.status.permanent', $storage->id) }}">
                                                        <i class="la la-check-circle"></i> @lang('Enable as Permanent')
                                                    </a>

                                                @else
                                                    <a href="javascript:void(0)" class="dropdown-item confirmationBtn"
                                                        data-question="@lang('Are you sure to disable this storage?')"
                                                        data-action="{{ route('admin.storage.status', $storage->id) }}">
                                                        <i class="la la-eye-slash"></i> @lang('Disable')
                                                    </a>
                                                    @if ($storage->status == Status::BACKUP_STORAGE)
                                                        <a href="javascript:void(0)" class="dropdown-item disabled">
                                                            <i class="la la-cloud-upload"></i> @lang('Enable as Backup')
                                                        </a>
                                                    @else
                                                        <a href="javascript:void(0)" class="dropdown-item confirmationBtn {{ ($storage->verified) ? "" : "disabled" }}"
                                                            data-question="@lang('Are you sure to enable this as backup storage?')"
                                                            data-action="{{ route('admin.storage.status.backup', $storage->id) }}">
                                                            <i class="la la-cloud-upload"></i> @lang('Enable as Backup')
                                                        </a>
                                                    @endif
                                                    @if ($storage->status == Status::PERMANENT_STORAGE)
                                                        <a href="javascript:void(0)" class="dropdown-item disabled">
                                                            <i class="la la-check-circle"></i> @lang('Enable as Permanent')
                                                        </a>
                                                    @else
                                                        <a href="javascript:void(0)" class="dropdown-item confirmationBtn {{ ($storage->verified) ? "" : "disabled" }}"
                                                            data-question="@lang('Are you sure to enable this as permanent file/image storage?')"
                                                            data-action="{{ route('admin.storage.status.permanent', $storage->id) }}">
                                                            <i class="la la-check-circle"></i> @lang('Enable as Permanent')
                                                        </a>
                                                    @endif

                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="text-center text-muted" colspan="4">
                                            {{ __($emptyMessage ?? 'No storage found.') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>

                    </div>
                </div>
                @if ($storages->hasPages())
                    <div class="card-footer py-4">
                        {{ paginateLinks($storages) }}
                    </div>
                @endif
            </div>

        </div>
    </div>

    <div class="modal fade" id="storageModal" tabindex="-1" aria-labelledby="storageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="storageModalLabel">@lang('Add New Storage')</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="@lang('Close')"></button>
                </div>

                <form id="storageForm" action="{{ route('admin.storage.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="name" class="form-label">@lang('Name')</label>
                                    <input type="text" name="name" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="storage_type" class="form-label">@lang('Storge Type')</label>
                                    <select name="storage_type" class="form-control sm-style" required>
                                        <option value="{{ Status::S3_STORAGE }}">@lang('s3 Storage')</option>
                                        <option value="{{ Status::FTP_STORAGE }}">@lang('FTP Storage')</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div id="storageConfigInput" class="row"></div>
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
    <form action="" class="input-group w-auto search-form">
        <input type="text" name="search" class="form-control bg--white" value="{{ request('search') }}"
            placeholder="@lang('Name')">
        <button class="btn btn--primary input-group-text"><i class="fas fa-search"></i></button>
    </form>
    <a class="btn btn-outline--primary storageAddBtn" data-action="{{ route('admin.storage.store') }}"><i
            class="las la-plus"></i>@lang('Add New')</a>
@endpush

@push('script')
    <script>
        (function($) {
            "use strict";

            let storageModal = $('#storageModal');
            $('.storageAddBtn').on('click', function() {
                storageModal.find('form').attr('action', $(this).data('action'));
                storageModal.find('#storageModalLabel').text("@lang('Add New Storage')");
                storageModal.find('input[name="name"]').val("");
                $('select[name="storage_type"]').val('{{ Status::S3_STORAGE }}');
                s3Storage();
                storageModal.modal('show');
            });

            $('.storageEditBtn').on('click', function() {
                let data = $(this).data('storage');
                let action = $(this).data('action');

                storageModal.find('form').attr('action', action);
                storageModal.find('#storageModalLabel').text("@lang('Edit Storage')");
                storageModal.find('input[name="name"]').val(data.name);
                storageModal.find('select[name="storage_type"]').val(data.storage_type);
                if (data.storage_type == '{{ Status::S3_STORAGE }}') {
                    s3Storage(data.config);
                } else {
                    ftpStorage(data.config)
                }
                storageModal.modal('show');
            });

            $('select[name="storage_type"]').on('change', function() {
                let storageType = $(this).val();
                if (storageType == '{{ Status::S3_STORAGE }}') {
                    s3Storage();
                } else {
                    ftpStorage()
                }

            })

            function s3Storage(data = {}) {
                let html = `<div class="col-md-6">
                    <div class="form-group mb-3">
                            <label for="access_key" class="form-label">@lang('Access Key')</label>
                            <input type="text" name="access_key" class="form-control" value="${data.access_key || ""}" required>
                        </div>
                    </div>
                   <div class="col-md-6">
                     <div class="form-group mb-3">
                        <label for="secret_key" class="form-label">@lang('Secret Key')</label>
                            <input type="text" name="secret_key" class="form-control"
                                value="${data.secret_key || ""}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label for="region" class="form-label required">@lang('Region')</label>
                            <input type="text" name="region" class="form-control" value="${data.region || ""}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                        <label for="bucket_name" class="form-label">
                            @lang('Bucket Name')
                            <i class="las la-info-circle" title="@lang('Make sure public access is enabled so uploaded images and files can be viewed.')"></i>
                        </label>
                            <input type="text" name="bucket_name" class="form-control"
                                value="${data.bucket_name || ""}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label for="end_point" class="form-label">
                                @lang('End Point')
                                <i class="las la-info-circle" title="@lang('Provide the full S3 endpoint URL used for uploading files. Make sure it includes the HTTPS protocol. Example: https://s3.yourdomain.com')"></i>
                            </label>
                            <input type="url" name="end_point" class="form-control" value="${data.end_point || ""}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label for="public_end_point" class="form-label">
                                @lang('Public URL')
                                <i class="las la-info-circle" title="@lang('Provide the full S3 public endpoint URL where uploaded files are accessible. Example: https://cdn.yourdomain.com.')"></i>
                            </label>
                            <input type="url" name="public_end_point" class="form-control" value="${data.public_end_point || ""}" required>
                        </div>
                    </div>`;
                $('#storageConfigInput').html(html);
                reloadRequiredTooltip();
            }

            function ftpStorage(data = {}) {
                let html = `<div class="col-md-6">
                        <div class="form-group mb-3">
                        <label for="host" class="form-label">
                            @lang('Host')
                            <i class="las la-info-circle" title="@lang('The FTP or storage server address (e.g., ftp.yourdomain.com or 192.168.x.x). Used to connect and log in to the server.')"></i>
                        </label>
                        <input type="text" name="host" class="form-control"
                            value="${ data.host || ""}" required>
                    </div>
                </div>
                <div class="col-md-6">
                        <div class="form-group mb-3">
                        <label for="port" class="form-label">
                            @lang('Port')
                            <i class="las la-info-circle" title="@lang('The port number used for the connection (usually 21 for FTP)')"></i>
                        </label>
                        <input type="text" name="port" class="form-control" value="${ data.port || ""}" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label for="username" class="form-label">
                            @lang('Username')
                        <i class="las la-info-circle" title="@lang('Your FTP or storage account username used to log in.')"></i>
                        </label>
                        <input type="text" name="username" class="form-control" value="${ data.username || ""}" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label for="password" class="form-label">
                            @lang('Password')
                            <i class="las la-info-circle" title="@lang('The password for the above username')"></i>
                        </label>
                        <div class="input-group">
                            <input type="password" name="password" id="password" class="form-control"
                            value="${ data.password || ""}" required>
                            <span class="input-group-text toggle-password" id="#password">
                                <i class="las la-eye"></i>
                            </span>
                        </div>
                    </div>
                </div>
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                        <label for="upload_folder" class="form-label">
                            @lang('Upload Folder')
                            <i class="las la-info-circle" title="@lang('Storage Folder (relative to root, e.g. tracking/screenshot; root may be public_html, /var/www/html, /home/www/ or /)')"></i>
                        </label>
                        <input type="text" name="upload_folder" class="form-control" value="${ data.upload_folder || ""}" required>
                    </div>
                </div>
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                        <label for="domain" class="form-label">
                            @lang('URL')
                            <i class="las la-info-circle" title="@lang('Provide the full public URL where uploaded files can be accessed. It should include the HTTP or HTTPS protocol, e.g. https://yourdomain.com or https://yourdomain.com/screenshotftp')"></i>
                        </label>
                        <input type="url" name="domain" class="form-control" value="${ data.domain || ""}" required>
                    </div>
                </div>`;
                $('#storageConfigInput').html(html);
                reloadRequiredTooltip();
            }

            function reloadRequiredTooltip() {
                $.each($('input, select, textarea'), function(i, element) {

                    if (element.hasAttribute('required')) {
                        $(element).closest('.form-group').find('label').first().addClass('required');
                    }

                });

                var tooltipTriggerList = [].slice.call(document.querySelectorAll(
                    '[title], [data-title], [data-bs-title]'))
                tooltipTriggerList.map(function(tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl)
                });

                var inputElements = $('[type=text],[type=password],[type=url],select,textarea');
                $.each(inputElements, function(index, element) {
                    element = $(element);
                    element.closest('.form-group').find('label').attr('for', element.attr('name'));
                    element.attr('id', element.attr('name'))
                });
            }

            s3Storage();

            $(document).on("click", '.toggle-password', function() {
                var input = $($(this).attr("id"));
                if (input.attr("type") == "password") {
                    input.attr("type", "text");
                    $(this).find('.las').removeClass('la-eye').addClass('la-eye-slash');
                } else {
                    input.attr("type", "password");
                    $(this).find('.las').addClass('la-eye').removeClass('la-eye-slash');
                }
            });

            $('#storageForm').on('submit', function() {
                $(this).find('button').prop('disabled', true);
            });



        })(jQuery);
    </script>
@endpush

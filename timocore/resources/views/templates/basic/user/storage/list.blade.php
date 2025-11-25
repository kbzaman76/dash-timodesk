@extends('Template::layouts.master')
@section('content')
    <div class="alert alert--danger mb-4 d-block">
        <p class="fw-bold">Attention: All files will be uploaded to the <strong
                class="text--base">{{ gs('site_name') }}</strong> default storage unless you activate your own FTP or S3
            server.</p>
        <p>You should only set up your own storage server if you are experienced with FTP or S3. Please note: <strong
                class="text--base">{{ gs('site_name') }}</strong> cannot be held responsible for any failed uploads after you
            activate your own storage. Once you activate your storage server, all files will be stored on your active
            storage server.</p>
    </div>

    <div class="widget-card-main mb-4">
        <div class="row g-3 g-md-4">
            <div class="col-xxl-3 col-xl-6 col-lg-6 col-md-6 col-sm-6">
                <div class="widget-card h-100">
                    <div class="widget-card__body">
                        <div class="widget-card__wrapper">
                            <div class="widget-card__icon">
                                <x-icons.storage />
                            </div>
                            <p class="widget-card__count daysInMonth">{{ formatStorageSize($storageUsed) }}</p>
                        </div>
                        <p class="widget-card__title">@lang('Storage Used')</p>
                    </div>
                </div>
            </div>
            <div class="col-xxl-3 col-xl-6 col-lg-6 col-md-6 col-sm-6">
                <div class="widget-card h-100">
                    <div class="widget-card__body">
                        <div class="widget-card__wrapper">
                            <div class="widget-card__icon">
                                <x-icons.screenshot />
                            </div>
                            <p class="widget-card__count daysInMonth">{{ formatNumberShort($totalScreenshot) }}</p>
                        </div>
                        <p class="widget-card__title">@lang('Total Screenshot')</p>
                    </div>
                </div>
            </div>
            <div class="col-xxl-3 col-xl-6 col-lg-6 col-md-6 col-sm-6">
                <div class="widget-card h-100">
                    <div class="widget-card__body">
                        <div class="widget-card__wrapper">
                            <div class="widget-card__icon">
                                <x-icons.billing />
                            </div>
                            <p class="widget-card__count daysInMonth">{{ formatNumberShort($currentBillingScreenshot) }}</p>
                        </div>
                        <p class="widget-card__title">@lang('Current Billing Cycle Screenshot')</p>
                    </div>
                </div>
            </div>
            <div class="col-xxl-3 col-xl-6 col-lg-6 col-md-6 col-sm-6">
                <div class="widget-card h-100">
                    <div class="widget-card__body">
                        <div class="widget-card__wrapper">
                            <div class="widget-card__icon">
                                <x-icons.server />
                            </div>
                            @php
                                $storage = $storages->where('id', $fileStorageId)->first();
                            @endphp
                            @if ($storage)
                                <p class="widget-card__count daysInMonth">
                                    {{ __($storage->name) }}
                                </p>
                        </div>
                        <p class="widget-card__title">@lang('You are using your own storage server')</p>
                    @else
                        <p class="widget-card__count daysInMonth">{{ gs('site_name') }}</p>
                    </div>
                    <p class="widget-card__title">You are using the <strong
                            class="text--base">{{ gs('site_name') }}</strong> storage server</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
    </div>


    <div class="table-wrapper">
        <div class="table-filter">
            <div class="table-filter-left">
                <div class="input-group filter-search">
                    <x-user.search />
                </div>
            </div>
            <div class="table-filter-right">
                <div class="btn-group">
                    <button type="button" class="btn btn--md btn--secondary storageAddBtn"
                        data-action="{{ route('user.setting.storage.store') }}">
                        <span class="icon">
                            <x-icons.plus />
                        </span>
                        @lang('Add Storage')
                    </button>
                </div>
            </div>
        </div>
        <div class="table-scroller">
            <table class="table table-striped">
                <thead>
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
                            <td class="badge-centered">
                                @php
                                    echo $storage->storageTypeBadge;
                                @endphp
                            </td>
                            <td>{{ formatStorageSize($storage->screenshots->sum('size_in_bytes')) }}</td>
                            <td class="badge-centered">
                                @if ($storage->id == $fileStorageId)
                                    <span class="badge badge--success">@lang('Activated')</span>
                                @else
                                    <span class="badge badge--warning">@lang('Disabled')</span>
                                @endif
                            </td>
                            <td>
                                @if ($storage->verified == Status::NO)
                                    <a href="{{ route('user.setting.storage.verify', $storage->id) }}"
                                        class="btn btn--sm btn-outline--danger rounded">
                                        <i class="la la-check-circle"></i> @lang('Verify Now')
                                    </a>
                                    <i class="las la-info-circle text--danger"
                                        title="{{ __($storage->error_message ?? 'Click to verify your storage to enable and use the store') }}"></i>
                                @else
                                    @php
                                        echo $storage->verifiedBadge;
                                    @endphp
                                @endif
                            </td>
                            <td>
                                <button type="button" class="btn btn--sm btn-outline--base storageEditBtn"
                                    data-storage="{{ $storage }}"
                                    data-action="{{ route('user.setting.storage.store', $storage->id) }}">
                                    Edit
                                </button>
                                @if ($storage->id == $fileStorageId)
                                    <button type="button"
                                        class="btn btn--sm btn-outline--danger notify-delete-btn confirmationBtn"
                                        data-title="Storage Deactivation Confirmation"
                                        data-question="@lang('Are you sure to deactivate the storage?')"
                                        data-action="{{ route('user.setting.storage.deactivate', $storage->id) }}"
                                        @disabled($storage->verified == Status::NO)>
                                        Disable
                                    </button>
                                @else
                                    <button type="button"
                                        class="btn btn--sm btn-outline--success notify-delete-btn confirmationBtn"
                                        data-title="Storage Activation Confirmation"
                                        data-question="@lang('Are you sure to activate the storage?')"
                                        data-action="{{ route('user.setting.storage.activate', $storage->id) }}"
                                        @disabled($storage->verified == Status::NO)>
                                        Enable
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="100%">
                                <x-user.no-data title="No Storage Found" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($storages->hasPages())
            <div class="pagination-wrapper">
                {{ paginateLinks($storages) }}
            </div>
        @endif
    </div>

    <div class="modal fade custom--modal" id="storageModal" tabindex="-1" aria-labelledby="storageModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title" id="storageModalLabel">@lang('Add New Storage')</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="@lang('Close')">
                        <i class="las la-times"></i>
                    </button>
                </div>

                <form id="storageForm" action="{{ route('user.setting.storage.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name" class="form--label">@lang('Name')</label>
                                    <input type="text" name="name" class="form--control md-style" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="storage_type" class="form--label">@lang('Storge Type')</label>
                                    <select name="storage_type" class="form--control sm-style select2" data-minimum-results-for-search="-1" required>
                                        <option value="{{ Status::S3_STORAGE }}">@lang('s3 Storage')</option>
                                        <option value="{{ Status::FTP_STORAGE }}">@lang('FTP Storage')</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div id="storageConfigInput" class="row"></div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn--dark btn--md"
                            data-bs-dismiss="modal">@lang('Cancel')</button>
                        <button type="submit" class="btn btn--base btn--md">@lang('Submit')</button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <x-confirmation-modal />
@endsection

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
                storageModal.find('select[name="storage_type"]').val(data.storage_type).change();
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
                            <div class="form-group">
                                    <label for="access_key" class="form--label">@lang('Access Key')</label>
                                    <input type="text" name="access_key" class="form--control md-style" value="${data.access_key || ""}" required>
                                </div>
                                </div>
                                <div class="col-md-6">
                                <div class="form-group">
                                    <label for="secret_key" class="form--label">@lang('Secret Key')</label>
                                    <input type="text" name="secret_key" class="form--control md-style"
                                        value="${data.secret_key || ""}" required>
                                </div>
                                    </div>
                                <div class="col-md-6">
                                <div class="form-group">
                                    <label for="region" class="form--label required">@lang('Region')</label>
                                    <input type="text" name="region" class="form--control md-style" value="${data.region || ""}" required>
                                </div>
                                    </div>
                                <div class="col-md-6">
                                <div class="form-group">
                                    <label for="bucket_name" class="form--label">
                                        @lang('Bucket Name')
                                        <i class="las la-info-circle" title="@lang('Make sure public access is enabled so uploaded images and files can be viewed.')"></i>
                                    </label>
                                    <input type="text" name="bucket_name" class="form--control md-style"
                                        value="${data.bucket_name || ""}" required>
                                </div>
                                    </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="end_point" class="form--label">
                                            @lang('End Point')
                                            <i class="las la-info-circle" title="@lang('Provide the full S3 endpoint URL used for uploading files. Make sure it includes the HTTPS protocol. Example: https://s3.yourdomain.com')"></i>
                            </label>
                            <input type="url" name="end_point" class="form--control md-style" value="${data.end_point || ""}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="public_end_point" class="form--label">
                                @lang('Public URL')
                                <i class="las la-info-circle" title="@lang('Provide the full S3 public endpoint URL where uploaded files are accessible. Example: https://cdn.yourdomain.com.')"></i>
                            </label>
                            <input type="url" name="public_end_point" class="form--control md-style" value="${data.public_end_point || ""}" required>
                        </div>
                    </div>`;
                $('#storageConfigInput').html(html);
                reloadRequiredTooltip();
            }

            function ftpStorage(data = {}) {
                let html = `
                <div class="col-md-6">
                    <div class="form-group">
                    <label for="host" class="form--label">
                        @lang('Host')
                        <i class="las la-info-circle" title="@lang('The FTP or storage server address (e.g., ftp.yourdomain.com or 192.168.x.x). Used to connect and log in to the server.')"></i>
                    </label>
                    <input type="text" name="host" class="form--control md-style"
                        value="${ data.host || ""}" required>
                </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                    <label for="port" class="form--label">
                        @lang('Port')
                        <i class="las la-info-circle" title="@lang('The port number used for the connection (usually 21 for FTP)')"></i>
                    </label>
                    <input type="text" name="port" class="form--control md-style" value="${ data.port || ""}" required>
                </div>
                </div>
               <div class="col-md-6">
                 <div class="form-group">
                    <label for="username" class="form--label">
                        @lang('Username')
                        <i class="las la-info-circle" title="@lang('Your FTP or storage account username used to log in.')"></i>
                    </label>
                    <input type="text" name="username" class="form--control md-style" value="${ data.username || ""}" required>
                </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                    <label for="password" class="form--label">
                        @lang('Password')
                        <i class="las la-info-circle" title="@lang('The password for the above username')"></i>
                    </label>
                    <div class="position-relative">
                        <input type="password" name="password" id="password" class="form--control md-style" value="${ data.password || ""}" required>
                        <span class="password-show-hide toggle-password" id="#password">@lang('Show')</span>
                    </div>
                </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                    <label for="upload_folder" class="form--label">
                        @lang('Upload Folder')
                        <i class="las la-info-circle" title="@lang('Storage Folder (relative to root, e.g. tracking/screenshot; root may be public_html, /var/www/html, /home/www/ or /)')"></i>
                    </label>
                    <input type="text" name="upload_folder" class="form--control md-style" value="${ data.upload_folder || ""}" required>
                </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                    <label for="domain" class="form--label">
                        @lang('URL')
                        <i class="las la-info-circle" title="@lang('Provide the full public URL where uploaded files can be accessed. It should include the HTTP or HTTPS protocol, e.g. https://yourdomain.com or https://yourdomain.com/screenshotftp')"></i>
                    </label>
                    <input type="url" name="domain" class="form--control md-style" value="${ data.domain || ""}" required>
                </div>
                </div>`;
                $('#storageConfigInput').html(html);
                reloadRequiredTooltip();
            }

            s3Storage();

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

            $('#storageForm').on('submit', function() {
                $(".preloader").fadeIn();
            });



        })(jQuery);
    </script>
@endpush

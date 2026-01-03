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
                            <form action="{{ route('user.account.setting.organization.update') }}" method="POST"
                                enctype="multipart/form-data">
                                @csrf
                                <div class="row">
                                    <div class="col-lg-3">
                                        <div class="form-group image-uploader">
                                            <label for="profile_img__profile_img_upload__box" class="form--label d-block">
                                                @lang('Logo')
                                            </label>

                                            <div class="org-logo-wrapper">
                                                <div class="org-logo-avatar">
                                                    <div class="image-container">
                                                        <div class="image-container-loader">
                                                            <div class="spinner-border text-white" role="status">
                                                            </div>
                                                        </div>
                                                        <img class="image-holder" src="{{ $organization->logo_url }}"
                                                            alt="@lang('log')" />
                                                    </div>
                                                    <div class="org-logo-placeholder">
                                                        <span class="org-logo-initial">
                                                        </span>
                                                    </div>
                                                    <label for="profile_img__profile_img_upload__box"
                                                        class="org-logo-upload-btn">
                                                        <x-icons.upload />
                                                    </label>
                                                </div>


                                                <input type="file" name="logo"
                                                    id="profile_img__profile_img_upload__box" class="d-none"
                                                    accept=".png, .jpg, .jpeg">

                                                <small class="text--muted d-block mt-2">
                                                    @lang('Logo will be resized to') {{ getFilesize('organization') }}px
                                                </small>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-9">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="form--label">@lang('Organization Name')</label>
                                                    <input type="text" class="form--control md-style"
                                                        name="organization_name"
                                                        value="{{ old('organization_name', $organization->name) }}"
                                                        required maxlength="255"/>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="form--label">@lang('Timezone')</label>
                                                    <select class="form-control form--control sm-style select2" name="timezone" required>
                                                        @foreach ($timezones as $timezone)
                                                            <option @selected(old('timezone', $organization->timezone) == $timezone)
                                                                value="{{ $timezone }}">
                                                                {{ $timezone }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label class="form--label">@lang('Address')</label>
                                                    <textarea class="form--control" name="address" placeholder="Address" rows="3" maxlength="255">{{ old('address', $organization->address ?? '') }}</textarea>
                                                </div>
                                            </div>

                                            <div class="col-md-12">
                                                <button type="submit" class="btn btn--base w-100">
                                                    @lang('Save')
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>

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

            let oldLogo = $('.image-holder').attr('src');

            $('#profile_img__profile_img_upload__box').on('change', function(e) {
                oldLogo = $('.image-holder').attr('src');

                const file = e.target.files[0];
                if (!file) return;

                const allowed = ['image/jpeg', 'image/jpg', 'image/png'];

                if (!allowed.includes(file.type)) {
                    notify('error', "Only JPG, JPEG, PNG images are allowed.");
                    $(this).val('');
                    return;
                }

                // Preview image
                $('.image-holder').attr('src', URL.createObjectURL(file));
                $('.org-logo-avatar').addClass('disabled');

                // Prepare form data
                let formData = new FormData();
                formData.append('logo', file);
                formData.append('_token', '{{ csrf_token() }}');

                $.ajax({
                    url: '{{ route('user.account.setting.organization.upload.logo') }}',
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        notify('success', response);
                        $('.org-logo-avatar').removeClass('disabled');
                    },
                    error: function(xhr) {
                        notify('error', "Upload failed");
                        $('.org-logo-avatar').removeClass('disabled');
                        $('.image-holder').attr('src', oldLogo);
                    }
                });
            });
        })(jQuery);
    </script>
@endpush

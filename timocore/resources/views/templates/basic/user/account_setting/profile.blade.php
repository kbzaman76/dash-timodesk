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
                            <div class="profile-edit-wrapper">
                                <div class="form-group image-uploader">
                                    <label for="profile_img__profile_img_upload__box" class="form--label d-block">
                                        @lang('Image')
                                    </label>
                                    <div class="org-logo-wrapper">
                                        <div class="org-logo-avatar">
                                            <div class="image-container">
                                                <div class="image-container-loader">
                                                    <div class="spinner-border text-white" role="status">
                                                    </div>
                                                </div>
                                                <img class="image-holder" src="{{ auth()->user()->image_url }}" alt="@lang('Image')" />
                                            </div>
                                            <div class="org-logo-placeholder">
                                                <span class="org-logo-initial">
                                                </span>
                                            </div>
                                            <label for="profile_img__profile_img_upload__box" class="org-logo-upload-btn">
                                                <x-icons.upload />
                                            </label>
                                        </div>

                                        <input type="file" name="image" id="profile_img__profile_img_upload__box" class="d-none" accept=".png, .jpg, .jpeg">

                                        <small class="text--muted d-block mt-2">
                                            @lang('Image will be resized to') {{ getFilesize('userProfile') }}px
                                        </small>
                                    </div>
                                </div>
                                <div class="member-view__info-wrapper">
                                    <div class="member-view__info">
                                        <div class="member-view__info-title">
                                            <div class="flex-align gap-2">
                                                <span class="icon">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" color="currentColor">
                                                    <path d="M17 8.5C17 5.73858 14.7614 3.5 12 3.5C9.23858 3.5 7 5.73858 7 8.5C7 11.2614 9.23858 13.5 12 13.5C14.7614 13.5 17 11.2614 17 8.5Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                                    <path d="M19 20.5C19 16.634 15.866 13.5 12 13.5C8.13401 13.5 5 16.634 5 20.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                                    </svg>
                                                </span>
                                                <span class="text">Name</span>
                                            </div>
                                            <button type="button" class="member-view__info-edit" id="editName">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" color="currentColor">
                                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M18.7116 3.40901C17.833 2.53033 16.4083 2.53033 15.5296 3.40901L13.4997 5.43906L18.5604 10.4997L20.5903 8.46965C21.469 7.59098 21.469 6.16637 20.5903 5.28769L18.7116 3.40901ZM17.4997 11.5604L12.4391 6.49975L3.40899 15.5303C2.98705 15.9523 2.75 16.5246 2.75 17.1213V20.5C2.75 20.9142 3.08579 21.25 3.5 21.25H6.87868C7.47542 21.25 8.04773 21.0129 8.46969 20.591L17.4997 11.5604Z" fill="currentColor">
                                                    </path>
                                                </svg>
                                            </button>
                                        </div>
                                        <div class="member-view__info-desc">{{ $user->fullname ?? 'N/A' }}</div>
                                    </div>
                                    <div class="member-view__info">
                                        <div class="member-view__info-title d-flex align-items-center justify-content-between gap-2">
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="icon">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" color="currentColor">
                                                        <path d="M7 8.5L9.94202 10.2394C11.6572 11.2535 12.3428 11.2535 14.058 10.2394L17 8.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                                        <path
                                                            d="M2.01576 13.4756C2.08114 16.5411 2.11382 18.0739 3.24495 19.2093C4.37608 20.3448 5.95033 20.3843 9.09883 20.4634C11.0393 20.5122 12.9607 20.5122 14.9012 20.4634C18.0497 20.3843 19.6239 20.3448 20.755 19.2093C21.8862 18.0739 21.9189 16.5411 21.9842 13.4756C22.0053 12.4899 22.0053 11.51 21.9842 10.5244C21.9189 7.45883 21.8862 5.92606 20.755 4.79063C19.6239 3.6552 18.0497 3.61565 14.9012 3.53654C12.9607 3.48778 11.0393 3.48778 9.09882 3.53653C5.95033 3.61563 4.37608 3.65518 3.24495 4.79062C2.11382 5.92605 2.08113 7.45882 2.01576 10.5243C1.99474 11.51 1.99474 12.4899 2.01576 13.4756Z"
                                                            stroke="currentColor" stroke-width="2" stroke-linejoin="round">
                                                        </path>
                                                    </svg>
                                                </span>
                                                <span class="text">Email</span>
                                            </div>
                                            @php
                                                echo $user->emailStatusBadge;
                                            @endphp
                                        </div>
                                        <div class="member-view__info-desc">{{ $user->email }}</div>
                                    </div>
                                    <div class="member-view__info">
                                        <div class="member-view__info-title">
                                            <div class="flex-align gap-2">
                                                <span class="icon">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" color="currentColor">
                                                        <path d="M11.5 3.99973L16.4998 3.99923C17.6044 3.99911 18.5 4.89458 18.5 5.99922V9.08714C18.5 9.31462 18.3156 9.49902 18.0881 9.49902C18.0056 9.49902 17.9251 9.47426 17.8568 9.42793L16.3462 8.40255" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                                        <path d="M12 20H7C5.89543 20 5 19.1046 5 18V14.9109C5 14.684 5.18399 14.5 5.41095 14.5C5.494 14.5 5.57511 14.5252 5.64358 14.5722L7.15385 15.6093" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                                        <path
                                                            d="M9.00325 11V11.75C9.41746 11.75 9.75325 11.4142 9.75325 11H9.00325ZM2 11H1.25C1.25 11.4142 1.58579 11.75 2 11.75V11ZM9.00325 10.25H2V11.75H9.00325V10.25ZM2.75 11C2.75 9.48154 3.98161 8.25 5.50162 8.25V6.75C3.15384 6.75 1.25 8.65246 1.25 11H2.75ZM5.50162 8.25C7.02164 8.25 8.25325 9.48154 8.25325 11H9.75325C9.75325 8.65246 7.84941 6.75 5.50162 6.75V8.25ZM6.50162 3.75C6.50162 4.30196 6.05387 4.75 5.50081 4.75V6.25C6.88165 6.25 8.00162 5.13104 8.00162 3.75H6.50162ZM5.50081 4.75C4.94775 4.75 4.5 4.30196 4.5 3.75H3C3 5.13104 4.11998 6.25 5.50081 6.25V4.75ZM4.5 3.75C4.5 3.19804 4.94775 2.75 5.50081 2.75V1.25C4.11998 1.25 3 2.36896 3 3.75H4.5ZM5.50081 2.75C6.05387 2.75 6.50162 3.19804 6.50162 3.75H8.00162C8.00162 2.36896 6.88165 1.25 5.50081 1.25V2.75Z"
                                                            fill="currentColor"></path>
                                                        <path
                                                            d="M22.0032 22V22.75C22.4175 22.75 22.7532 22.4142 22.7532 22H22.0032ZM15 22H14.25C14.25 22.4142 14.5858 22.75 15 22.75V22ZM22.0032 21.25H15V22.75H22.0032V21.25ZM15.75 22C15.75 20.4815 16.9816 19.25 18.5016 19.25V17.75C16.1538 17.75 14.25 19.6525 14.25 22H15.75ZM18.5016 19.25C20.0216 19.25 21.2532 20.4815 21.2532 22H22.7532C22.7532 19.6525 20.8494 17.75 18.5016 17.75V19.25ZM19.5016 14.75C19.5016 15.302 19.0539 15.75 18.5008 15.75V17.25C19.8816 17.25 21.0016 16.131 21.0016 14.75H19.5016ZM18.5008 15.75C17.9477 15.75 17.5 15.302 17.5 14.75H16C16 16.131 17.12 17.25 18.5008 17.25V15.75ZM17.5 14.75C17.5 14.198 17.9477 13.75 18.5008 13.75V12.25C17.12 12.25 16 13.369 16 14.75H17.5ZM18.5008 13.75C19.0539 13.75 19.5016 14.198 19.5016 14.75H21.0016C21.0016 13.369 19.8816 12.25 18.5008 12.25V13.75Z"
                                                            fill="currentColor"></path>
                                                    </svg>
                                                </span>
                                                <span class="text">Role</span>
                                            </div>
                                        </div>
                                        <div class="member-view__info-desc">{{ $user->getRole() ?? 'N/A' }}</div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-9">

                                    <div class="row d-none">
                                        <div class="col-sm-12">
                                            <div class="form-group">
                                                <label class="form--label">
                                                    @lang('Name')
                                                    <i class="las la-edit" id="editName"></i>
                                                </label>
                                                <div id="showName">
                                                    {{ $user->fullname }}
                                                </div>
                                                <div id="nameInput" class="d-none d-flex">
                                                    <input type="text" class="form--control md-style" name="fullname" value="{{ $user->fullname }}" maxlength="40">
                                                    <button type="submit" class="btn btn--sm btn--base">Save</button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label class="form--label">@lang('Email')</label>
                                                {{ $user->email }}
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label class="form--label">@lang('Role')</label>
                                                {{ $user->roleText }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    

    <div class="modal fade custom--modal" id="NameEditModal" tabindex="-1" aria-labelledby="NameEditModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="NameEditModalLabel">
                        @lang('Edit Name')
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <form method="post" id="profileForm">
                    <div class="modal-body">
                        @csrf
                        <div class="form-group">
                            <label class="form--label">@lang('Name')</label>
                            <input type="text" name="fullname" class="form--control md-style" value="{{ auth()->user()->fullname }}" maxlength="40" required />
                        </div>
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
@endsection


@push('style')
    <style>
        .org-logo-wrapper {
            --square: 150px;
        }

        .profile-edit-wrapper{
            display: flex;
            align-items: center;
            gap: 16px;
        }

        @media (max-width: 767px) {
            .profile-edit-wrapper{
                flex-direction: column;
                align-items: flex-start;
            }

            .profile-edit-wrapper .member-view__info-wrapper{
                width: 100%;
            }
        }

        .profile-edit-wrapper .image-uploader{
            flex-shrink: 0;
        }

    </style>
@endpush

@push('script')
    <script>
        (function($) {
            let oldImage = $('.image-holder').attr('src');

            $('#profile_img__profile_img_upload__box').on('change', function(e) {
                oldImage = $('.image-holder').attr('src');

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
                formData.append('image', file);
                formData.append('_token', '{{ csrf_token() }}');

                $.ajax({
                    url: '{{ route('user.account.setting.upload.image') }}',
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
                        $('.image-holder').attr('src', oldImage);
                    }
                });
            });

            $('#editName').on('click', function() {
                $('#NameEditModal').modal('show');
                $('#profileForm')[0].reset();
            });


        })(jQuery);
    </script>
@endpush

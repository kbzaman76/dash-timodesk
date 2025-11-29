@extends('Template::layouts.master')
@section('content')
    <div class="row justify-content-center">
        <div class="col-xxl-10">
            <div class="card custom--card">
                <div class="card-body">
                    <form action="{{ route('ticket.store') }}" class="disableSubmission" method="post"
                        enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="form--label">@lang('Subject')</label>
                                    <input type="text" name="subject" value="{{ old('subject') }}"
                                        class="form--control md-style" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form--label">@lang('Department')</label>
                                    <select name="department" class="form--control select2 sm-style"
                                        data-minimum-results-for-search="-1" required>
                                        <option value="">Select One</option>
                                        <option value="General" @selected(old('department') == 'General')>@lang('General')</option>
                                        <option value="Sales" @selected(old('department') == 'Sales')>@lang('Sales')</option>
                                        <option value="Technical" @selected(old('department') == 'Technical')>@lang('Technical')</option>
                                        <option value="Billing" @selected(old('department') == 'Billing')>@lang('Billing')</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form--label">@lang('Priority')</label>
                                    <select name="priority" class="form--control select2 sm-style"
                                        data-minimum-results-for-search="-1" required>
                                        <option value="3" @selected(old('priority') == 3 || old('priority') == '')>@lang('High')</option>
                                        <option value="2" @selected(old('priority') == 2)>@lang('Medium')</option>
                                        <option value="1" @selected(old('priority') == 1)>@lang('Low')</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="form-group">
                                    <label class="form--label">@lang('Message')</label>
                                    <textarea name="message" id="inputMessage" rows="6" class="form--control md-style" required>{{ old('message') }}</textarea>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <input type="file" name="attachments[]" multiple class="form--control md-style">
                                <small class="text--muted d-block mt-2">
                                    Allowed File Extensions: .jpg, .jpeg, .png, .pdf, .doc, .docx. You can upload multiple
                                    attachments.
                                </small>
                            </div>
                            <div class="col-md-12">
                                <div class="text-end">
                                    <button class="btn btn--md btn--base" type="submit">
                                        @lang('Submit')
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        (function($) {
            "use strict";
            var fileAdded = 0;
            $('.addAttachment').on('click', function() {
                fileAdded++;
                if (fileAdded == 5) {
                    $(this).attr('disabled', true)
                }
                $(".fileUploadsContainer").append(`
                    <div class="col-lg-4 col-md-12 removeFileInput">
                        <div class="form-group">
                            <div class="input-group">
                                <input type="file" name="attachments[]" class="form-control md-style" accept=".jpeg,.jpg,.png,.pdf,.doc,.docx" required>
                                <button type="button" class="input-group-text removeFile bg--danger border--danger"><i class="fas fa-times"></i></button>
                            </div>
                        </div>
                    </div>
                `)
            });
            $(document).on('click', '.removeFile', function() {
                $('.addAttachment').removeAttr('disabled', true)
                fileAdded--;
                $(this).closest('.removeFileInput').remove();
            });
        })(jQuery);
    </script>
@endpush

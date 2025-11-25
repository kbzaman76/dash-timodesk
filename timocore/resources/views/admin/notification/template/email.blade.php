@extends('admin.layouts.app')
@push('topBar')
  @include('admin.notification.top_bar')
@endpush
@section('panel')
    @include('admin.notification.template.nav')
    @include('admin.notification.template.shortcodes')


    <form action="{{ route('admin.setting.notification.template.update',['email',$template->id]) }}" method="post">
        @csrf
        <div class="row">
            <div class="col-md-12">
                <div class="card mt-4">
                    <div class="card-header bg--primary">
                        <h5 class="card-title text-white">@lang('Email Template')</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label>@lang('Subject')</label>
                                    <input type="text" class="form-control" placeholder="@lang('Email subject')" name="subject" value="{{ $template->subject }}" required/>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>@lang('Status') <span class="text--danger">*</span></label>
                                    <input type="checkbox" data-height="46px" data-width="100%" data-onstyle="-success"
                                       data-offstyle="-danger" data-bs-toggle="toggle" data-on="@lang('Send Email')"
                                       data-off="@lang("Don't Send")" name="email_status"
                                       @if($template->email_status) checked @endif>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>@lang('Email Sent From - Name')</label>
                                    <input type="text" class="form-control" name="email_sent_from_name" value="{{ $template->email_sent_from_name }}">
                                    <small class="text-primary"><i><i class="las la-info-circle"></i> @lang('Make the field empty if you want to use global template\'s name as email sent from name.')</i></small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>@lang('Email Sent From - Email')</label>
                                    <input type="text" class="form-control" name="email_sent_from_address" value="{{ $template->email_sent_from_address }}">
                                    <small class="text-primary"><i><i class="las la-info-circle"></i> @lang('Make the field empty if you want to use global template\'s email as email sent from.')</i></small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>@lang('Email Heading')</label>
                                    <input type="text" class="form-control" name="email_heading" value="{{ $template->email_heading }}">
                                    <small class="text-primary"><i><i class="las la-info-circle"></i>Use <span class="short-codes">@php echo "{{". 'email_heading' ."}}"  @endphp</span>  short-code to show email header.</i></small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>@lang('Message') <span class="text--danger">*</span></label>
                                    <textarea name="email_body" rows="10" class="form-control emailTemplateEditor" id="htmlInput" placeholder="@lang('Your message using short-codes')">{{ $template->email_body }}</textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div id="previewContainer">
                                    <label>&nbsp;</label>
                                    <iframe id="iframePreview"></iframe>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn--primary w-100 h-45">@lang('Submit')</button>
                    </div>
                </div>
            </div>

        </div>
    </form>
@endsection


@push('breadcrumb-plugins')
    <x-back route="{{ route('admin.setting.notification.templates') }}" />
@endpush


@push('style')
    <style>
        #iframePreview {
            width: 100%;
            height: 400px;
            border: none;
        }

        .emailTemplateEditor {
            height: 400px;
        }
    </style>
@endpush

@push('script')
    <script>
        var iframe = document.getElementById('iframePreview');
        var emailTemplate = @php echo json_encode($emailTemplate) @endphp;

        $(".emailTemplateEditor").on('input', function() {
            var htmlContent = document.getElementById('htmlInput').value;
            htmlContent = emailTemplate.replace(/timeMessageBodyTemp/g, htmlContent);
            iframe.src = 'data:text/html;charset=utf-8,' + encodeURIComponent(htmlContent);
        }).trigger('input');
    </script>
@endpush

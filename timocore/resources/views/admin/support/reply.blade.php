@extends('admin.layouts.app')

@section('panel')
    <div class="row">
        <div class="col-lg-12">

            <div class="card">
                <div class="card-body ">

                    <h6 class="card-title  mb-4">
                        <div class="row align-items-center">
                            <div class="col-sm-8 col-md-6">
                                <h6>
                                    <span class="badge badge--info">{{ $ticket->department }}</span>
                                    <span class="fw-normal">|</span>
                                    @php echo $ticket->statusBadge; @endphp
                                    [@lang('Ticket#'){{ $ticket->ticket }}] {{ $ticket->subject }}
                                </h6>
                            </div>
                            <div class="col-sm-4  col-md-6 text-sm-end mt-sm-0 mt-3">
                                @if ($ticket->status != Status::TICKET_CLOSE)
                                    <button class="btn btn--danger btn-sm" type="button" data-bs-toggle="modal"
                                        data-bs-target="#DelModal">
                                        <i class="la la-times"></i> @lang('Close Ticket')
                                    </button>
                                @endif
                            </div>
                        </div>
                    </h6>



                    <form action="{{ route('admin.ticket.reply', $ticket->ticket) }}" enctype="multipart/form-data"
                        method="post" class="form-horizontal disableSubmission">
                        @csrf


                        <div class="row ">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <textarea class="form-control" name="message" rows="5" required id="inputMessage" placeholder="@lang('Enter reply here')"></textarea>
                                </div>
                            </div>
                            <div class="col-md-9">
                                <input type="file" name="attachments[]" multiple
                                                class="form-control">
                                            <small class="info text--muted d-block mt-2">
                                                Allowed File Extensions: .jpg, .jpeg, .png, .pdf, .doc, .docx. You can
                                                upload
                                                multiple attachments.
                                            </small>
                            </div>
                            <div class="col-md-3">
                                <button class="btn btn--primary w-100 my-2" type="submit" name="replayTicket"
                                    value="1"><i class="la la-fw la-lg la-reply"></i> @lang('Reply')
                                </button>
                            </div>
                        </div>

                    </form>


                    @foreach ($messages as $message)
                        @if ($message->admin_id == 0)
                            <div class="row border border--primary border-radius-3 my-3 mx-0">
                                <div class="col-md-3 border-end text-md-end text-start">
                                    <h5 class="my-3">{{ $message->fullname }}</h5>
                                    @if ($message->user_id)
                                        <p><a href="{{ route('admin.users.detail', $message->user_id) }}">{{ $message->user->email }}</a></p>
                                    @else
                                        <p>{{ $ticket->email }}</p>
                                    @endif

                                    @if ($ticket->organization)
                                        <p><a href="{{ route('admin.organization.detail', $ticket->organization_id) }}">{{ $ticket->organization->name }}</a></p>
                                    @endif

                                    <button class="btn btn--danger btn-sm my-3 confirmationBtn"
                                        data-question="@lang('Are you sure to delete this message?')"
                                        data-action="{{ route('admin.ticket.delete', $message->id) }}"><i
                                            class="la la-trash"></i> @lang('Delete')</button>
                                </div>

                                <div class="col-md-9">
                                    <p class="text-muted fw-bold my-3">
                                        @lang('Posted on') {{ showDateTime($message->created_at, 'l, dS F Y @ h:i a') }}</p>
                                    <p>{!! nl2br($message->message) !!}</p>
                                    @if ($message->attachments->count() > 0)
                                        <div class="my-3">
                                            @foreach ($message->attachments as $k => $image)
                                                <a href="{{ route('admin.ticket.download', encrypt($image->id)) }}"
                                                    class="me-2"><i class="fa-regular fa-file"></i> @lang('Attachment')
                                                    {{ ++$k }}</a>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @else
                            <div class="row border border-warning border-radius-3 my-3 mx-0 admin-bg-reply">

                                <div class="col-md-3 border-end text-md-end text-start">
                                    <h5 class="my-3">{{ $message?->admin?->name }}</h5>
                                    <p class="lead text-muted">@lang('Staff')</p>
                                    <button class="btn btn--danger btn-sm my-3 confirmationBtn"
                                        data-question="@lang('Are you sure to delete this message?')"
                                        data-action="{{ route('admin.ticket.delete', $message->id) }}"><i
                                            class="la la-trash"></i> @lang('Delete')</button>
                                </div>

                                <div class="col-md-9">
                                    <p class="text-muted fw-bold my-3">
                                        @lang('Posted on') {{ showDateTime($message->created_at, 'l, dS F Y @ h:i a') }}
                                    </p>
                                    <p>{!! nl2br($message->message) !!}</p>
                                    @if ($message->attachments->count() > 0)
                                        <div class="my-3">
                                            @foreach ($message->attachments as $k => $image)
                                                <a href="{{ route('admin.ticket.download', encrypt($image->id)) }}"
                                                    class="me-2"><i class="fa-regular fa-file"></i> @lang('Attachment')
                                                    {{ ++$k }} </a>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>

                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    </div>




    <div class="modal fade" id="DelModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"> @lang('Close Support Ticket!')</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <p>@lang('Are you want to close this support ticket?')</p>
                </div>
                <div class="modal-footer">
                    <form method="post" action="{{ route('admin.ticket.close', $ticket->ticket) }}">
                        @csrf
                        <input type="hidden" name="replayTicket" value="2">
                        <button type="button" class="btn btn--dark" data-bs-dismiss="modal"> @lang('No') </button>
                        <button type="submit" class="btn btn--primary"> @lang('Yes') </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <x-confirmation-modal />
@endsection




@push('breadcrumb-plugins')
    <x-back route="{{ route('admin.ticket.index') }}" />
@endpush

@extends('Template::layouts.' . $layout)
@section('content')
    <div class="support-ticket">
        @if ($layout == 'frontend')
            <div class="section-heading">
                <h2 class="section-heading__title">Ticket Details</h2>
            </div>
        @endif
        <div class="row justify-content-center">
            <div class="col-xxl-10">
                <div class="row gy-4">
                    <div class="col-lg-8">
                        <div class="card custom--card">
                            <div class="card-header">
                                <h5 class="card-title mb-0 d-flex flex-column gap-1">
                                    {{ $myTicket->subject }}
                                </h5>
                            </div>
                            <div class="card-body">
                                <form method="post" class="disableSubmission"
                                    action="{{ route('ticket.reply', $myTicket->ticket) }}{{ $myTicket->password ? '?access-key='.$myTicket->password : null }}" enctype="multipart/form-data">
                                    @csrf
                                    <div class="row justify-content-between">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <textarea name="message" class="form--control md-style" rows="4" required>{{ old('message') }}</textarea>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <input type="file" name="attachments[]" multiple
                                                    class="form--control md-style">
                                                <small class="info text--muted d-block mt-2">
                                                    Allowed File Extensions: .jpg, .jpeg, .png, .pdf, .doc, .docx. You can
                                                    upload
                                                    multiple attachments.
                                                </small>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="text-end">
                                                <button class="btn btn--md btn--base btn--reply" type="submit">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                        viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                        class="lucide lucide-reply-icon lucide-reply">
                                                        <path d="M20 18v-2a4 4 0 0 0-4-4H4" />
                                                        <path d="m9 17-5-5 5-5" />
                                                    </svg>
                                                    @lang('Reply')
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="card custom--card mt-4">
                            <div class="card-body">
                                @forelse($messages as $message)
                                    @if ($message->admin_id == 0)
                                        <div class="chat-box chat-box--user">
                                            <div class="chat-box__header d-flex align-items-center justify-content-between">
                                                <h6 class="chat-box__name">{{ $message->fullname }}</h6>
                                                <p class="chat-box__date">
                                                    {{ $myTicket->password? $message->created_at->format('Y-m-d h:i A') : showDateTime($message->created_at, 'Y-m-d h:i A') }}
                                                    {{ $myTicket->password? '(UTC)' : null }}
                                                </p>
                                            </div>
                                            <div class="chat-box__body">
                                                <p class="chat-box__text">{!! nl2br($message->message) !!}</p>
                                            </div>
                                            @if ($message->attachments->count() > 0)
                                                <div class="chat-box__footer">
                                                    <div class="chat-box__attachments">
                                                        @foreach ($message->attachments as $k => $image)
                                                            <a href="{{ route('ticket.download', encrypt($image->id)) }}">
                                                                <svg xmlns="http://www.w3.org/2000/svg" width="24"
                                                                    height="24" viewBox="0 0 24 24" fill="none"
                                                                    stroke="currentColor" stroke-width="2"
                                                                    stroke-linecap="round" stroke-linejoin="round"
                                                                    class="lucide lucide-paperclip-icon lucide-paperclip">
                                                                    <path
                                                                        d="m16 6-8.414 8.586a2 2 0 0 0 2.829 2.829l8.414-8.586a4 4 0 1 0-5.657-5.657l-8.379 8.551a6 6 0 1 0 8.485 8.485l8.379-8.551" />
                                                                </svg>
                                                                @lang('Attachment')
                                                                {{ ++$k }} </a>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    @else
                                        <div class="chat-box chat-box--admin">
                                            <div class="chat-box__header">
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <h6 class="chat-box__name">{{ $message->admin->name }} <span class="chat-box__member">(Timo Staff)</span></h6>
                                                    <p class="chat-box__date">
                                                        {{ $myTicket->password? $message->created_at->format('Y-m-d h:i A') : showDateTime($message->created_at, 'Y-m-d h:i A') }}
                                                        {{ $myTicket->password? '(UTC)' : null }}
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="chat-box__body">
                                                <p class="chat-box__text">{!! nl2br($message->message) !!}</p>
                                            </div>
                                            @if ($message->attachments->count() > 0)
                                                <div class="chat-box__footer">
                                                    <div class="chat-box__attachments">
                                                        @foreach ($message->attachments as $k => $image)
                                                            <a href="{{ route('ticket.download', encrypt($image->id)) }}">
                                                                <svg xmlns="http://www.w3.org/2000/svg" width="24"
                                                                    height="24" viewBox="0 0 24 24" fill="none"
                                                                    stroke="currentColor" stroke-width="2"
                                                                    stroke-linecap="round" stroke-linejoin="round"
                                                                    class="lucide lucide-paperclip-icon lucide-paperclip">
                                                                    <path
                                                                        d="m16 6-8.414 8.586a2 2 0 0 0 2.829 2.829l8.414-8.586a4 4 0 1 0-5.657-5.657l-8.379 8.551a6 6 0 1 0 8.485 8.485l8.379-8.551" />
                                                                </svg>
                                                                @lang('Attachment')
                                                                {{ ++$k }} </a>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                @empty
                                    <x-user.no-data title="No replies found here!" />
                                @endforelse
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="card custom--card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Ticket Information</h5>
                            </div>
                            <div class="card-body">
                                <ul class="ticket-info-list">
                                    <li class="ticket-info-list__item">
                                        <span class="label">Ticket ID</span>
                                        <span class="value">#{{ $myTicket->ticket }}</span>
                                    </li>
                                    <li class="ticket-info-list__item">
                                        <span class="label">Department</span>
                                        <span class="value">{{ $myTicket->department }}</span>
                                    </li>
                                    <li class="ticket-info-list__item">
                                        <span class="label">Status</span>
                                        @php echo $myTicket->statusBadge; @endphp
                                    </li>
                                    <li class="ticket-info-list__item">
                                        <span class="label">Priority</span>
                                        @php echo $myTicket->priorityBadge; @endphp
                                    </li>
                                    <li class="ticket-info-list__item">
                                        <span class="label">Opened At</span>
                                        <span
                                            class="value">{{ $myTicket->password? $myTicket->created_at->format('Y-m-d h:i A') : showDateTime($myTicket->created_at, 'Y-m-d h:i A') }} {{ $myTicket->password? '(UTC)' : null }}</span>
                                    </li>
                                    <li class="ticket-info-list__item">
                                        <span class="label">Last Reply</span>
                                        <span
                                            class="value">{{ $myTicket->password? $myTicket->last_reply->format('Y-m-d h:i A') : showDateTime($myTicket->last_reply, 'Y-m-d h:i A') }} {{ $myTicket->password? '(UTC)' : null }}</span>
                                    </li>
                                </ul>

                                @if ($myTicket->status != Status::TICKET_CLOSE)
                                    <button class="btn btn--md btn-outline--danger close-button confirmationBtn mt-3 w-100"
                                        type="button" data-question="@lang('Are you sure to close this ticket?')"
                                        data-action="{{ route('ticket.close', $myTicket->ticket) }}{{ $myTicket->password ? '?access-key='.$myTicket->password : null }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24"
                                            height="24" color="currentColor" fill="none">
                                            <path d="M18 6L6.00081 17.9992M17.9992 18L6 6.00085" stroke="currentColor"
                                                stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                        Close Support
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <x-confirmation-modal />
@endsection
@push('style')
    <style>
        .input-group-text:focus {
            box-shadow: none !important;
        }

        .reply-bg {
            background-color: #ffd96729
        }

        .empty-message img {
            width: 120px;
            margin-bottom: 15px;
        }

        .info {
            font-weight: 500;
            font-style: italic;
        }

        .close-button svg,
        .btn--reply svg {
            width: 1.2em;
            height: 1.2em;
        }
    </style>
@endpush

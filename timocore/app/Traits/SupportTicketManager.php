<?php

namespace App\Traits;

use App\Constants\Status;
use App\Models\AdminNotification;
use App\Models\SupportAttachment;
use App\Models\SupportMessage;
use App\Models\SupportTicket;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

trait SupportTicketManager {
    protected $files;
    protected $allowedExtension = ['jpg', 'png', 'jpeg', 'pdf', 'doc', 'docx'];
    protected $userType;
    protected $user   = null;
    protected $layout = null;
    protected $column;
    protected $apiRequest   = false;
    protected $organization = null;

    public function supportTicket() {
        $user = $this->user;
        if (!$user) {
            abort(404);
        }
        $pageTitle = "Support Tickets";
        $supports  = SupportTicket::searchable(['name', 'subject', 'ticket'])->where('organization_id', $this->organization->id)->orderBy('id', 'desc')->paginate(getPaginate());
        if ($this->apiRequest) {
            $notify[] = 'Support ticket data';
            return responseSuccess('tickets', $notify, [
                'tickets' => $supports,
            ]);
        }
        return view("Template::$this->userType" . '.support.index', compact('supports', 'pageTitle'));
    }

    public function openSupportTicket() {
        $user = $this->user;

        if (!$user) {
            return to_route('user.home');
        }
        $pageTitle = "Open Ticket";
        return view("Template::$this->userType" . '.support.create', compact('pageTitle', 'user'));
    }

    public function storeSupportTicket(Request $request) {
        $user = $this->user;

        if (!$user) {
            return to_route('user.home');
        }
        $ticket  = new SupportTicket();
        $message = new SupportMessage();

        $validationRule = $this->validation($request);
        if ($this->apiRequest) {
            $validator = Validator::make($request->all(), $validationRule);
            if ($validator->fails()) {
                return responseError('validation_error', $validator->errors());
            }
        } else {
            $request->validate($validationRule);
        }

        $column                  = $this->column;
        $user                    = $this->user;
        $ticket->$column         = $user->id;
        $ticket->organization_id = $this->organization->id ?? 0;
        $ticket->ticket          = rand(100000, 999999);
        $ticket->name            = $user->fullname;
        $ticket->email           = $user->email;
        $ticket->subject         = $request->subject;
        $ticket->last_reply      = Carbon::now();
        $ticket->status          = Status::TICKET_OPEN;
        $ticket->priority        = $request->priority;
        $ticket->department      = $request->department;
        $ticket->save();

        $message->support_ticket_id = $ticket->id;
        $message->user_id           = $user->id;
        $message->message           = strip_tags($request->message);
        $message->save();

        $adminNotification            = new AdminNotification();
        $adminNotification->$column   = $user->id;
        $adminNotification->title     = 'New support ticket has opened';
        $adminNotification->click_url = urlPath('admin.ticket.view', $ticket->id);
        $adminNotification->save();

        $fileUploadError = null;
        if ($request->hasFile('attachments')) {
            $uploadAttachments = $this->storeSupportAttachments($message->id, $this->organization);
            if ($uploadAttachments != 200) {
                $fileUploadError = 'File could not upload';
            }
        }

        if ($this->apiRequest) {
            $notify[] = 'Ticket opened successfully';
            if ($fileUploadError) {
                $notify[] = $fileUploadError;
            }
            return responseSuccess('ticket_open', $notify, [
                'ticket' => $ticket,
            ]);
        }

        $notify[] = ['success', 'Ticket opened successfully!'];
        if ($fileUploadError) {
            $notify[] = ['warning', $fileUploadError];
        }

        return to_route($this->redirectLink, $ticket->ticket)->withNotify($notify);
    }

    public function viewTicket($ticket) {
        $user      = $this->user;
        $column    = $this->column;
        $pageTitle = "View Ticket";
        $organizationId    = 0;
        $layout    = $this->layout;
        $password = request('access-key');

        $myTicket = SupportTicket::where('ticket', $ticket)->orderBy('id', 'desc')->first();

        if (!$myTicket) {
            if ($this->apiRequest) {
                $notify[] = 'Ticket not found';
                return responseError('ticket_not_found', $notify);
            }
            abort(404);
        }

        if ($myTicket->organization_id > 0) {
            if ($this->organization) {
                $organizationId = $this->organization->id;
            } else {
                if ($this->apiRequest) {
                    $notify[] = 'Unauthorized user';
                    return responseError('unauthorized_user', $notify);
                }
                return to_route($this->userType . '.login');
            }
        } elseif(!$password) {
            abort(404);
        }

        $myTicket = SupportTicket::where('ticket', $ticket)->where('organization_id', $organizationId)->when($password, function($query) use($password) {
            $query->where('password', $password);
        })->orderBy('id', 'desc')->first();
        if (!$myTicket) {
            if ($this->apiRequest) {
                $notify[] = 'Ticket not found';
                return responseError('ticket_not_found', $notify);
            }
            abort(404);
        }
        $messages = SupportMessage::where('support_ticket_id', $myTicket->id)->with('ticket', 'admin', 'attachments.fileStorage')->orderBy('id', 'desc')->get();
        if ($this->apiRequest) {
            $notify[] = 'Support ticket view';
            return responseSuccess('ticket_view', $notify, [
                'my_ticket' => $myTicket,
                'messages'  => $messages,
            ]);
        }

        return view("Template::$this->userType" . '.support.view', compact('myTicket', 'messages', 'pageTitle', 'user', 'layout'));
    }

    public function replyTicket(Request $request, $id) {
        $user   = $this->user;
        $userId = 0;
        $organizationId = 0;
        if ($user) {
            $userId = $user->id;
        }
        if ($this->organization) {
            $organizationId = $this->organization->id;
        }
        $ticket = SupportTicket::where('id', $id)->first();
        if (!$ticket) {
            if ($this->apiRequest) {
                $notify[] = 'Ticket not found';
                return responseError('ticket_not_found', $notify);
            }
            abort(404);
        }
        if (($this->userType == 'user') && ($organizationId != $ticket->organization_id)) {
            if ($this->apiRequest) {
                $notify[] = 'Unauthorized user';
                return responseError('unauthorized', $notify);
            }
            abort(404);
        }
        $message = new SupportMessage();

        $request->merge(['ticket_reply' => 1]);

        $validationRule = $this->validation($request);
        if ($this->apiRequest) {
            $validator = Validator::make($request->all(), $validationRule);
            if ($validator->fails()) {
                return responseError('validation_error', $validator->errors());
            }
        } else {
            $request->validate($validationRule);
        }

        $ticket->status     = $this->userType != 'admin' ? Status::TICKET_REPLY : Status::TICKET_ANSWER;
        $ticket->last_reply = Carbon::now();
        $ticket->save();
        $message->support_ticket_id = $ticket->id;
        if ($this->userType == 'admin') {
            $message->admin_id = $user->id;
        }

        $message->user_id = $userId;
        $message->message = strip_tags($request->message);
        $message->save();

        $fileUploadError = null;
        if ($request->hasFile('attachments')) {
            $uploadAttachments = $this->storeSupportAttachments($message->id, ($ticket?->organization ?? null));
            if ($uploadAttachments != 200) {
                $fileUploadError = 'File could not upload';
            }
        }

        if ($this->userType == 'admin') {
            $createLog = false;
            $user      = $ticket;
            $sendVia   = ['email'];
            if ($ticket->user_id != 0) {
                $createLog = true;
                $user      = $ticket->user;
                $sendVia   = null;
            }

            $route = $ticket->password? route('ticket.view', $ticket->ticket).'?access-key='.$ticket->password : route('ticket.view', $ticket->ticket);
            notify($user, 'ADMIN_SUPPORT_REPLY', [
                'ticket_id'      => $ticket->ticket,
                'ticket_subject' => $ticket->subject,
                'reply'          => $request->message,
                'link'           => $route,
            ], $sendVia, $createLog);
        }

        $message->load('attachments');

        if ($this->apiRequest) {
            $notify[] = 'Ticket replied successfully';
            if ($fileUploadError) {
                $notify[] = $fileUploadError;
            }
            return responseSuccess('ticket_replied', $notify, [
                'ticket'  => $ticket,
                'message' => $message,
            ]);
        }

        $notify[] = ['success', 'Support ticket replied successfully!'];
        if ($fileUploadError) {
            $notify[] = ['warning', $fileUploadError];
        }

        return back()->withNotify($notify);
    }

    protected function storeSupportAttachments($messageId, $organization = null) {
        $error = null;
        try {
            foreach ($this->files as $file) {
                $imageExtensions = ['jpg', 'jpeg', 'png'];
                $extension       = strtolower($file->getClientOriginalExtension());
                if (in_array($extension, $imageExtensions)) {
                    $file = toWebpFile($file);
                }

                $location               = 'tickets/' . ($organization->uid ?? 'public');
                [$fileName, $storageId] = uploadPermanentImage($file, $location);

                // Save attachment info
                if ($fileName && $storageId) {
                    $attachment                     = new SupportAttachment();
                    $attachment->support_message_id = $messageId;
                    $attachment->file_storage_id    = $storageId;
                    $attachment->attachment         = $fileName;
                    $attachment->save();
                } else {
                    $error = "File could not upload";
                }
            }

        } catch (\Exception $exp) {
            return 'File could not upload';
        }

        if ($error) {
            return $error;
        }

        return 200;
    }

    protected function validation($request) {
        $this->files = $request->file('attachments');

        return [
            'attachments' => [
                function ($attribute, $value, $fail) {
                    foreach ($this->files as $file) {
                        $ext = strtolower($file->getClientOriginalExtension());
                        if (!in_array($ext, $this->allowedExtension)) {
                            return $fail("Only png, jpg, jpeg, pdf, doc, docx files are allowed");
                        }
                    }
                },
            ],
            'subject'     => 'required_without:ticket_reply|max:255',
            'priority'    => 'required_without:ticket_reply|in:1,2,3',
            'department'  => 'required_without:ticket_reply|in:General,Sales,Technical,Billing',
            'message'     => 'required',
        ];
    }

    private function convertToMb($value) {
        $unit  = strtolower(substr($value, -1));
        $value = substr($value, 0, -1);
        if ($unit == 'k') {
            return $value / 1024;
        }
        if ($unit == 'm') {
            return $value;
        }
        if ($unit == 'g') {
            return $value * 1024;
        }
        return $value;
    }

    public function closeTicket($id) {
        $user   = $this->user;
        $ticket = SupportTicket::where('id', $id)->first();
        if (!$ticket) {
            if ($this->apiRequest) {
                $notify[] = 'Ticket not found';
                return responseError('ticket_not_found', $notify);
            }
            abort(404);
        }
        if ($this->userType != 'admin') {
            $column = $this->column;
            if ($user->id != $ticket->$column) {
                if ($this->apiRequest) {
                    $notify[] = 'Unauthorized user';
                    return responseError('unauthorized', $notify);
                }
                abort(403);
            }
        }

        $ticket->status = Status::TICKET_CLOSE;
        $ticket->save();

        if ($this->apiRequest) {
            $notify[] = 'Ticket closed successfully';
            return responseSuccess('ticket_closed', $notify);
        }

        $notify[] = ['success', 'Support ticket closed successfully!'];
        return back()->withNotify($notify);
    }

    public function ticketDownload($attachmentId) {
        $attachment = SupportAttachment::find(decrypt($attachmentId));
        if (!$attachment) {
            if ($this->apiRequest) {
                $notify[] = 'Attachment not found';
                return responseError('attachment_not_found', $notify);
            }
            abort(404);
        }

        $fileUrl = $attachment->url;
        $ext     = strtolower(pathinfo($fileUrl, PATHINFO_EXTENSION));
        $title   = slug($attachment->supportMessage->ticket->subject) . '.' . $ext;

        $fileContent = @file_get_contents($fileUrl);

        if (!$fileContent) {
            if ($this->apiRequest) {
                $notify[] = 'Attachment not found';
                return responseError('attachment_not_found', $notify);
            }
            abort(404);
        }

        $mime = $this->getMimeByExtension($ext);

        return response()->streamDownload(function () use ($fileContent) {
            echo $fileContent;
        }, $title, [
            'Content-Type'        => $mime,
            'Content-Disposition' => 'attachment; filename="' . $title . '"',
        ]);
    }

    protected function getMimeByExtension($ext)
    {
        $mimes = [
            'webp' => 'image/webp',
            'pdf'  => 'application/pdf',
            'doc'  => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ];

        return $mimes[$ext] ?? 'application/octet-stream';
    }

}

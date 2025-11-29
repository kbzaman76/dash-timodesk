<?php

namespace App\Http\Controllers\Api;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use App\Models\SupportMessage;
use App\Models\SupportTicket;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ContactMessageController extends Controller {
    public function store(Request $request) {
        $validation = Validator::make($request->all(), [
            'name'    => 'required',
            'email'   => 'required|email',
            'subject' => 'required|string|max:255',
            'message' => 'required',
        ]);

        if ($validation->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Something went wrong',
                'errors'  => $validation->errors(),
            ]);
        }

        $random   = getNumber();

        $ticket                  = new SupportTicket();
        $ticket->user_id         = 0;
        $ticket->organization_id = 0;
        $ticket->name            = $request->name;
        $ticket->email           = strtolower($request->email);
        $ticket->password        = getTrx(8);
        $ticket->priority        = Status::PRIORITY_MEDIUM;

        $ticket->ticket     = $random;
        $ticket->subject    = $request->subject;
        $ticket->last_reply = Carbon::now();
        $ticket->status     = Status::TICKET_OPEN;
        $ticket->department     = 'General';
        $ticket->save();

        $adminNotification            = new AdminNotification();
        $adminNotification->user_id   = 0;
        $adminNotification->title     = 'A new contact message has been submitted';
        $adminNotification->click_url = urlPath('admin.ticket.view', $ticket->id);
        $adminNotification->save();

        $message                    = new SupportMessage();
        $message->support_ticket_id = $ticket->id;
        $message->user_id           = 0;
        $message->message           = strip_tags($request->message);
        $message->save();

        notify($ticket, 'NEW_SUPPORT_TICKET', [
            'ticket_id'      => $ticket->ticket,
            'ticket_subject' => $ticket->subject,
            'link'           => route('ticket.view', $ticket->ticket).'?access-key='.$ticket->password
        ], ['email']);

        return response()->json([
            'status'  => 'success',
            'message' => 'Message sent successfully',
        ]);
    }
}

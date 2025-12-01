<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportMessage;
use App\Models\SupportTicket;
use App\Traits\SupportTicketManager;

class SupportTicketController extends Controller {
    use SupportTicketManager;

    public function __construct() {
        parent::__construct();
        $this->userType = 'admin';
        $this->column   = 'admin_id';
        $this->user     = auth()->guard('admin')->user();
        $this->isPublic = 'admin';
    }

    public function tickets() {
        $pageTitle = 'Support Tickets';
        $items     = $this->getTicketQuery()->paginate(getPaginate());
        return view('admin.support.tickets', compact('items', 'pageTitle'));
    }

    public function pendingTicket() {
        $pageTitle = 'Pending Tickets';
        $items     = $this->getTicketQuery()->pending()->paginate(getPaginate());
        return view('admin.support.tickets', compact('items', 'pageTitle'));
    }

    public function closedTicket() {
        $pageTitle = 'Closed Tickets';
        $items     = $this->getTicketQuery()->closed()->paginate(getPaginate());
        return view('admin.support.tickets', compact('items', 'pageTitle'));
    }

    public function answeredTicket() {
        $pageTitle = 'Answered Tickets';
        $items     = $this->getTicketQuery()->answered()->paginate(getPaginate());
        return view('admin.support.tickets', compact('items', 'pageTitle'));
    }

    private function getTicketQuery() {
        return SupportTicket::searchable(['name', 'subject', 'ticket'])
            ->when(request('department'), function ($q) {
                $q->where('department', request('department'));
            })
            ->orderBy('id', 'desc')
            ->with('user');
    }

    public function ticketReply($id) {
        $ticket    = SupportTicket::with('user')->where('id', $id)->firstOrFail();
        $pageTitle = 'Reply Ticket';
        $messages  = SupportMessage::with('ticket', 'admin', 'attachments')->where('support_ticket_id', $ticket->id)->orderBy('id', 'desc')->get();
        return view('admin.support.reply', compact('ticket', 'messages', 'pageTitle'));
    }

    public function ticketDelete($id) {
        $message = SupportMessage::findOrFail($id);
        
        if ($message->attachments()->count() > 0) {
            foreach ($message->attachments as $attachment) {
                deleteStorageFile($attachment->attachment, $attachment->file_storage_id);
            }
        }
        $message->delete();
        $notify[] = ['success', "Support ticket deleted successfully"];
        return back()->withNotify($notify);

    }

}

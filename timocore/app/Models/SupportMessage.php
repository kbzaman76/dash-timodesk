<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class SupportMessage extends Model
{

    public function ticket(){
        return $this->belongsTo(SupportTicket::class, 'support_ticket_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function username(): Attribute
    {
        return new Attribute(
            get:fn () => $this->email,
        );
    }

    public function fullname(): Attribute
    {
        return new Attribute(
            get:fn () => $this->user ? $this->user->fullname : $this->ticket->name,
        );
    }

    public function admin(){
        return $this->belongsTo(Admin::class, 'admin_id', 'id');
    }

    public function attachments()
    {
        return $this->hasMany(SupportAttachment::class,'support_message_id','id');
    }
}

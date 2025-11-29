<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class SupportAttachment extends Model
{
    protected $appends = ['encrypted_id'];

    public function supportMessage()
    {
        return $this->belongsTo(SupportMessage::class,'support_message_id');
    }

    public function fileStorage()
    {
        return $this->belongsTo(FileStorage::class);
    }

    public function getUrlAttribute()
    {
        return $this->fileStorage?->base_path . '/' . $this->attachment;
    }

    public function encryptedId(): Attribute
    {
        return new Attribute(
            get: fn () => encrypt($this->attributes['id']),
        );
    }
}

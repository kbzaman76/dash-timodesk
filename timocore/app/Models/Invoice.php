<?php

namespace App\Models;

use App\Constants\Status;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;


class Invoice extends Model
{
    public function scopePaid($query)
    {
        return $query->where('status', Status::INVOICE_PAID);
    }

    public function scopeUnpaid($query)
    {
        return $query->where('status', Status::INVOICE_UNPAID);
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', Status::INVOICE_CANCELLED);
    }

    public function invoiceItems()
    {
        return $this->hasMany(InvoiceItem::class);
    }
    
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function statusBadge(): Attribute
    {
        return new Attribute(
            get: fn () => $this->badgeData(),
        );
    }

    public function badgeData()
    {
        $html = '';
        if ($this->status == Status::INVOICE_PAID) {
            $html = '<span class="badge badge--success">' . trans('PAID') . '</span>';
        } elseif ($this->status == Status::INVOICE_UNPAID) {
            $html = '<span class="badge badge--danger">' . trans('UNPAID') . '</span>';
        } else {
            $html = '<span class="badge badge--dark">' . trans('CANCELLED') . '</span>';
        }
        return $html;
    }

}

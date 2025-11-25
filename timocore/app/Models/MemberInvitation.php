<?php

namespace App\Models;

use App\Constants\Status;
use App\Traits\GlobalStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class MemberInvitation extends Model
{
    use GlobalStatus;

    public function organization() {
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
        if ($this->status == Status::ENABLE) {
            $html = '<span class="badge badge--success">' . trans('Accepted') . '</span>';
        } else {
            $html = '<span class="badge badge--warning">' . trans('Pending') . '</span>';
        }
        return $html;
    }

    public function roleText(): Attribute {
        return Attribute::get(function () {
            if ($this->role == Status::ORGANIZER) {
                return trans('Organizer');
            } elseif ($this->role == Status::MANAGER) {
                return trans('Manager');
            } else {
                return trans('Staff');
            }
        });
    }
}

<?php

namespace App\Models;

use App\Constants\Status;
use App\Traits\GlobalStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class FileStorage extends Model
{
    use GlobalStatus;

    protected $casts = [
        'config' => 'object',
    ];

    public function organizations() {
        return $this->hasMany(Organization::class);
    }

    public function screenshots() {
        return $this->hasMany(Screenshot::class);
    }

    public function scopeVerified($query)
    {
        $query->where('verified', Status::YES);
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
        if ($this->status == Status::ACTIVE_STORAGE) {
            $html = '<span class="badge badge--success">' . trans('Enabled') . '</span>';
        } elseif ($this->status == Status::BACKUP_STORAGE) {
            $html = '<span class="badge badge--info">' . trans('Backup') . '</span>';
        } elseif ($this->status == Status::PERMANENT_STORAGE) {
            $html = '<span class="badge badge--primary">' . trans('Permanent') . '</span>';
        } else {
            $html = '<span class="badge badge--warning">' . trans('Disabled') . '</span>';
        }
        return $html;
    }


    public function storageTypeBadge(): Attribute
    {
        return new Attribute(
            get: fn () => $this->typebadgeData(),
        );
    }

    public function typebadgeData()
    {
        $html = '';
        if ($this->storage_type == Status::S3_STORAGE) {
            $html = '<span class="badge badge--success">' . trans('s3') . '</span>';
        } else {
            $html = '<span class="badge badge--info">' . trans('FTP') . '</span>';
        }
        return $html;
    }

    public function verifiedBadge(): Attribute
    {
        return new Attribute(
            get: fn () => $this->verifybadgeData(),
        );
    }

    public function verifybadgeData()
    {
        $html = '';
        if ($this->verified == Status::YES) {
            $html = '<span class="badge badge--success">' . trans('Verified') . '</span>';
        } else {
            $html = '<span class="badge badge--danger">
            ' . trans('Unverified') . '</span>';
        }
        return $html;
    }
}

<?php

namespace App\Models;

use Carbon\Carbon;
use App\Constants\Status;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Organization extends Model
{
    public function fileStorage()
    {
        return $this->belongsTo(FileStorage::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);

    }

    public function users()
    {
        return $this->hasMany(User::class);

    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class)->orderBy('id', 'desc');
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function suspendBadge(): Attribute
    {
        return new Attribute(
            get: fn() => $this->badgeData(),
        );
    }

    public function badgeData()
    {
        $html = '';
        if ($this->is_suspend == Status::ENABLE) {
            $html = '<span class="badge badge--danger">' . trans('Yes') . '</span>';
        } else {
            $html = '<span class="badge badge--success">' . trans('No') . '</span>';
        }
        return $html;
    }

    // SCOPES
    public function scopeActive($query)
    {
        return $query->where('is_suspend', Status::NO);
    }

    public function scopeSuspend($query)
    {
        return $query->where('is_suspend', Status::YES);
    }

    public function scopePaid($query)
    {
        return $query->whereHas('transactions');
    }

    public function scopeUnpaid($query)
    {
        return $query->whereDoesntHave('transactions');
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function tracks()
    {
        return $this->hasMany(Track::class);
    }

    public function screenshots()
    {
        return $this->hasMany(Screenshot::class);
    }

    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    public function referrer() {
        return $this->belongsTo(Organization::class,'referred_by');
    }

    public function getLogoUrlAttribute()
    {
        if (!$this->logo) {
            return asset('assets/images/avatar_org.png');
        }

        try {
            [$storageId, $path] = explode('|', $this->logo, 2);
        } catch (\Exception $e) {
            return asset('assets/images/avatar_org.png');
        }

        $storage = FileStorage::find($storageId);

        if (!$storage) {
            return asset('assets/images/avatar_org.png');
        }

        return rtrim($storage->base_path, '/') . '/' . ltrim($path, '/');
    }

    public function getCurrentBillingStartDateAttribute() {
        if($this->trial_end_at > now()) {
            return $this->created_at;
        }

        return Carbon::parse($this->next_invoice_date)->subMonth();

    }

}

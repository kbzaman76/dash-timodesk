<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $appends = ['total_work_time'];

    // public function getTotalWorkTimeAttribute()
    // {
    //     return $this->tracks()->sum('time_in_seconds');
    // }

    public function getTotalWorkTimeAttribute()
    {
        if (request()->has('today')) {
            return $this->tracks()
                ->whereDateOrg('started_at')
                ->sum('time_in_seconds');
        }

        return $this->tracks()->sum('time_in_seconds');
    }

    public function tracks()
    {
        return $this->hasMany(Track::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function screenshots()
    {
        return $this->belongsTo(Screenshot::class);
    }
}

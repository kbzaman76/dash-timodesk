<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Screenshot extends Model
{
    protected $guarded = [];

    public function fileStorage()
    {
        return $this->belongsTo(FileStorage::class, 'file_storage_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function track()
    {
        return $this->belongsTo(Track::class);
    }

    public function scopeOrg($query) {
        return $query->where('organization_id', auth()->user()->organization_id);
    }

    public function getUrlAttribute()
    {
        return $this->fileStorage?->base_path . '/' . $this->src;
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $casts = [
        'color' => 'object'
    ];

    public function idleTimes()
    {
        return $this->hasMany(IdleTime::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'project_user')->withTimestamps();
    }

    public function tracks()
    {
        return $this->hasMany(Track::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function getIconUrlAttribute()
    {
        if (!$this->icon) {
            return null;
        }

        try {
            [$storageId, $path] = explode('|', $this->icon, 2);
        } catch (\Exception $e) {
            return null;
        }

        $storage = FileStorage::find($storageId);

        if (!$storage) {
            return null;
        }

        return rtrim($storage->base_path, '/') . '/' . ltrim($path, '/');
    }
}

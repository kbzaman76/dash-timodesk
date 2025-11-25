<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Track extends Model
{
    protected $guarded = [];

    public const NO_TRACK_FOUND = 'no_track_found';
    public const STORED_TRACK   = 'stored_track';
    public const UPDATED_TRACK  = 'updated_track';

    protected $casts = [
        'apps' => 'array'
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function activeStatusBadge(): Attribute
    {
        return new Attribute(function () {
            return '<span class="badge bg-success">' . __('Active') . '</span>';
        });
    }

    public function screenshots(){
        return $this->hasMany(Screenshot::class);
    }
}

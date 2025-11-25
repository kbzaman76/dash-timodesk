<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SummaryMailQueue extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
}

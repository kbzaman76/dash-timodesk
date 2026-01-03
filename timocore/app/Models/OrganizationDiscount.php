<?php

namespace App\Models;

use App\Traits\GlobalStatus;
use Illuminate\Database\Eloquent\Model;

class OrganizationDiscount extends Model {
    use GlobalStatus;

    public function coupon() {
        return $this->belongsTo(Coupon::class);
    }
}

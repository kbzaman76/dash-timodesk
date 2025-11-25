<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

class UserController extends Controller
{
    public function utilSettings(){
        return responseSuccess('util_settings', [], [
            'user' => auth()->user(),
            'tracking_status' => auth()->user()->tracking_status,
            'screenshot_time' => gs('screenshot_time'),
            'idle_time' => gs('idle_time'),
        ]);
    }
}

<?php

namespace App\Http\Controllers;

abstract class Controller
{
    public function __construct()
    {
    }

    public static function middleware()
    {
        return [];
    }

}

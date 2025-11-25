<?php

use Illuminate\Support\Facades\Route;

Route::post('stripe-v3', 'StripeV3\ProcessController@ipn')->name('StripeV3');
Route::any('bkash', 'BKash\ProcessController@ipn')->name('BKash');

Route::post('stripe', 'Stripe\ProcessController@ipn')->name('Stripe');

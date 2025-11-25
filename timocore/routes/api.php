<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::namespace('Api')->name('api.')->group(function(){

	Route::namespace('Auth')->group(function(){
        Route::controller('LoginController')->group(function(){
            // 10 minutes 3 reqeusts
            // Route::post('login', 'login')->middleware('throttle:3,10');
            Route::post('login', 'login');
            Route::post('check-token', 'checkToken');
        });
	});

    Route::middleware(['auth:sanctum', 'token.proof'])->group(function () {

        Route::middleware(['check.status'])->group(function () {

            Route::middleware('registration.complete')->group(function(){
                Route::get('dashboard',function(){
                    return auth()->user();
                });

                Route::controller('TaskController')->prefix('tasks')->group(function () {
                    Route::get('/my', 'myTasks');
                    Route::post('/save/{id?}', 'saveTask');
                });

                Route::controller('ProjectController')->prefix('projects')->group(function () {
                    Route::get('/', 'projects');
                });

                Route::prefix('tracks')->controller('TrackController')->group(function () {
                    Route::post('/screenshot', 'uploadScreenshot');
                    Route::post('/store-idle-track', 'storeIdleTrack');
                    Route::post('/', 'storeTrack');
                    Route::post('/{id}/update', 'updateTrack');
                });


                Route::controller('UserController')->group(function(){
                    Route::get('util/settings', 'utilSettings');
                });
            });
        });

        Route::get('logout', 'Auth\LoginController@logout');
    });
});

<?php

use Illuminate\Http\Request;
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

    Route::get('version',function(){
        return gs('app_version');
    });

	Route::namespace('Auth')->group(function(){
        Route::controller('LoginController')->group(function(){
            Route::post('login', 'login');
            Route::post('check-token', 'checkToken');
        });
	});

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/me', function (Request $request) {
            return [
                'org_id' => $request->user()->organization_id,
                'id' => $request->user()->id,
                'email' => $request->user()->email,
                'role' => $request->user()->role,
                'app_version' => $request->header('X-App-Version'),
            ];
        });

        Route::middleware('token.proof')->group(function() {
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

    Route::controller('ContactMessageController')->group(function(){
        Route::post('contact/store', 'store');
    });
});

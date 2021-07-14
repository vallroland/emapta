<?php

use App\Http\Controllers\EventApiController;
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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
}); 

 
Route::get( '/events', [EventApiController::class, 'index']); 
Route::post( '/events', [EventApiController::class, 'store']);
Route::get( '/events/instance/', [EventApiController::class, 'get_instance']);
Route::put( '/events/{event}', [EventApiController::class, 'update']);
Route::delete( '/events/{event}', [EventApiController::class, 'destroy']);





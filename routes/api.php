<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\RegisterController;
use App\Http\Controllers\API\StaffController;
use App\Http\Controllers\API\FeedbackController;
use App\Http\Controllers\API\MessageController;

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

Route::group(['prefix' => 'auth'], function () {
    Route::post('register', 'App\Http\Controllers\API\RegisterController@register');
    Route::post('login', 'App\Http\Controllers\API\RegisterController@login');
    Route::get('check', 'App\Http\Controllers\API\RegisterController@check');
    Route::get('logout', 'App\Http\Controllers\API\RegisterController@logout');
});

Route::group(['prefix' => 'messages', 'middleware' => ['auth:api']], function(){
    Route::get('/','App\Http\Controllers\API\MessageController@index');
    Route::get('/all','App\Http\Controllers\API\MessageController@allChat')->middleware(['scope:staff-scope']);
    Route::post('/send','App\Http\Controllers\API\MessageController@store');
});

Route::group(['prefix' => 'feedbacks', 'middleware' => ['auth:api']], function(){
    Route::post('/create','App\Http\Controllers\API\FeedbackController@store')->middleware(['scope:customer-scope']);
    Route::post('/report','App\Http\Controllers\API\FeedbackController@report')->middleware(['scope:customer-scope']);
});

Route::group(['prefix' => 'staffs', 'middleware' => ['auth:api', 'scope:staff-scope']], function(){
    Route::get('/see-customers-data','App\Http\Controllers\API\StaffController@seeAllCustomerData');
    Route::post('/delete-customers','App\Http\Controllers\API\StaffController@deleteCustomer');
});

Route::group(['prefix' => 'customers', 'middleware' => ['auth:api', 'scope:customer-scope']], function(){
    Route::get('/see-customers-data','App\Http\Controllers\API\StaffController@seeAllCustomerData');
    Route::post('/delete-customers','App\Http\Controllers\API\StaffController@deleteCustomer');
});

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::namespace('Walmart')->group(function () {
    Route::post('/authenticate', 'AuthController@authenticate');
    Route::post('/refresh-token', 'AuthController@refreshToken');

    Route::middleware(['token'])->group(function () {
        Route::get('/categories', 'CategoryController@pullCategories');

        Route::post('/fulfillments', 'FulfillmentController@pushFulfillment');

        Route::get('/listing/get', 'ListingController@pullListings');

        Route::get('/orders/get', 'OrderController@pullOrders');
    });
});

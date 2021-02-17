<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
/***CUSTOMER*****/
Route::group(['prefix' => 'customer', 'namespace'=>'Back\Customer'], function () {
    Route::get('/download-file/{fileId}/{fileName}', 'CustomerController@download');
});

Route::get('/', function () {
    return view('welcome');
});
